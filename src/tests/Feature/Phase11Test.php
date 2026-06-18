<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class Phase11Test extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->company = Company::create(['name' => 'Demo']);
        // Un responsable pour la notification de réponse.
        User::create([
            'company_id' => $this->company->id, 'name' => 'Admin', 'email' => 'admin@test.local',
            'password' => Hash::make('password'), 'role' => User::ROLE_ADMIN,
        ]);
    }

    private function quote(string $status = 'sent'): Quote
    {
        $client = Client::factory()->create(['company_id' => $this->company->id]);
        $quote = Quote::create([
            'company_id' => $this->company->id, 'client_id' => $client->id,
            'number' => 'DEV-1', 'status' => $status, 'issue_date' => now(),
        ]);
        $quote->lines()->create(['type' => 'main_oeuvre', 'description' => 'Pose', 'quantity' => 1, 'unit_price' => 200, 'tax_rate' => 20]);
        $quote->load('lines');
        $quote->recalculateTotals();

        return $quote;
    }

    public function test_quote_gets_public_token_automatically(): void
    {
        $this->assertNotEmpty($this->quote()->public_token);
    }

    public function test_public_page_is_accessible_without_login(): void
    {
        $quote = $this->quote();
        $this->get(route('quotes.public', $quote->public_token))
            ->assertOk()->assertSee($quote->number)->assertSee('Accepter le devis');
    }

    public function test_invalid_token_returns_404(): void
    {
        $this->get(route('quotes.public', 'jeton-bidon'))->assertNotFound();
    }

    public function test_client_acceptance_updates_status_and_creates_invoice(): void
    {
        $quote = $this->quote('sent');

        $this->post(route('quotes.public.accept', $quote->public_token))->assertRedirect();

        $this->assertSame('accepted', $quote->fresh()->status);
        $invoice = Invoice::withoutGlobalScopes()->where('quote_id', $quote->id)->first();
        $this->assertNotNull($invoice);
        $this->assertEquals($quote->total_ttc, $invoice->total_ttc);
    }

    public function test_client_refusal_updates_status_without_invoice(): void
    {
        $quote = $this->quote('sent');

        $this->post(route('quotes.public.refuse', $quote->public_token))->assertRedirect();

        $this->assertSame('refused', $quote->fresh()->status);
        $this->assertSame(0, Invoice::withoutGlobalScopes()->where('quote_id', $quote->id)->count());
    }

    public function test_acceptance_is_idempotent(): void
    {
        $quote = $this->quote('sent');
        $this->post(route('quotes.public.accept', $quote->public_token));
        $this->post(route('quotes.public.accept', $quote->public_token)); // 2e clic

        $this->assertSame(1, Invoice::withoutGlobalScopes()->where('quote_id', $quote->id)->count());
    }

    public function test_in_app_status_change_to_accepted_autoconverts(): void
    {
        $admin = User::where('email', 'admin@test.local')->first();
        $quote = $this->quote('sent');

        $this->actingAs($admin)->patch(route('quotes.status', $quote), ['status' => 'accepted'])
            ->assertRedirect();

        $this->assertSame(1, Invoice::withoutGlobalScopes()->where('quote_id', $quote->id)->count());
    }
}
