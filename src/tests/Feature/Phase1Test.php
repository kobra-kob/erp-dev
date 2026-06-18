<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\LoginAudit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Phase1Test extends TestCase
{
    use RefreshDatabase;

    private function makeUser(Company $company, string $role = User::ROLE_ADMIN): User
    {
        return User::create([
            'company_id' => $company->id,
            'name'       => 'Test '.$role,
            'email'      => strtolower($role).'@test.local',
            'password'   => Hash::make('password'),
            'role'       => $role,
        ]);
    }

    public function test_registration_creates_company_and_admin_user(): void
    {
        $response = $this->post('/register', [
            'company_name'          => 'Menuiserie Test',
            'name'                  => 'Jean Test',
            'email'                 => 'jean@test.local',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $user = User::where('email', 'jean@test.local')->first();
        $this->assertNotNull($user);
        $this->assertSame(User::ROLE_ADMIN, $user->role);
        $this->assertDatabaseHas('companies', ['name' => 'Menuiserie Test']);
        $this->assertSame('Menuiserie Test', $user->company->name);
    }

    public function test_login_succeeds_and_reaches_dashboard(): void
    {
        $company = Company::create(['name' => 'Demo']);
        $this->makeUser($company);

        $this->post('/login', ['email' => 'admin@test.local', 'password' => 'password'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $this->get('/dashboard')->assertOk()->assertSee('Bonjour');
        $this->assertDatabaseHas('login_audits', ['action' => 'login']);
    }

    public function test_login_fails_with_wrong_password_and_is_audited(): void
    {
        $company = Company::create(['name' => 'Demo']);
        $this->makeUser($company);

        $this->post('/login', ['email' => 'admin@test.local', 'password' => 'wrong'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertDatabaseHas('login_audits', ['action' => 'failed', 'email' => 'admin@test.local']);
    }

    public function test_clients_are_isolated_by_company(): void
    {
        $companyA = Company::create(['name' => 'A']);
        $companyB = Company::create(['name' => 'B']);
        Client::factory()->count(3)->create(['company_id' => $companyA->id]);
        Client::factory()->count(5)->create(['company_id' => $companyB->id]);

        $userA = $this->makeUser($companyA);

        // Le global scope filtre automatiquement sur l'entreprise de l'utilisateur.
        $this->actingAs($userA);
        $this->assertSame(3, Client::count());
        $this->assertSame(8, Client::withoutGlobalScopes()->count());
    }

    public function test_employee_cannot_access_clients_module(): void
    {
        $company = Company::create(['name' => 'Demo']);

        $employe = $this->makeUser($company, User::ROLE_EMPLOYE);
        $this->actingAs($employe)->get(route('clients.index'))->assertForbidden();

        $gerant = $this->makeUser($company, User::ROLE_GERANT);
        $this->actingAs($gerant)->get(route('clients.index'))->assertOk();
    }

    public function test_creating_client_auto_assigns_current_company(): void
    {
        $company = Company::create(['name' => 'Demo']);
        $admin = $this->makeUser($company);

        $this->actingAs($admin)->post(route('clients.store'), [
            'type' => 'particulier',
            'name' => 'Client Auto',
        ])->assertRedirect();

        $client = Client::withoutGlobalScopes()->where('name', 'Client Auto')->first();
        $this->assertNotNull($client);
        $this->assertSame($company->id, $client->company_id);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->get(route('clients.index'))->assertRedirect(route('login'));
    }
}
