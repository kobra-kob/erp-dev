<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Phase18Test extends TestCase
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

    public function test_new_company_has_core_modules_enabled_by_default(): void
    {
        $this->assertTrue($this->company->isModuleEnabled('clients'));
        $this->assertTrue($this->company->isModuleEnabled('invoices'));
        // Paramètres : toujours actif (obligatoire), sans ligne d'activation.
        $this->assertTrue($this->company->isModuleEnabled('settings'));

        $this->get(route('clients.index'))->assertOk();
    }

    public function test_admin_can_disable_a_core_module_and_lose_access(): void
    {
        // Désactivation via le catalogue.
        $this->post(route('modules.toggle', 'clients'))->assertRedirect();

        $this->assertFalse($this->company->fresh()->isModuleEnabled('clients'));
        $this->get(route('clients.index'))->assertForbidden();         // route bloquée
        $this->get(route('dashboard'))->assertOk()->assertDontSee('Clients / CRM'); // retiré du launcher

        // Réactivation → accès restauré.
        $this->post(route('modules.toggle', 'clients'))->assertRedirect();
        $this->get(route('clients.index'))->assertOk();
    }

    public function test_settings_module_cannot_be_disabled(): void
    {
        $this->post(route('modules.toggle', 'settings'))->assertForbidden();
        $this->get(route('settings.index'))->assertOk();
    }

    public function test_catalog_lists_core_and_sector_modules(): void
    {
        $this->get(route('modules.catalog'))->assertOk()
            ->assertSee('Modules de base')
            ->assertSee('Clients / CRM')
            ->assertSee('Modules métiers')
            ->assertSee('Opticien');
    }

    public function test_registration_redirects_to_onboarding(): void
    {
        auth()->logout(); // l'inscription se fait en tant qu'invité

        $this->post(route('register'), [
            'company_name' => 'Nouvelle SARL',
            'name'         => 'Patron',
            'email'        => 'patron@nouvelle.local',
            'password'     => 'Password123',
            'password_confirmation' => 'Password123',
        ])->assertRedirect(route('onboarding'));
    }

    public function test_onboarding_applies_module_selection(): void
    {
        // On garde clients, on retire invoices, on active opticien.
        $this->post(route('onboarding.store'), [
            'modules' => ['clients', 'opticien'],
        ])->assertRedirect(route('dashboard'));

        $company = $this->company->fresh();
        $this->assertTrue($company->isModuleEnabled('clients'));
        $this->assertFalse($company->isModuleEnabled('invoices')); // décoché → désactivé
        $this->assertTrue($company->hasModule('opticien'));        // vertical activé
    }
}
