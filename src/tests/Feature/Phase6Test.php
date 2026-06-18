<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Document;
use App\Models\EmployeeDocument;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase6Test extends TestCase
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

    // --- Dépenses ---

    public function test_expense_creation_and_isolation(): void
    {
        $this->actingAs($this->admin)->post(route('expenses.store'), [
            'category' => 'carburant', 'label' => 'Plein', 'amount' => 90, 'spent_at' => now()->toDateString(),
        ])->assertRedirect();

        $expense = Expense::firstOrFail();
        $this->assertSame($this->company->id, $expense->company_id);
        $this->assertEquals(90.00, $expense->amount);
    }

    public function test_expense_receipt_is_served_inline_and_downloadable(): void
    {
        Storage::fake('local');
        $this->actingAs($this->admin)->post(route('expenses.store'), [
            'category' => 'materiel', 'label' => 'Achat', 'amount' => 50, 'spent_at' => now()->toDateString(),
            'receipt' => UploadedFile::fake()->create('ticket.pdf', 50, 'application/pdf'),
        ])->assertRedirect();

        $expense = Expense::firstOrFail();
        Storage::assertExists($expense->receipt_path);

        // Aperçu => inline ; téléchargement => attachment
        $inline = $this->actingAs($this->admin)->get(route('expenses.receipt', $expense));
        $inline->assertOk();
        $this->assertStringContainsString('inline', $inline->headers->get('content-disposition'));

        $dl = $this->actingAs($this->admin)->get(route('expenses.receipt.download', $expense));
        $this->assertStringContainsString('attachment', $dl->headers->get('content-disposition'));
    }

    public function test_employee_cannot_access_expenses(): void
    {
        $emp = User::create([
            'company_id' => $this->company->id, 'name' => 'E', 'email' => 'e@test.local',
            'password' => Hash::make('password'), 'role' => User::ROLE_EMPLOYE,
        ]);
        $this->actingAs($emp)->get(route('expenses.index'))->assertForbidden();
    }

    // --- Contrats employés ---

    public function test_admin_can_attach_contract_to_employee(): void
    {
        Storage::fake('local');
        $employee = User::create([
            'company_id' => $this->company->id, 'name' => 'Paul', 'email' => 'paul@test.local',
            'password' => Hash::make('password'), 'role' => User::ROLE_EMPLOYE,
        ]);

        $this->actingAs($this->admin)->post(route('employees.documents.store', $employee), [
            'title' => 'Contrat CDI', 'type' => 'contrat',
            'file' => UploadedFile::fake()->create('cdi.pdf', 80, 'application/pdf'),
        ])->assertRedirect();

        $doc = EmployeeDocument::firstOrFail();
        $this->assertSame($employee->id, $doc->user_id);
        Storage::assertExists($doc->path);

        $inline = $this->actingAs($this->admin)->get(route('employees.documents.show', [$employee, $doc]));
        $this->assertStringContainsString('inline', $inline->headers->get('content-disposition'));
    }

    public function test_employee_contracts_are_admin_only(): void
    {
        $gerant = User::create([
            'company_id' => $this->company->id, 'name' => 'G', 'email' => 'g@test.local',
            'password' => Hash::make('password'), 'role' => User::ROLE_GERANT,
        ]);
        $this->actingAs($gerant)->get(route('employees.show', $this->admin))->assertForbidden();
    }

    // --- Visualiseur (documents transverses servis inline) ---

    public function test_document_is_served_inline(): void
    {
        Storage::fake('local');
        $this->actingAs($this->admin)->post(route('documents.store'), [
            'title' => 'Contrat', 'category' => 'contrat',
            'file' => UploadedFile::fake()->create('doc.pdf', 30, 'application/pdf'),
        ])->assertRedirect();

        $doc = Document::firstOrFail();
        $inline = $this->actingAs($this->admin)->get(route('documents.show', $doc));
        $this->assertStringContainsString('inline', $inline->headers->get('content-disposition'));

        $dl = $this->actingAs($this->admin)->get(route('documents.download', $doc));
        $this->assertStringContainsString('attachment', $dl->headers->get('content-disposition'));
    }
}
