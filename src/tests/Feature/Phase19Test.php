<?php

namespace Tests\Feature;

use App\Http\Middleware\SupportIpAllowlist;
use App\Models\Client;
use App\Models\Company;
use App\Models\SupportAuditLog;
use App\Models\SupportUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * Phase 19 — Console de support (socle) : isolation, guard support, audit,
 * présence des tenants, allowlist IP.
 *
 * Les routes de la console ne sont chargées que sur le conteneur APP_ROLE=support ;
 * en test (rôle « tenant ») on vérifie donc la logique de fond, indépendante des routes.
 */
class Phase19Test extends TestCase
{
    use RefreshDatabase;

    private Company $companyA;
    private Company $companyB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyA = Company::create(['name' => 'Entreprise A']);
        $this->companyB = Company::create(['name' => 'Entreprise B']);

        Client::create(['company_id' => $this->companyA->id, 'name' => 'Client A1']);
        Client::create(['company_id' => $this->companyA->id, 'name' => 'Client A2']);
        Client::create(['company_id' => $this->companyB->id, 'name' => 'Client B1']);
    }

    private function supportUser(bool $active = true): SupportUser
    {
        return SupportUser::create([
            'name'      => 'Support',
            'email'     => 'support@artisanflow.test',
            'password'  => Hash::make('SupportSecret2026!'),
            'is_active' => $active,
        ]);
    }

    private function tenantUser(Company $company): User
    {
        return User::create([
            'company_id' => $company->id, 'name' => 'U', 'email' => "u{$company->id}@test.local",
            'password' => Hash::make('password'), 'role' => User::ROLE_ADMIN,
        ]);
    }

    public function test_tenant_user_only_sees_own_company_data(): void
    {
        $this->actingAs($this->tenantUser($this->companyA));

        $this->assertSame(2, Client::count()); // A1 + A2, pas B1
    }

    public function test_support_guard_bypasses_company_scope_and_sees_all_tenants(): void
    {
        // Authentifié sur le guard « support » : aucun company_id → le CompanyScope
        // (basé sur le guard web) ne filtre rien.
        $this->actingAs($this->supportUser(), 'support');

        $this->assertNull(Auth::guard('web')->user());
        $this->assertSame(3, Client::count());       // tous tenants confondus
        $this->assertSame(2, Company::count());
    }

    public function test_support_create_admin_command_creates_account(): void
    {
        $this->artisan('support:create-admin', [
            '--email'    => 'ops@artisanflow.test',
            '--password' => 'SuperSecret2026!',
            '--name'     => 'Ops',
        ])->assertSuccessful();

        $this->assertDatabaseHas('support_users', ['email' => 'ops@artisanflow.test']);
    }

    public function test_support_command_rejects_weak_password(): void
    {
        $this->artisan('support:create-admin', [
            '--email'    => 'weak@artisanflow.test',
            '--password' => 'short',
        ])->assertFailed();

        $this->assertDatabaseMissing('support_users', ['email' => 'weak@artisanflow.test']);
    }

    public function test_support_guard_authenticates_with_credentials(): void
    {
        $this->supportUser();

        $this->assertTrue(Auth::guard('support')->attempt([
            'email' => 'support@artisanflow.test', 'password' => 'SupportSecret2026!',
        ]));

        $this->assertFalse(Auth::guard('support')->attempt([
            'email' => 'support@artisanflow.test', 'password' => 'mauvais',
        ]));
    }

    public function test_audit_log_records_acting_support_user(): void
    {
        $support = $this->supportUser();
        $this->actingAs($support, 'support');

        SupportAuditLog::record('tenant.view', [
            'company_id'  => $this->companyA->id,
            'description' => 'Consultation',
        ]);

        $this->assertDatabaseHas('support_audit_logs', [
            'support_user_id' => $support->id,
            'action'          => 'tenant.view',
            'company_id'      => $this->companyA->id,
        ]);
    }

    public function test_presence_is_tracked_for_authenticated_tenant_user(): void
    {
        $user = $this->tenantUser($this->companyA);
        $this->assertNull($user->last_seen_at);

        // Une requête authentifiée déclenche TrackUserPresence (groupe web).
        $this->actingAs($user)->get(route('dashboard'))->assertOk();

        $this->assertNotNull($user->fresh()->last_seen_at);
    }

    public function test_ip_allowlist_blocks_addresses_outside_the_list(): void
    {
        config()->set('app.role', 'support'); // contexte console
        $middleware = new SupportIpAllowlist;
        $next = fn ($r) => response('ok');

        // Sans liste configurée → tout passe (dev).
        putenv('SUPPORT_ALLOWED_IPS=');
        $this->assertSame('ok', $middleware->handle(Request::create('/tenants'), $next)->getContent());

        // Liste avec joker : IP hors plage refusée.
        putenv('SUPPORT_ALLOWED_IPS=10.0.0.*');
        $blocked = Request::create('/tenants', server: ['REMOTE_ADDR' => '192.168.1.5']);
        try {
            $middleware->handle($blocked, $next);
            $this->fail('Une IP hors allowlist aurait dû être refusée.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        // IP dans la plage autorisée.
        $allowed = Request::create('/tenants', server: ['REMOTE_ADDR' => '10.0.0.42']);
        $this->assertSame('ok', $middleware->handle($allowed, $next)->getContent());

        putenv('SUPPORT_ALLOWED_IPS');
    }
}
