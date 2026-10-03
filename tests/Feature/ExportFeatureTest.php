<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_exports_work()
    {
        $role = Role::create([
            'name' => 'Super Administrator',
            'slug' => 'super-admin',
            'is_system' => true,
            'permissions' => ['*'],
        ]);

        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'mobile' => '9999999999',
            'password' => bcrypt('password'),
            'role_title' => 'Super Administrator',
            'role_id' => null,
        ]);

        $dept = Department::create([
            'name' => 'Engineering',
            'code' => 'ENG',
            'status' => 'active',
        ]);

        $employee = Employee::create([
            'employee_code' => 'EMP001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'designation' => 'Developer',
            'department_id' => $dept->id,
            'status' => 'active',
            'salary' => 50000,
            'joining_date' => now()->toDateString(),
        ]);

        $this->actingAs($admin);

        foreach (['employees.export', 'attendance.export', 'payroll.export', 'leaves.export'] as $routeName) {
            $response = $this->get(route($routeName));
            $response->assertStatus(200);
            $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
        }
    }
}
