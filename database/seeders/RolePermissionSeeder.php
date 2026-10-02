<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Granular Permissions grouped by Module
        $modules = [
            'Employees' => [
                'employees.view' => 'View employee records',
                'employees.create' => 'Create new employees',
                'employees.edit' => 'Edit employee profiles',
                'employees.delete' => 'Delete employee records',
            ],
            'Departments' => [
                'departments.view' => 'View departments list',
                'departments.manage' => 'Create, edit & delete departments',
            ],
            'Attendance' => [
                'attendance.view' => 'View daily attendance logs',
                'attendance.mark' => 'Mark employee attendance / punches',
            ],
            'Leaves' => [
                'leaves.view' => 'View staff leave requests',
                'leaves.apply' => 'Submit leave requests',
                'leaves.approve' => 'Approve or reject leave applications',
            ],
            'Payroll' => [
                'payroll.view' => 'View payroll summary and records',
                'payroll.generate' => 'Generate monthly payroll batches',
                'payroll.disburse' => 'Mark salary disbursements as paid',
                'payroll.payslip' => 'View & print employee payslips',
            ],
            'Holidays' => [
                'holidays.view' => 'View organization holiday calendar',
                'holidays.manage' => 'Add, update & delete company holidays',
            ],
            'Settings' => [
                'settings.manage' => 'Manage system configuration & company profile',
                'roles.manage' => 'Manage user roles and access permissions',
            ],
        ];

        $permissionModels = [];
        foreach ($modules as $moduleName => $permissions) {
            foreach ($permissions as $slug => $desc) {
                $permissionModels[$slug] = Permission::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => ucwords(str_replace(['.', '_'], ' ', $slug)),
                        'module' => $moduleName,
                        'description' => $desc,
                    ]
                );
            }
        }

        // 2. Define Core Roles
        $rolesData = [
            [
                'name' => 'Super Administrator',
                'slug' => 'super-admin',
                'description' => 'Full unrestricted access to all HRMS administrative functions and configurations.',
                'is_system' => true,
                'permissions' => array_keys($permissionModels), // All permissions
            ],
            [
                'name' => 'HR Manager',
                'slug' => 'hr-manager',
                'description' => 'Manages employee lifecycles, attendance tracking, leave approvals, and payroll processing.',
                'is_system' => false,
                'permissions' => [
                    'employees.view', 'employees.create', 'employees.edit',
                    'departments.view', 'departments.manage',
                    'attendance.view', 'attendance.mark',
                    'leaves.view', 'leaves.approve',
                    'payroll.view', 'payroll.generate', 'payroll.disburse', 'payroll.payslip',
                    'holidays.view', 'holidays.manage',
                    'settings.manage',
                ],
            ],
            [
                'name' => 'Department Manager',
                'slug' => 'department-manager',
                'description' => 'Supervises department personnel, tracks team attendance, and authorizes leave requests.',
                'is_system' => false,
                'permissions' => [
                    'employees.view',
                    'departments.view',
                    'attendance.view', 'attendance.mark',
                    'leaves.view', 'leaves.approve',
                    'holidays.view',
                ],
            ],
            [
                'name' => 'Staff / Employee',
                'slug' => 'employee',
                'description' => 'Standard employee access to punch attendance, request leaves, and inspect personal payslips.',
                'is_system' => false,
                'permissions' => [
                    'attendance.view',
                    'leaves.view', 'leaves.apply',
                    'payroll.payslip',
                    'holidays.view',
                ],
            ],
        ];

        $roleModels = [];
        foreach ($rolesData as $r) {
            $role = Role::updateOrCreate(
                ['slug' => $r['slug']],
                [
                    'name' => $r['name'],
                    'description' => $r['description'],
                    'is_system' => $r['is_system'],
                ]
            );

            // Sync permissions to role
            $permIds = collect($r['permissions'])
                ->map(fn($slug) => $permissionModels[$slug]->id ?? null)
                ->filter();

            $role->permissions()->sync($permIds);
            $roleModels[$r['slug']] = $role;
        }

        // 3. Assign default roles to existing sample employees
        $empRoleMap = [
            'EMP-001' => 'super-admin', // Keval (Lead)
            'EMP-002' => 'employee',    // Rahul (Developer)
            'EMP-003' => 'hr-manager',  // Priya (HR Specialist)
            'EMP-004' => 'department-manager', // Amit (Finance Lead)
            'EMP-005' => 'department-manager', // Neha (Marketing Lead)
            'EMP-006' => 'employee',    // Vikram (Sales)
        ];

        foreach ($empRoleMap as $code => $roleSlug) {
            if (isset($roleModels[$roleSlug])) {
                Employee::where('employee_code', $code)->update(['role_id' => $roleModels[$roleSlug]->id]);
            }
        }
    }
}
