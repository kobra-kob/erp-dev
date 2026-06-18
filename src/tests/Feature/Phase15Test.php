<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\Prescription;
use App\Models\Property;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Phase15Test extends TestCase
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

    // --- Opticien ---

    public function test_optician_module_gated_and_functional(): void
    {
        $this->get(route('prescriptions.index'))->assertForbidden();      // module inactif

        $this->company->enableModule('opticien');
        $this->get(route('prescriptions.index'))->assertOk();             // activé

        $client = Client::factory()->create(['company_id' => $this->company->id]);
        $this->post(route('prescriptions.store'), [
            'client_id' => $client->id, 'prescribed_at' => now()->toDateString(),
            'od_sphere' => -1.25, 'og_sphere' => -1.5, 'pupillary_distance' => 62,
        ])->assertRedirect(route('prescriptions.index'));

        $this->assertDatabaseHas('prescriptions', ['client_id' => $client->id, 'pupillary_distance' => 62]);
    }

    // --- Immobilier ---

    public function test_realestate_module_gated_and_functional(): void
    {
        $this->get(route('properties.index'))->assertForbidden();

        $this->company->enableModule('immobilier');
        $this->get(route('properties.index'))->assertOk();

        $this->post(route('properties.store'), [
            'title' => 'T3 centre', 'type' => 'appartement', 'transaction' => 'vente',
            'status' => 'disponible', 'price' => 250000, 'surface' => 65, 'rooms' => 3, 'dpe' => 'C',
        ])->assertRedirect(route('properties.index'));

        $this->assertDatabaseHas('properties', ['title' => 'T3 centre', 'company_id' => $this->company->id]);
    }

    // --- Concessionnaire ---

    public function test_dealer_module_gated_and_functional(): void
    {
        $this->get(route('vehicles.index'))->assertForbidden();

        $this->company->enableModule('concessionnaire');
        $this->get(route('vehicles.index'))->assertOk();

        $this->post(route('vehicles.store'), [
            'brand' => 'Renault', 'model' => 'Clio', 'energy' => 'essence',
            'condition' => 'occasion', 'status' => 'disponible', 'price' => 12000,
            'year' => 2021, 'mileage' => 35000,
        ])->assertRedirect(route('vehicles.index'));

        $this->assertDatabaseHas('vehicles', ['brand' => 'Renault', 'model' => 'Clio', 'company_id' => $this->company->id]);
    }

    // --- Dashboard ---

    public function test_activated_vertical_modules_appear_on_dashboard(): void
    {
        $this->get(route('dashboard'))->assertDontSee('Parc véhicules');

        $this->company->enableModule('concessionnaire');
        $this->company->enableModule('opticien');

        $this->get(route('dashboard'))->assertOk()
            ->assertSee('Parc véhicules')        // feature concessionnaire
            ->assertSee('Ordonnances optiques'); // feature opticien
    }
}
