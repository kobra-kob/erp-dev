<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class Phase13Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    private function companyWithOwner(): array
    {
        $company = Company::create(['name' => 'Demo']);
        $owner = User::create([
            'company_id' => $company->id, 'name' => 'Owner', 'email' => 'owner@test.local',
            'password' => Hash::make('password'), 'role' => User::ROLE_ADMIN,
        ]);
        $company->update(['owner_id' => $owner->id]);

        return [$company->fresh(), $owner];
    }

    public function test_registration_sets_owner(): void
    {
        $this->post('/register', [
            'company_name' => 'Ma Boîte', 'name' => 'Jean', 'email' => 'jean@test.local',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertRedirect(route('onboarding'));

        $user = User::where('email', 'jean@test.local')->firstOrFail();
        $this->assertSame($user->id, $user->company->owner_id);
    }

    public function test_authenticated_user_cannot_open_registration(): void
    {
        [$company, $owner] = $this->companyWithOwner();

        // La page d'inscription est réservée aux invités → un owner connecté est redirigé.
        $this->actingAs($owner)->get('/register')->assertRedirect();
    }

    public function test_employee_cap_is_enforced(): void
    {
        [$company, $owner] = $this->companyWithOwner();

        // 5 employés (la limite)
        for ($i = 1; $i <= Company::MAX_EMPLOYEES; $i++) {
            User::create([
                'company_id' => $company->id, 'name' => "E$i", 'email' => "e$i@test.local",
                'password' => Hash::make('password'), 'role' => User::ROLE_EMPLOYE,
            ]);
        }
        $this->assertFalse($company->fresh()->canAddEmployee());

        // Le 6e est refusé
        $this->actingAs($owner)->post(route('employees.store'), [
            'name' => 'Trop', 'email' => 'trop@test.local', 'role' => 'EMPLOYE',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertSessionHasErrors('employee');

        $this->assertDatabaseMissing('users', ['email' => 'trop@test.local']);
        $this->assertSame(Company::MAX_EMPLOYEES, $company->fresh()->employeeCount());
    }

    public function test_employee_can_be_added_below_cap(): void
    {
        [$company, $owner] = $this->companyWithOwner();

        $this->actingAs($owner)->post(route('employees.store'), [
            'name' => 'Paul', 'email' => 'paul@test.local', 'role' => 'EMPLOYE',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertRedirect();

        $this->assertSame(1, $company->fresh()->employeeCount());
    }

    public function test_owner_cannot_be_deleted(): void
    {
        [$company, $owner] = $this->companyWithOwner();
        $admin2 = User::create([
            'company_id' => $company->id, 'name' => 'Admin2', 'email' => 'a2@test.local',
            'password' => Hash::make('password'), 'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin2)->delete(route('employees.destroy', $owner))->assertSessionHasErrors('employee');
        $this->assertDatabaseHas('users', ['id' => $owner->id]);
    }
}
