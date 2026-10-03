<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function makeSuperAdmin(): User
    {
        $role = Role::create([
            'name' => 'Super Administrator',
            'slug' => 'super-admin',
            'is_system' => true,
        ]);

        return User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'mobile' => '98765' . rand(10000, 99999),
            'password' => bcrypt('password'),
            'role_title' => 'Super Administrator',
            'role_id' => $role->id,
        ]);
    }

    protected function makeRegularUser(): User
    {
        $role = Role::create([
            'name' => 'Staff / Employee',
            'slug' => 'employee',
            'is_system' => false,
        ]);

        return User::create([
            'name' => 'Regular User',
            'email' => 'staff@example.com',
            'mobile' => '98765' . rand(10000, 99999),
            'password' => bcrypt('password'),
            'role_title' => 'Staff / Employee',
            'role_id' => $role->id,
        ]);
    }

    protected function makeEmployee(array $attributes = []): Employee
    {
        return Employee::create(array_merge([
            'employee_code' => 'EMP-' . rand(100, 999),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe' . uniqid() . '@example.com',
            'designation' => 'Developer',
            'joining_date' => now()->toDateString(),
            'salary' => 50000,
            'status' => 'active',
        ], $attributes));
    }

    public function test_super_admin_can_assign_role_to_employee(): void
    {
        $admin = $this->makeSuperAdmin();
        $targetRole = Role::create([
            'name' => 'HR Manager',
            'slug' => 'hr-manager',
            'is_system' => false,
        ]);

        $employee = $this->makeEmployee(['email' => 'employee.linked@example.com']);
        $linkedUser = User::create([
            'name' => 'Linked User',
            'email' => 'employee.linked@example.com',
            'mobile' => '98765' . rand(10000, 99999),
            'password' => bcrypt('password'),
            'role_title' => 'Staff / Employee',
        ]);

        $response = $this->actingAs($admin)
            ->postJson(route('roles.assignEmployee'), [
                'employee_id' => $employee->id,
                'role_id' => $targetRole->id,
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'role_id' => $targetRole->id,
                'role_name' => 'HR Manager',
            ]);

        $employee->refresh();
        $this->assertEquals($targetRole->id, $employee->role_id);

        $linkedUser->refresh();
        $this->assertEquals($targetRole->id, $linkedUser->role_id);
        $this->assertEquals('HR Manager', $linkedUser->role_title);
    }

    public function test_super_admin_can_bulk_assign_role_to_employees(): void
    {
        $admin = $this->makeSuperAdmin();
        $targetRole = Role::create([
            'name' => 'Department Manager',
            'slug' => 'department-manager',
            'is_system' => false,
        ]);

        $emp1 = $this->makeEmployee();
        $emp2 = $this->makeEmployee();

        $response = $this->actingAs($admin)
            ->postJson(route('roles.bulkAssign'), [
                'role_id' => $targetRole->id,
                'employee_ids' => [$emp1->id, $emp2->id],
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'count' => 2,
                'role_name' => 'Department Manager',
            ]);

        $emp1->refresh();
        $emp2->refresh();
        $this->assertEquals($targetRole->id, $emp1->role_id);
        $this->assertEquals($targetRole->id, $emp2->role_id);
    }

    public function test_non_super_admin_cannot_assign_employee_roles(): void
    {
        $regularUser = $this->makeRegularUser();
        $employee = $this->makeEmployee();
        $targetRole = Role::create([
            'name' => 'Super Administrator',
            'slug' => 'super-admin',
            'is_system' => true,
        ]);

        $response = $this->actingAs($regularUser)
            ->postJson(route('roles.assignEmployee'), [
                'employee_id' => $employee->id,
                'role_id' => $targetRole->id,
            ]);

        $response->assertStatus(403);
    }

    public function test_super_admin_profile_cannot_be_modified(): void
    {
        $admin = $this->makeSuperAdmin();
        $superAdminRole = Role::where('slug', 'super-admin')->first();
        $adminEmployee = $this->makeEmployee([
            'email' => $admin->email,
            'role_id' => $superAdminRole->id,
        ]);
        $targetRole = Role::create([
            'name' => 'HR Manager',
            'slug' => 'hr-manager',
            'is_system' => false,
        ]);

        $response = $this->actingAs($admin)
            ->postJson(route('roles.assignEmployee'), [
                'employee_id' => $adminEmployee->id,
                'role_id' => $targetRole->id,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        $adminEmployee->refresh();
        $this->assertEquals($superAdminRole->id, $adminEmployee->role_id);
    }

    public function test_super_admin_role_cannot_be_assigned_to_other_employees(): void
    {
        $admin = $this->makeSuperAdmin();
        $superAdminRole = Role::where('slug', 'super-admin')->first();
        $employee = $this->makeEmployee();

        $response = $this->actingAs($admin)
            ->postJson(route('roles.assignEmployee'), [
                'employee_id' => $employee->id,
                'role_id' => $superAdminRole->id,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_super_admin_is_excluded_from_assignable_employees_list(): void
    {
        $admin = $this->makeSuperAdmin();
        $superAdminRole = Role::where('slug', 'super-admin')->first();
        $adminEmp = $this->makeEmployee([
            'email' => $admin->email,
            'role_id' => $superAdminRole->id,
        ]);
        $regularEmp = $this->makeEmployee();

        $response = $this->actingAs($admin)->get(route('roles.index'));
        $response->assertOk();

        $employeesInView = $response->viewData('employees');
        $this->assertFalse($employeesInView->contains('id', $adminEmp->id));
        $this->assertTrue($employeesInView->contains('id', $regularEmp->id));
    }
}
