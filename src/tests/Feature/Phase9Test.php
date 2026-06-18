<?php

namespace Tests\Feature;

use App\Models\AccountingEntryLine;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Client;
use App\Models\Company;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\User;
use App\Services\AccountingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Phase9Test extends TestCase
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
        $this->actingAs($this->admin);
    }

    private function invoiceWithPayment(): Invoice
    {
        $client = Client::factory()->create(['company_id' => $this->company->id]);
        $invoice = Invoice::create([
            'company_id' => $this->company->id, 'client_id' => $client->id,
            'number' => 'FAC-1', 'status' => 'unpaid', 'issue_date' => now(),
        ]);
        $invoice->lines()->create(['type' => 'main_oeuvre', 'description' => 'Pose', 'quantity' => 1, 'unit_price' => 100, 'tax_rate' => 20]);
        $invoice->load('lines');
        $invoice->recalculateTotals(); // HT 100, TVA 20, TTC 120

        $invoice->payments()->create([
            'company_id' => $this->company->id, 'amount' => 120, 'paid_at' => now()->toDateString(), 'method' => 'virement',
        ]);
        $invoice->refreshPaymentStatus();

        return $invoice;
    }

    public function test_rebuild_generates_balanced_double_entry(): void
    {
        $this->invoiceWithPayment();
        Expense::create([
            'company_id' => $this->company->id, 'category' => 'carburant', 'label' => 'Gasoil',
            'amount' => 60, 'spent_at' => now()->toDateString(),
        ]);

        $result = app(AccountingService::class)->rebuild();

        $this->assertTrue($result['balanced']);
        $this->assertSame(3, $result['entries']); // facture + paiement + dépense

        // Partie double : total débit == total crédit sur l'ensemble du grand livre.
        $this->assertEqualsWithDelta(
            (float) AccountingEntryLine::sum('debit'),
            (float) AccountingEntryLine::sum('credit'),
            0.01
        );

        // Le plan comptable a été provisionné automatiquement.
        $this->assertDatabaseHas('accounting_accounts', ['code' => '411000']);
        $this->assertDatabaseHas('accounting_accounts', ['code' => '707000']);
        $this->assertDatabaseHas('accounting_accounts', ['code' => '512000']);
    }

    public function test_sales_entry_splits_ht_and_vat(): void
    {
        $this->invoiceWithPayment();
        app(AccountingService::class)->rebuild();

        // 707 (produit) crédité du HT, 445710 (TVA) crédité de la TVA.
        $ht = AccountingEntryLine::whereHas('account', fn ($q) => $q->where('code', '707000'))->sum('credit');
        $vat = AccountingEntryLine::whereHas('account', fn ($q) => $q->where('code', '445710'))->sum('credit');
        $this->assertEquals(100.00, $ht);
        $this->assertEquals(20.00, $vat);
    }

    public function test_rebuild_is_idempotent(): void
    {
        $this->invoiceWithPayment();
        $svc = app(AccountingService::class);
        $first = $svc->rebuild()['entries'];
        $second = $svc->rebuild()['entries'];

        $this->assertSame($first, $second); // pas de doublon
    }

    public function test_bank_auto_reconciliation_matches_payment(): void
    {
        $invoice = $this->invoiceWithPayment();
        $payment = $invoice->payments()->first();

        $bank = BankAccount::create(['company_id' => $this->company->id, 'name' => 'Compte courant']);
        $tx = $bank->transactions()->create([
            'company_id' => $this->company->id, 'transaction_date' => now()->toDateString(),
            'label' => 'VIR CLIENT', 'amount' => 120, 'reconciled' => false,
        ]);

        $this->post(route('bank.reconcile', $bank))->assertRedirect();

        $tx->refresh();
        $this->assertTrue($tx->reconciled);
        $this->assertSame($payment->id, $tx->payment_id);
    }

    public function test_fec_export_is_downloadable(): void
    {
        $this->invoiceWithPayment();
        app(AccountingService::class)->rebuild();

        $this->get(route('accounting.fec'))
            ->assertOk()
            ->assertHeader('content-type', 'text/plain; charset=UTF-8');
    }

    public function test_accounting_is_restricted_to_managers(): void
    {
        $employe = User::create([
            'company_id' => $this->company->id, 'name' => 'E', 'email' => 'e@test.local',
            'password' => Hash::make('password'), 'role' => User::ROLE_EMPLOYE,
        ]);
        $this->actingAs($employe)->get(route('accounting.index'))->assertForbidden();
        $this->actingAs($employe)->get(route('bank.index'))->assertForbidden();
    }
}
