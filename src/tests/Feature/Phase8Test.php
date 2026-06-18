<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Phase8Test extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->company = Company::create(['name' => 'Demo']);
        $this->admin = $this->user(User::ROLE_ADMIN, 'admin@test.local');
    }

    private function user(string $role, string $email): User
    {
        return User::create([
            'company_id' => $this->company->id, 'name' => ucfirst(explode('@', $email)[0]),
            'email' => $email, 'password' => Hash::make('password'), 'role' => $role,
        ]);
    }

    private function leaveFor(User $u, array $attrs = []): LeaveRequest
    {
        return LeaveRequest::create(array_merge([
            'company_id' => $this->company->id, 'user_id' => $u->id, 'type' => 'conges_payes',
            'start_date' => now()->addWeek(), 'end_date' => now()->addWeeks(2), 'status' => 'pending',
        ], $attrs));
    }

    public function test_employee_can_submit_request(): void
    {
        $emp = $this->user(User::ROLE_EMPLOYE, 'paul@test.local');

        $this->actingAs($emp)->post(route('leaves.store'), [
            'type' => 'conges_payes',
            'start_date' => now()->addWeek()->toDateString(),
            'end_date' => now()->addWeeks(2)->toDateString(),
            'reason' => 'Vacances',
        ])->assertRedirect(route('leaves.index'));

        $this->assertDatabaseHas('leave_requests', [
            'user_id' => $emp->id, 'status' => 'pending', 'reason' => 'Vacances',
        ]);
    }

    public function test_employee_sees_only_own_requests(): void
    {
        $paul = $this->user(User::ROLE_EMPLOYE, 'paul@test.local');
        $marie = $this->user(User::ROLE_EMPLOYE, 'marie@test.local');
        $this->leaveFor($paul, ['reason' => 'RaisonPaul']);
        $this->leaveFor($marie, ['reason' => 'RaisonMarie']);

        $this->actingAs($paul)->get(route('leaves.index'))
            ->assertOk()->assertSee('RaisonPaul')->assertDontSee('RaisonMarie');
    }

    public function test_manager_sees_all_requests(): void
    {
        $paul = $this->user(User::ROLE_EMPLOYE, 'paul@test.local');
        $this->leaveFor($paul);

        $this->actingAs($this->admin)->get(route('leaves.index'))
            ->assertOk()->assertSee('Paul');
    }

    public function test_employee_cannot_approve(): void
    {
        $paul = $this->user(User::ROLE_EMPLOYE, 'paul@test.local');
        $leave = $this->leaveFor($paul);

        $this->actingAs($paul)->patch(route('leaves.approve', $leave))->assertForbidden();
    }

    public function test_manager_can_approve_and_reject(): void
    {
        $paul = $this->user(User::ROLE_EMPLOYE, 'paul@test.local');
        $leave = $this->leaveFor($paul);

        $this->actingAs($this->admin)->patch(route('leaves.approve', $leave), ['review_comment' => 'OK'])
            ->assertRedirect();
        $leave->refresh();
        $this->assertSame('approved', $leave->status);
        $this->assertSame($this->admin->id, $leave->reviewed_by);

        $other = $this->leaveFor($paul);
        $this->actingAs($this->admin)->patch(route('leaves.reject', $other))->assertRedirect();
        $this->assertSame('rejected', $other->fresh()->status);
    }

    public function test_owner_can_cancel_pending_only(): void
    {
        $paul = $this->user(User::ROLE_EMPLOYE, 'paul@test.local');
        $leave = $this->leaveFor($paul);

        $this->actingAs($paul)->patch(route('leaves.cancel', $leave))->assertRedirect();
        $this->assertSame('cancelled', $leave->fresh()->status);

        $approved = $this->leaveFor($paul, ['status' => 'approved']);
        $this->actingAs($paul)->patch(route('leaves.cancel', $approved))->assertSessionHasErrors('leave');
    }

    public function test_cannot_approve_already_processed(): void
    {
        $paul = $this->user(User::ROLE_EMPLOYE, 'paul@test.local');
        $leave = $this->leaveFor($paul, ['status' => 'approved']);

        $this->actingAs($this->admin)->patch(route('leaves.approve', $leave))->assertSessionHasErrors('leave');
    }
}
