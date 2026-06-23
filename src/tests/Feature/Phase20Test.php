<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Phase20Test extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $admin;
    private Client $client;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->company = Company::create(['name' => 'Demo']);
        $this->admin = User::create([
            'company_id' => $this->company->id, 'name' => 'Admin', 'email' => 'admin@test.local',
            'password' => Hash::make('password'), 'role' => User::ROLE_ADMIN,
        ]);
        $this->actingAs($this->admin);

        $this->client = Client::factory()->create(['company_id' => $this->company->id]);
        $this->product = Product::create([
            'company_id' => $this->company->id, 'name' => 'Robinet', 'unit' => 'u',
            'purchase_price' => 20, 'sale_price' => 49, 'tax_rate' => 20, 'is_sellable' => true,
            'stock' => 10, 'min_stock' => 1,
        ]);
    }

    private function invoicePayload(float $qty): array
    {
        return [
            'client_id'  => $this->client->id,
            'issue_date' => now()->toDateString(),
            'lines'      => [[
                'type' => 'materiel', 'description' => 'Robinet', 'quantity' => $qty,
                'unit_price' => 49, 'tax_rate' => 20, 'product_id' => $this->product->id,
            ]],
        ];
    }

    private function quotePayload(float $qty): array
    {
        return [
            'client_id'  => $this->client->id,
            'issue_date' => now()->toDateString(),
            'lines'      => [[
                'type' => 'materiel', 'description' => 'Robinet', 'quantity' => $qty,
                'unit_price' => 49, 'tax_rate' => 20, 'product_id' => $this->product->id,
            ]],
        ];
    }

    public function test_invoice_deducts_stock(): void
    {
        $this->post(route('invoices.store'), $this->invoicePayload(3))->assertRedirect();
        $this->assertEquals(7, $this->product->fresh()->stock);
    }

    public function test_invoice_cannot_exceed_stock(): void
    {
        $this->post(route('invoices.store'), $this->invoicePayload(15))->assertSessionHasErrors('lines');
        $this->assertEquals(10, $this->product->fresh()->stock);
    }

    public function test_deleting_invoice_restores_stock(): void
    {
        $this->post(route('invoices.store'), $this->invoicePayload(4))->assertRedirect();
        $invoice = Invoice::firstOrFail();
        $this->assertEquals(6, $this->product->fresh()->stock);

        $this->delete(route('invoices.destroy', $invoice))->assertRedirect();
        $this->assertEquals(10, $this->product->fresh()->stock);
    }

    public function test_editing_invoice_reconciles_stock(): void
    {
        $this->post(route('invoices.store'), $this->invoicePayload(3))->assertRedirect();
        $invoice = Invoice::firstOrFail();
        $this->assertEquals(7, $this->product->fresh()->stock);

        $this->put(route('invoices.update', $invoice), $this->invoicePayload(5))->assertRedirect();
        $this->assertEquals(5, $this->product->fresh()->stock);

        $this->put(route('invoices.update', $invoice), $this->invoicePayload(1))->assertRedirect();
        $this->assertEquals(9, $this->product->fresh()->stock);
    }

    public function test_quote_does_not_deduct_but_validates_availability(): void
    {
        $this->post(route('quotes.store'), $this->quotePayload(4))->assertRedirect();
        $this->assertEquals(10, $this->product->fresh()->stock); // devis : pas de décompte

        $this->post(route('quotes.store'), $this->quotePayload(99))->assertSessionHasErrors('lines');
    }

    public function test_apply_invoices_command_regularises_past_invoices(): void
    {
        // Facture « ancienne » : ligne sans product_id, libellé = nom du produit, non appliquée.
        $invoice = Invoice::create([
            'company_id' => $this->company->id, 'client_id' => $this->client->id,
            'number' => 'FAC-OLD', 'status' => 'unpaid', 'issue_date' => now()->toDateString(),
        ]);
        $invoice->lines()->create([
            'type' => 'materiel', 'description' => 'Robinet', 'quantity' => 4,
            'unit_price' => 49, 'tax_rate' => 20, 'position' => 0,
        ]);
        $this->assertEquals(10, $this->product->fresh()->stock);

        $this->artisan('stock:apply-invoices')->assertExitCode(0);

        $this->assertEquals(6, $this->product->fresh()->stock);                 // 10 - 4
        $this->assertNotNull($invoice->fresh()->stock_applied_at);
        $this->assertEquals($this->product->id, $invoice->lines()->first()->product_id); // rattaché par libellé

        // Idempotent : un 2e passage ne re-décompte pas.
        $this->artisan('stock:apply-invoices')->assertExitCode(0);
        $this->assertEquals(6, $this->product->fresh()->stock);
    }

    public function test_quote_acceptance_converts_and_deducts_once(): void
    {
        $this->post(route('quotes.store'), $this->quotePayload(4))->assertRedirect();
        $quote = Quote::firstOrFail();
        $this->assertEquals(10, $this->product->fresh()->stock);

        $this->patch(route('quotes.status', $quote), ['status' => 'accepted'])->assertRedirect();
        $this->assertEquals(6, $this->product->fresh()->stock); // décompté une seule fois
    }
}
