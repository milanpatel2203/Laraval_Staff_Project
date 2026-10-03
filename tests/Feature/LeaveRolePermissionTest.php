<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Leave;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveRolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setupRoles(): array
    {
        $superAdminRole = Role::create(['name' => 'Super Administrator', 'slug' => 'super-admin', 'is_system' => true]);
        
        $staffRole = Role::create(['name' => 'Staff / Employee', 'slug' => 'employee', 'is_system' => false]);
        $hrRole = Role::create(['name' => 'HR Manager', 'slug' => 'hr-manager', 'is_system' => false]);

        $permApply = \App\Models\Permission::create(['name' => 'Leaves Apply', 'slug' => 'leaves.apply', 'module' => 'Leaves']);
        $permView = \App\Models\Permission::create(['name' => 'Leaves View', 'slug' => 'leaves.view', 'module' => 'Leaves']);
        $permApprove = \App\Models\Permission::create(['name' => 'Leaves Approve', 'slug' => 'leaves.approve', 'module' => 'Leaves']);

        $staffRole->permissions()->sync([$permView->id, $permApply->id]);
        $hrRole->permissions()->sync([$permView->id, $permApply->id, $permApprove->id]);

        return [$superAdminRole, $hrRole, $staffRole];
    }

    public function test_staff_sees_only_own_leaves(): void
    {
        [$superRole, $hrRole, $staffRole] = $this->setupRoles();

        $staffEmp = Employee::create([
            'employee_code' => 'EMP-010',
            'first_name' => 'Staff',
            'last_name' => 'Member',
            'email' => 'staff@example.com',
            'designation' => 'Developer',
            'joining_date' => now()->toDateString(),
            'salary' => 45000,
            'status' => 'active',
            'role_id' => $staffRole->id,
        ]);

        $otherEmp = Employee::create([
            'employee_code' => 'EMP-020',
            'first_name' => 'Other',
            'last_name' => 'Colleague',
            'email' => 'other@example.com',
            'designation' => 'Designer',
            'joining_date' => now()->toDateString(),
            'salary' => 50000,
            'status' => 'active',
            'role_id' => $staffRole->id,
        ]);

        $staffLeave = Leave::create([
            'employee_id' => $staffEmp->id,
            'type' => 'Casual Leave',
            'from_date' => now()->toDateString(),
            'to_date' => now()->addDay()->toDateString(),
            'days' => 2,
            'reason' => 'Staff private trip',
            'status' => 'pending',
        ]);

        $otherLeave = Leave::create([
            'employee_id' => $otherEmp->id,
            'type' => 'Sick Leave',
            'from_date' => now()->toDateString(),
            'to_date' => now()->addDays(3)->toDateString(),
            'days' => 4,
            'reason' => 'Other medical leave',
            'status' => 'pending',
        ]);

        $staffUser = User::create([
            'name' => 'Staff Member',
            'email' => 'staff@example.com',
            'mobile' => '9876543299',
            'password' => bcrypt('password'),
            'role_title' => 'Staff / Employee',
            'role_id' => $staffRole->id,
        ]);

        $response = $this->actingAs($staffUser)->get(route('leaves.index'));
        $response->assertOk();

        $leaves = $response->viewData('leaves');
        $this->assertTrue($leaves->contains('id', $staffLeave->id));
        $this->assertFalse($leaves->contains('id', $otherLeave->id));
    }

    public function test_staff_cannot_approve_or_reject_leaves(): void
    {
        [$superRole, $hrRole, $staffRole] = $this->setupRoles();

        $emp = Employee::create([
            'employee_code' => 'EMP-011',
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'email' => 'alice@example.com',
            'designation' => 'Support',
            'joining_date' => now()->toDateString(),
            'salary' => 40000,
            'status' => 'active',
            'role_id' => $staffRole->id,
        ]);

        $leave = Leave::create([
            'employee_id' => $emp->id,
            'type' => 'Casual Leave',
            'from_date' => now()->toDateString(),
            'to_date' => now()->toDateString(),
            'days' => 1,
            'reason' => 'Doctor appointment',
            'status' => 'pending',
        ]);

        $staffUser = User::create([
            'name' => 'Staff Member',
            'email' => 'alice@example.com',
            'mobile' => '9876543298',
            'password' => bcrypt('password'),
            'role_title' => 'Staff / Employee',
            'role_id' => $staffRole->id,
        ]);

        // Attempt approve
        $responseApprove = $this->actingAs($staffUser)->post(route('leaves.approve', $leave->id));
        $responseApprove->assertSessionHas('error');
        $leave->refresh();
        $this->assertEquals('pending', $leave->status);

        // Attempt reject
        $responseReject = $this->actingAs($staffUser)->post(route('leaves.reject', $leave->id));
        $responseReject->assertSessionHas('error');
        $leave->refresh();
        $this->assertEquals('pending', $leave->status);
    }

    public function test_staff_can_apply_for_leave(): void
    {
        [$superRole, $hrRole, $staffRole] = $this->setupRoles();

        $emp = Employee::create([
            'employee_code' => 'EMP-012',
            'first_name' => 'Bob',
            'last_name' => 'Brown',
            'email' => 'bob@example.com',
            'designation' => 'QA Engineer',
            'joining_date' => now()->toDateString(),
            'salary' => 42000,
            'status' => 'active',
            'role_id' => $staffRole->id,
        ]);

        $staffUser = User::create([
            'name' => 'Bob Brown',
            'email' => 'bob@example.com',
            'mobile' => '9876543297',
            'password' => bcrypt('password'),
            'role_title' => 'Staff / Employee',
            'role_id' => $staffRole->id,
        ]);

        $response = $this->actingAs($staffUser)->post(route('leaves.store'), [
            'type' => 'Sick Leave',
            'from_date' => now()->addDays(2)->toDateString(),
            'to_date' => now()->addDays(3)->toDateString(),
            'reason' => 'Medical dental rest',
        ]);

        $response->assertRedirect(route('leaves.index'));
        $this->assertDatabaseHas('leaves', [
            'employee_id' => $emp->id,
            'type' => 'Sick Leave',
            'days' => 2,
            'status' => 'pending',
            'reason' => 'Medical dental rest',
        ]);
    }

    public function test_unmarking_leaves_apply_hides_apply_button_and_blocks_store_for_super_admin(): void
    {
        [$superRole, $hrRole, $staffRole] = $this->setupRoles();

        // Grant leaves.view to superRole, but NOT leaves.apply
        $permView = \App\Models\Permission::where('slug', 'leaves.view')->first();
        $superRole->permissions()->sync([$permView->id]);

        $superAdmin = User::create([
            'name' => 'Super User',
            'email' => 'super@example.com',
            'mobile' => '9998887776',
            'password' => bcrypt('password'),
            'role_title' => 'Super Administrator',
            'role_id' => $superRole->id,
        ]);

        $response = $this->actingAs($superAdmin)->get(route('leaves.index'));
        $response->assertOk();
        $this->assertFalse($response->viewData('canApplyLeave'));
        $response->assertDontSee('Apply for Leave');

        // Attempting to post leave request without leaves.apply permission is blocked
        $postResponse = $this->actingAs($superAdmin)->post(route('leaves.store'), [
            'type' => 'Casual Leave',
            'from_date' => now()->toDateString(),
            'to_date' => now()->toDateString(),
            'reason' => 'Should be unauthorized',
        ]);
        $postResponse->assertSessionHas('error');
    }

    public function test_granting_leaves_apply_shows_apply_button_and_allows_store(): void
    {
        [$superRole, $hrRole, $staffRole] = $this->setupRoles();

        $permView = \App\Models\Permission::where('slug', 'leaves.view')->first();
        $permApply = \App\Models\Permission::where('slug', 'leaves.apply')->first();
        $superRole->permissions()->sync([$permView->id, $permApply->id]);

        $superEmp = Employee::create([
            'employee_code' => 'EMP-001',
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'super2@example.com',
            'designation' => 'Director',
            'joining_date' => now()->toDateString(),
            'salary' => 100000,
            'status' => 'active',
            'role_id' => $superRole->id,
        ]);

        $superAdmin = User::create([
            'name' => 'Super User 2',
            'email' => 'super2@example.com',
            'mobile' => '9998887775',
            'password' => bcrypt('password'),
            'role_title' => 'Super Administrator',
            'role_id' => $superRole->id,
        ]);

        $response = $this->actingAs($superAdmin)->get(route('leaves.index'));
        $response->assertOk();
        $this->assertTrue($response->viewData('canApplyLeave'));
        $response->assertSee('Apply for Leave');

        $postResponse = $this->actingAs($superAdmin)->post(route('leaves.store'), [
            'type' => 'Casual Leave',
            'from_date' => now()->toDateString(),
            'to_date' => now()->toDateString(),
            'reason' => 'Annual summit',
        ]);
        $postResponse->assertRedirect(route('leaves.index'));
        $postResponse->assertSessionHas('success');
    }
}
