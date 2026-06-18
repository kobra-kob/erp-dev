<?php

namespace Tests\Feature;

use App\Mail\AccountWelcomeMail;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;
use Tests\TestCase;

class Phase12Test extends TestCase
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

    private function user(string $role, array $extra = []): User
    {
        return User::create(array_merge([
            'company_id' => $this->company->id, 'name' => 'U', 'email' => uniqid() . '@test.local',
            'password' => Hash::make('password'), 'role' => $role,
        ], $extra));
    }

    // --- Rôles personnalisés ---

    public function test_admin_can_create_custom_role(): void
    {
        $this->actingAs($this->admin)->post(route('roles.store'), [
            'name' => 'Comptable', 'modules' => ['accounting', 'invoices'],
        ])->assertRedirect(route('roles.index'));

        $role = Role::firstOrFail();
        $this->assertSame(['accounting', 'invoices'], $role->modules);
        $this->assertSame($this->company->id, $role->company_id);
    }

    public function test_custom_role_user_access_is_limited_to_its_modules(): void
    {
        $role = Role::create(['company_id' => $this->company->id, 'name' => 'Comptable', 'modules' => ['accounting']]);
        $user = $this->user(User::ROLE_CUSTOM, ['role_id' => $role->id]);

        $this->assertTrue($user->canAccessModule('accounting'));
        $this->assertFalse($user->canAccessModule('clients'));
        // Modules toujours autorisés
        $this->assertTrue($user->canAccessModule('leaves'));
        $this->assertTrue($user->canAccessModule('settings'));
    }

    public function test_custom_role_route_access_enforced(): void
    {
        $role = Role::create(['company_id' => $this->company->id, 'name' => 'Comptable', 'modules' => ['accounting']]);
        $user = $this->user(User::ROLE_CUSTOM, ['role_id' => $role->id]);

        $this->actingAs($user)->get(route('accounting.index'))->assertOk();   // autorisé
        $this->actingAs($user)->get(route('clients.index'))->assertForbidden(); // interdit
    }

    public function test_admin_assigns_custom_role_to_employee(): void
    {
        Mail::fake();
        $role = Role::create(['company_id' => $this->company->id, 'name' => 'Commercial', 'modules' => ['clients', 'quotes']]);

        $this->actingAs($this->admin)->post(route('employees.store'), [
            'name' => 'Léa', 'email' => 'lea@test.local', 'role' => 'custom:' . $role->id,
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertRedirect();

        $lea = User::where('email', 'lea@test.local')->firstOrFail();
        $this->assertSame(User::ROLE_CUSTOM, $lea->role);
        $this->assertSame($role->id, $lea->role_id);
        $this->assertTrue($lea->canAccessModule('clients'));
        $this->assertFalse($lea->canAccessModule('accounting'));
    }

    public function test_role_management_is_admin_only(): void
    {
        $gerant = $this->user(User::ROLE_GERANT);
        $this->actingAs($gerant)->get(route('roles.index'))->assertForbidden();
    }

    public function test_cannot_delete_role_in_use(): void
    {
        $role = Role::create(['company_id' => $this->company->id, 'name' => 'X', 'modules' => []]);
        $this->user(User::ROLE_CUSTOM, ['role_id' => $role->id]);

        $this->actingAs($this->admin)->delete(route('roles.destroy', $role))->assertSessionHasErrors('role');
        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }

    // --- Built-in roles inchangés ---

    public function test_builtin_gerant_still_accesses_business_modules(): void
    {
        $gerant = $this->user(User::ROLE_GERANT);
        $this->actingAs($gerant)->get(route('clients.index'))->assertOk();
        $this->actingAs($gerant)->get(route('accounting.index'))->assertOk();
    }

    public function test_builtin_employe_blocked_from_managerial_modules(): void
    {
        $emp = $this->user(User::ROLE_EMPLOYE);
        $this->actingAs($emp)->get(route('clients.index'))->assertForbidden();
        $this->actingAs($emp)->get(route('projects.index'))->assertOk(); // chantiers ok
    }

    // --- Reset mot de passe depuis les paramètres ---

    public function test_settings_password_reset_sends_link(): void
    {
        Notification::fake();

        $this->actingAs($this->admin)->post(route('settings.password-reset'))->assertRedirect();

        Notification::assertSentTo($this->admin, ResetPassword::class);
    }
}
