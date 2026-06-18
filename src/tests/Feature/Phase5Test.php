<?php

namespace Tests\Feature;

use App\Mail\InvoiceReminderMail;
use App\Models\Client;
use App\Models\Company;
use App\Models\Document;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase5Test extends TestCase
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

    private function overdueInvoice(?string $email = 'client@test.local'): Invoice
    {
        $client = Client::factory()->create(['company_id' => $this->company->id, 'email' => $email]);
        $invoice = Invoice::create([
            'company_id' => $this->company->id, 'client_id' => $client->id,
            'number' => 'FAC-OVERDUE', 'status' => 'unpaid',
            'issue_date' => now()->subDays(40), 'due_date' => now()->subDays(10),
        ]);
        $invoice->lines()->create(['type' => 'autre', 'description' => 'P', 'quantity' => 1, 'unit_price' => 200, 'tax_rate' => 0]);
        $invoice->load('lines');
        $invoice->recalculateTotals();

        return $invoice;
    }

    // --- Employés ---

    public function test_admin_can_create_employee_scoped_to_company(): void
    {
        $this->actingAs($this->admin)->post(route('employees.store'), [
            'name' => 'Paul', 'email' => 'paul@test.local', 'role' => 'EMPLOYE',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertRedirect();

        $paul = User::where('email', 'paul@test.local')->first();
        $this->assertNotNull($paul);
        $this->assertSame($this->company->id, $paul->company_id);
        $this->assertSame('EMPLOYE', $paul->role);
    }

    public function test_employee_management_is_admin_only(): void
    {
        $gerant = User::create([
            'company_id' => $this->company->id, 'name' => 'G', 'email' => 'g@test.local',
            'password' => Hash::make('password'), 'role' => User::ROLE_GERANT,
        ]);
        $this->actingAs($gerant)->get(route('employees.index'))->assertForbidden();
    }

    public function test_admin_cannot_delete_self(): void
    {
        $this->actingAs($this->admin)->delete(route('employees.destroy', $this->admin))
            ->assertSessionHasErrors('employee');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    // --- Documents ---

    public function test_document_upload_and_secure_download(): void
    {
        Storage::fake('local');
        $this->actingAs($this->admin)->post(route('documents.store'), [
            'title' => 'Contrat', 'category' => 'contrat',
            'file' => UploadedFile::fake()->create('contrat.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $doc = Document::firstOrFail();
        $this->assertSame($this->company->id, $doc->company_id);
        Storage::assertExists($doc->path);
        $this->actingAs($this->admin)->get(route('documents.show', $doc))->assertOk();
    }

    // --- Relances ---

    public function test_manual_reminder_sends_mail_and_marks_invoice(): void
    {
        Mail::fake();
        $invoice = $this->overdueInvoice();

        $this->actingAs($this->admin)->post(route('invoices.remind', $invoice))->assertRedirect();

        Mail::assertSent(InvoiceReminderMail::class);
        $this->assertSame(1, $invoice->fresh()->reminders_sent);
    }

    public function test_reminder_command_processes_overdue_invoices(): void
    {
        Mail::fake();
        $this->actingAs($this->admin);
        $this->overdueInvoice();

        $this->artisan('invoices:send-reminders')->assertSuccessful();

        Mail::assertSent(InvoiceReminderMail::class);
        $this->assertSame(1, Invoice::where('number', 'FAC-OVERDUE')->first()->reminders_sent);
    }

    public function test_paid_invoice_is_not_reminded(): void
    {
        $invoice = $this->overdueInvoice();
        $invoice->update(['status' => 'paid']);

        $this->actingAs($this->admin)->post(route('invoices.remind', $invoice))
            ->assertSessionHasErrors('invoice');
    }

    // --- Export comptable ---

    public function test_accounting_export_returns_csv(): void
    {
        $this->actingAs($this->admin);
        $this->overdueInvoice();

        $response = $this->get(route('exports.invoices', ['year' => now()->year]));
        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
        $this->assertStringContainsString('FAC-OVERDUE', $response->streamedContent());
    }
}
