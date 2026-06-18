<?php

namespace Tests\Feature;

use App\Models\CatalogItem;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Phase14Test extends TestCase
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

    public function test_admin_can_activate_and_deactivate_module(): void
    {
        $this->actingAs($this->admin)->post(route('modules.toggle', 'batiment'))->assertRedirect();
        $this->assertTrue($this->company->fresh()->hasModule('batiment'));

        $this->actingAs($this->admin)->post(route('modules.toggle', 'batiment'))->assertRedirect();
        $this->assertFalse($this->company->fresh()->hasModule('batiment'));

        // Désactivation = conservation des données (la ligne existe, inactive)
        $this->assertDatabaseHas('company_modules', ['company_id' => $this->company->id, 'module_key' => 'batiment', 'active' => false]);
    }

    public function test_unknown_module_cannot_be_toggled(): void
    {
        $this->actingAs($this->admin)->post(route('modules.toggle', 'inconnu'))->assertNotFound();
        $this->assertDatabaseMissing('company_modules', ['company_id' => $this->company->id, 'module_key' => 'inconnu']);
    }

    public function test_module_catalog_is_admin_only(): void
    {
        $gerant = User::create([
            'company_id' => $this->company->id, 'name' => 'G', 'email' => 'g@test.local',
            'password' => Hash::make('password'), 'role' => User::ROLE_GERANT,
        ]);
        $this->actingAs($gerant)->get(route('modules.catalog'))->assertForbidden();
    }

    public function test_catalog_requires_module_active(): void
    {
        // Sans le module Bâtiment → accès interdit
        $this->actingAs($this->admin)->get(route('catalog.index'))->assertForbidden();

        // Après activation → accès autorisé
        $this->company->enableModule('batiment');
        $this->actingAs($this->admin)->get(route('catalog.index'))->assertOk();
    }

    public function test_can_manage_catalog_items_when_active(): void
    {
        $this->company->enableModule('batiment');

        $this->actingAs($this->admin)->post(route('catalog.store'), [
            'trade' => 'plombier', 'line_type' => 'materiel', 'label' => 'Robinet',
            'unit' => 'u', 'unit_price' => 50, 'tax_rate' => 20,
        ])->assertRedirect(route('catalog.index'));

        $this->assertDatabaseHas('catalog_items', [
            'company_id' => $this->company->id, 'label' => 'Robinet', 'trade' => 'plombier',
        ]);
    }

    public function test_activated_module_appears_on_dashboard(): void
    {
        // Inactif → pas de tuile Bâtiment sur le tableau de bord
        $this->actingAs($this->admin)->get(route('dashboard'))->assertDontSee('Catalogue de prestations');

        // Activé → la tuile apparaît avec les autres apps
        $this->company->enableModule('batiment');
        $this->actingAs($this->admin)->get(route('dashboard'))
            ->assertOk()->assertSee('Catalogue de prestations');
    }

    public function test_quote_form_offers_catalog_only_when_active(): void
    {
        $item = CatalogItem::create([
            'company_id' => $this->company->id, 'trade' => 'peintre', 'line_type' => 'main_oeuvre',
            'label' => 'Peinture plafond', 'unit' => 'm2', 'unit_price' => 22, 'tax_rate' => 10,
        ]);

        // Module inactif → pas de sélecteur catalogue
        $this->actingAs($this->admin)->get(route('quotes.create'))->assertDontSee('Peinture plafond');

        // Module actif → le devis propose les prestations du catalogue
        $this->company->enableModule('batiment');
        $this->actingAs($this->admin)->get(route('quotes.create'))->assertSee('Peinture plafond');
    }
}
