<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\User;
use App\Services\AssistantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Phase4Test extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('assistant.api_key', ''); // mode gratuit (sans IA)
        $this->company = Company::create(['name' => 'Demo']);
        $this->admin = User::create([
            'company_id' => $this->company->id, 'name' => 'Admin', 'email' => 'admin@test.local',
            'password' => Hash::make('password'), 'role' => User::ROLE_ADMIN,
        ]);
    }

    private function unpaidInvoice(string $clientName, float $amount): void
    {
        $client = Client::factory()->create(['company_id' => $this->company->id, 'name' => $clientName]);
        $invoice = Invoice::create([
            'company_id' => $this->company->id, 'client_id' => $client->id,
            'number' => 'FAC-X', 'status' => 'unpaid', 'issue_date' => now()->toDateString(),
        ]);
        $invoice->lines()->create(['type' => 'autre', 'description' => 'P', 'quantity' => 1, 'unit_price' => $amount, 'tax_rate' => 0]);
        $invoice->load('lines');
        $invoice->recalculateTotals();
    }

    public function test_assistant_answers_unpaid_from_database_without_ai(): void
    {
        $this->actingAs($this->admin);
        $this->unpaidInvoice('Client Dupont', 1500);

        $result = app(AssistantService::class)->ask('Quels clients doivent payer ?');

        $this->assertSame('local', $result['source']);
        $this->assertStringContainsString('Client Dupont', $result['answer']);
        $this->assertStringContainsString('1 500', $result['answer']);
    }

    public function test_assistant_message_endpoint_returns_json(): void
    {
        $this->actingAs($this->admin);
        $this->unpaidInvoice('Client Martin', 800);

        $this->postJson(route('assistant.message'), ['message' => 'Qui doit payer ?'])
            ->assertOk()
            ->assertJsonStructure(['answer', 'source'])
            ->assertJsonFragment(['source' => 'local']);
    }

    public function test_assistant_is_restricted_to_managers(): void
    {
        $employe = User::create([
            'company_id' => $this->company->id, 'name' => 'Emp', 'email' => 'emp@test.local',
            'password' => Hash::make('password'), 'role' => User::ROLE_EMPLOYE,
        ]);

        $this->actingAs($employe)->get(route('assistant.index'))->assertForbidden();
    }

    public function test_statistics_page_loads(): void
    {
        $this->actingAs($this->admin)->get(route('statistics.index'))->assertOk()->assertSee('Statistiques');
    }

    public function test_security_headers_are_present(): void
    {
        $this->get(route('login'))
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }
}
