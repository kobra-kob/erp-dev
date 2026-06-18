<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Phase2Test extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $admin;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create(['name' => 'Demo']);
        $this->admin = User::create([
            'company_id' => $this->company->id,
            'name'       => 'Admin',
            'email'      => 'admin@test.local',
            'password'   => Hash::make('password'),
            'role'       => User::ROLE_ADMIN,
        ]);
        $this->client = Client::factory()->create(['company_id' => $this->company->id]);
        $this->actingAs($this->admin);
    }

    private function quotePayload(array $overrides = []): array
    {
        return array_merge([
            'client_id'  => $this->client->id,
            'issue_date' => now()->toDateString(),
            'lines'      => [
                ['type' => 'main_oeuvre', 'description' => 'Pose', 'quantity' => 2, 'unit_price' => 100, 'tax_rate' => 20],
                ['type' => 'materiel', 'description' => 'Tuyau', 'quantity' => 1, 'unit_price' => 50, 'tax_rate' => 10],
            ],
        ], $overrides);
    }

    public function test_quote_creation_computes_totals(): void
    {
        $this->post(route('quotes.store'), $this->quotePayload())->assertRedirect();

        $quote = Quote::firstOrFail();
        // HT = 2*100 + 1*50 = 250 ; TVA = 200*0.20 + 50*0.10 = 45 ; TTC = 295
        $this->assertEquals(250.00, $quote->subtotal_ht);
        $this->assertEquals(45.00, $quote->tax_amount);
        $this->assertEquals(295.00, $quote->total_ttc);
        $this->assertCount(2, $quote->lines);
        $this->assertSame('DEV-' . now()->year . '-001', $quote->number);
    }

    public function test_quote_numbering_increments(): void
    {
        $this->post(route('quotes.store'), $this->quotePayload());
        $this->post(route('quotes.store'), $this->quotePayload());

        $numbers = Quote::orderBy('id')->pluck('number')->all();
        $this->assertSame([
            'DEV-' . now()->year . '-001',
            'DEV-' . now()->year . '-002',
        ], $numbers);
    }

    public function test_accepted_quote_converts_to_invoice(): void
    {
        $this->post(route('quotes.store'), $this->quotePayload());
        $quote = Quote::firstOrFail();
        $quote->update(['status' => 'accepted']);

        $this->post(route('quotes.convert', $quote))->assertRedirect();

        $invoice = Invoice::firstOrFail();
        $this->assertSame($quote->id, $invoice->quote_id);
        $this->assertEquals($quote->total_ttc, $invoice->total_ttc);
        $this->assertCount(2, $invoice->lines);
        $this->assertTrue($quote->fresh()->isConvertedToInvoice());
    }

    public function test_non_accepted_quote_cannot_be_converted(): void
    {
        $this->post(route('quotes.store'), $this->quotePayload());
        $quote = Quote::firstOrFail(); // statut draft

        $this->post(route('quotes.convert', $quote))->assertSessionHasErrors('quote');
        $this->assertSame(0, Invoice::count());
    }

    public function test_payment_updates_invoice_status(): void
    {
        $invoice = Invoice::create([
            'company_id' => $this->company->id,
            'client_id'  => $this->client->id,
            'number'     => 'FAC-TEST-1',
            'issue_date' => now()->toDateString(),
        ]);
        $invoice->lines()->create(['type' => 'autre', 'description' => 'X', 'quantity' => 1, 'unit_price' => 100, 'tax_rate' => 0]);
        $invoice->load('lines');
        $invoice->recalculateTotals();
        $this->assertEquals(100.00, $invoice->total_ttc);

        // Paiement partiel
        $this->post(route('invoices.payments.store', $invoice), [
            'amount' => 40, 'paid_at' => now()->toDateString(), 'method' => 'cb',
        ])->assertRedirect();
        $this->assertSame('partial', $invoice->fresh()->status);

        // Solde
        $this->post(route('invoices.payments.store', $invoice), [
            'amount' => 60, 'paid_at' => now()->toDateString(), 'method' => 'virement',
        ])->assertRedirect();
        $this->assertSame('paid', $invoice->fresh()->status);
    }

    public function test_quote_pdf_is_generated(): void
    {
        $this->post(route('quotes.store'), $this->quotePayload());
        $quote = Quote::firstOrFail();

        $this->get(route('quotes.pdf', $quote))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_employee_cannot_access_quotes_or_invoices(): void
    {
        $employe = User::create([
            'company_id' => $this->company->id,
            'name' => 'Emp', 'email' => 'emp@test.local',
            'password' => Hash::make('password'), 'role' => User::ROLE_EMPLOYE,
        ]);

        $this->actingAs($employe)->get(route('quotes.index'))->assertForbidden();
        $this->actingAs($employe)->get(route('invoices.index'))->assertForbidden();
    }
}
