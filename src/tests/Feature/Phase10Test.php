<?php

namespace Tests\Feature;

use App\Mail\AccountWelcomeMail;
use App\Mail\InvoiceMail;
use App\Mail\LeaveReviewedMail;
use App\Mail\LeaveSubmittedMail;
use App\Mail\QuoteMail;
use App\Models\AccountingEntryLine;
use App\Models\Client;
use App\Models\Company;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\LeaveRequest;
use App\Models\Quote;
use App\Models\User;
use App\Services\AccountingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class Phase10Test extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->company = Company::create(['name' => 'Demo']);
        $this->admin = User::create([
            'company_id' => $this->company->id, 'name' => 'Admin', 'email' => 'admin@test.local',
            'password' => Hash::make('password'), 'role' => User::ROLE_ADMIN,
        ]);
    }

    private function clientWithEmail(): Client
    {
        return Client::factory()->create(['company_id' => $this->company->id, 'email' => 'client@test.local']);
    }

    private function quote(string $status = 'draft'): Quote
    {
        $quote = Quote::create([
            'company_id' => $this->company->id, 'client_id' => $this->clientWithEmail()->id,
            'number' => 'DEV-1', 'status' => $status, 'issue_date' => now(),
        ]);
        $quote->lines()->create(['type' => 'main_oeuvre', 'description' => 'X', 'quantity' => 1, 'unit_price' => 100, 'tax_rate' => 20]);
        $quote->load('lines');
        $quote->recalculateTotals();

        return $quote;
    }

    // --- Comptabilité : TVA déductible ---

    public function test_expense_posts_deductible_vat(): void
    {
        $this->actingAs($this->admin);
        Expense::create([
            'company_id' => $this->company->id, 'category' => 'materiel', 'label' => 'Achat',
            'amount' => 120, 'vat_rate' => 20, 'spent_at' => now()->toDateString(),
        ]);

        app(AccountingService::class)->rebuild();

        // 445660 (TVA déductible) débitée de 20 ; charge de 100 (HT).
        $vat = AccountingEntryLine::whereHas('account', fn ($q) => $q->where('code', '445660'))->sum('debit');
        $this->assertEquals(20.00, $vat);
    }

    public function test_vat_return_page_loads(): void
    {
        $this->actingAs($this->admin)->get(route('accounting.vat'))->assertOk()->assertSee('TVA');
        $this->actingAs($this->admin)->get(route('accounting.balance-sheet'))->assertOk()->assertSee('Actif');
    }

    // --- Envoi par e-mail ---

    public function test_sending_quote_emails_client_and_marks_sent(): void
    {
        Mail::fake();
        $quote = $this->quote('draft');

        $this->actingAs($this->admin)->post(route('quotes.send', $quote))->assertRedirect();

        Mail::assertSent(QuoteMail::class);
        $this->assertSame('sent', $quote->fresh()->status);
    }

    public function test_sending_invoice_emails_client_and_stamps_sent_at(): void
    {
        Mail::fake();
        $invoice = Invoice::create([
            'company_id' => $this->company->id, 'client_id' => $this->clientWithEmail()->id,
            'number' => 'FAC-1', 'status' => 'unpaid', 'issue_date' => now(),
        ]);
        $invoice->lines()->create(['type' => 'autre', 'description' => 'X', 'quantity' => 1, 'unit_price' => 50, 'tax_rate' => 20]);
        $invoice->load('lines');
        $invoice->recalculateTotals();

        $this->actingAs($this->admin)->post(route('invoices.send', $invoice))->assertRedirect();

        Mail::assertSent(InvoiceMail::class);
        $this->assertNotNull($invoice->fresh()->sent_at);
    }

    public function test_sending_without_client_email_fails(): void
    {
        $client = Client::factory()->create(['company_id' => $this->company->id, 'email' => null]);
        $quote = Quote::create([
            'company_id' => $this->company->id, 'client_id' => $client->id,
            'number' => 'DEV-2', 'status' => 'draft', 'issue_date' => now(),
        ]);

        Mail::fake();
        $this->actingAs($this->admin)->post(route('quotes.send', $quote))->assertSessionHasErrors('quote');
        Mail::assertNothingSent();
    }

    // --- Notifications ---

    public function test_registration_sends_welcome_email(): void
    {
        Mail::fake();
        $this->post('/register', [
            'company_name' => 'Nouvelle', 'name' => 'Jean', 'email' => 'jean@test.local',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertRedirect(route('dashboard'));

        Mail::assertSent(AccountWelcomeMail::class);
    }

    public function test_leave_workflow_sends_notifications(): void
    {
        Mail::fake();
        $emp = User::create([
            'company_id' => $this->company->id, 'name' => 'Paul', 'email' => 'paul@test.local',
            'password' => Hash::make('password'), 'role' => User::ROLE_EMPLOYE,
        ]);

        // Soumission → notifie les responsables
        $this->actingAs($emp)->post(route('leaves.store'), [
            'type' => 'conges_payes', 'start_date' => now()->addWeek()->toDateString(),
            'end_date' => now()->addWeeks(2)->toDateString(),
        ])->assertRedirect();
        Mail::assertSent(LeaveSubmittedMail::class);

        // Décision → notifie l'employé
        $leave = LeaveRequest::first();
        $this->actingAs($this->admin)->patch(route('leaves.approve', $leave))->assertRedirect();
        Mail::assertSent(LeaveReviewedMail::class);
    }
}
