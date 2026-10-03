<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'description' => 'Full system access',
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Administrative access',
            ],
            [
                'name' => 'HR Manager',
                'slug' => 'hr-manager',
                'description' => 'Human resource management access',
            ],
            [
                'name' => 'Manager',
                'slug' => 'manager',
                'description' => 'Department and team management access',
            ],
            [
                'name' => 'Staff',
                'slug' => 'staff',
                'description' => 'Basic employee access',
            ],
            [
                'name' => 'Team Leader',
                'slug' => 'team-leader',
                'description' => 'Team management access',
            ],
        ];
        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                [
                    'slug' => $role['slug'],
                    'description' => $role['description'],
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */

        $permissions = [

            // Dashboard
            [
                'name' => 'View Dashboard',
                'slug' => 'dashboard.view',
                'module' => 'dashboard',
                'description' => 'View dashboard',
            ],

            // Employees
            [
                'name' => 'View Employees',
                'slug' => 'employees.view',
                'module' => 'employees',
                'description' => 'View employees',
            ],
            [
                'name' => 'Create Employees',
                'slug' => 'employees.create',
                'module' => 'employees',
                'description' => 'Create employees',
            ],
            [
                'name' => 'Edit Employees',
                'slug' => 'employees.edit',
                'module' => 'employees',
                'description' => 'Edit employees',
            ],
            [
                'name' => 'Delete Employees',
                'slug' => 'employees.delete',
                'module' => 'employees',
                'description' => 'Delete employees',
            ],

            // Departments
            [
                'name' => 'View Departments',
                'slug' => 'departments.view',
                'module' => 'departments',
                'description' => 'View departments',
            ],
            [
                'name' => 'Create Departments',
                'slug' => 'departments.create',
                'module' => 'departments',
                'description' => 'Create departments',
            ],
            [
                'name' => 'Edit Departments',
                'slug' => 'departments.edit',
                'module' => 'departments',
                'description' => 'Edit departments',
            ],
            [
                'name' => 'Delete Departments',
                'slug' => 'departments.delete',
                'module' => 'departments',
                'description' => 'Delete departments',
            ],

            // Teams
            [
                'name' => 'View Teams',
                'slug' => 'teams.view',
                'module' => 'teams',
                'description' => 'View teams',
            ],
            [
                'name' => 'Create Teams',
                'slug' => 'teams.create',
                'module' => 'teams',
                'description' => 'Create teams',
            ],
            [
                'name' => 'Edit Teams',
                'slug' => 'teams.edit',
                'module' => 'teams',
                'description' => 'Edit teams',
            ],
            [
                'name' => 'Delete Teams',
                'slug' => 'teams.delete',
                'module' => 'teams',
                'description' => 'Delete teams',
            ],

            // Tasks
            [
                'name' => 'View Tasks',
                'slug' => 'tasks.view',
                'module' => 'tasks',
                'description' => 'View tasks',
            ],
            [
                'name' => 'Create Tasks',
                'slug' => 'tasks.create',
                'module' => 'tasks',
                'description' => 'Create tasks',
            ],
            [
                'name' => 'Edit Tasks',
                'slug' => 'tasks.edit',
                'module' => 'tasks',
                'description' => 'Edit tasks',
            ],
            [
                'name' => 'Delete Tasks',
                'slug' => 'tasks.delete',
                'module' => 'tasks',
                'description' => 'Delete tasks',
            ],
            [
                'name' => 'Assign Tasks',
                'slug' => 'tasks.assign',
                'module' => 'tasks',
                'description' => 'Assign tasks to employees',
            ],
            [
                'name' => 'Reassign Tasks',
                'slug' => 'tasks.reassign',
                'module' => 'tasks',
                'description' => 'Reassign tasks to other employees',
            ],

            // Attendance
            [
                'name' => 'View Attendance',
                'slug' => 'attendance.view',
                'module' => 'attendance',
                'description' => 'View attendance',
            ],
            [
                'name' => 'Create Attendance',
                'slug' => 'attendance.create',
                'module' => 'attendance',
                'description' => 'Create attendance',
            ],
            [
                'name' => 'Edit Attendance',
                'slug' => 'attendance.edit',
                'module' => 'attendance',
                'description' => 'Edit attendance',
            ],
            [
                'name' => 'Delete Attendance',
                'slug' => 'attendance.delete',
                'module' => 'attendance',
                'description' => 'Delete attendance',
            ],

            // Leaves
            [
                'name' => 'View Leaves',
                'slug' => 'leaves.view',
                'module' => 'leaves',
                'description' => 'View leave requests',
            ],
            [
                'name' => 'Create Leaves',
                'slug' => 'leaves.create',
                'module' => 'leaves',
                'description' => 'Create leave requests',
            ],
            [
                'name' => 'Edit Leaves',
                'slug' => 'leaves.edit',
                'module' => 'leaves',
                'description' => 'Edit leave requests',
            ],
            [
                'name' => 'Approve Leaves',
                'slug' => 'leaves.approve',
                'module' => 'leaves',
                'description' => 'Approve leave requests',
            ],
            [
                'name' => 'Reject Leaves',
                'slug' => 'leaves.reject',
                'module' => 'leaves',
                'description' => 'Reject leave requests',
            ],

            // Payroll
            [
                'name' => 'View Payroll',
                'slug' => 'payroll.view',
                'module' => 'payroll',
                'description' => 'View payroll',
            ],
            [
                'name' => 'Create Payroll',
                'slug' => 'payroll.create',
                'module' => 'payroll',
                'description' => 'Create payroll',
            ],
            [
                'name' => 'Edit Payroll',
                'slug' => 'payroll.edit',
                'module' => 'payroll',
                'description' => 'Edit payroll',
            ],
            [
                'name' => 'Delete Payroll',
                'slug' => 'payroll.delete',
                'module' => 'payroll',
                'description' => 'Delete payroll',
            ],

            // Holidays
            [
                'name' => 'View Holidays',
                'slug' => 'holidays.view',
                'module' => 'holidays',
                'description' => 'View holidays',
            ],
            [
                'name' => 'Create Holidays',
                'slug' => 'holidays.create',
                'module' => 'holidays',
                'description' => 'Create holidays',
            ],
            [
                'name' => 'Edit Holidays',
                'slug' => 'holidays.edit',
                'module' => 'holidays',
                'description' => 'Edit holidays',
            ],
            [
                'name' => 'Delete Holidays',
                'slug' => 'holidays.delete',
                'module' => 'holidays',
                'description' => 'Delete holidays',
            ],

            // Roles
            [
                'name' => 'View Roles',
                'slug' => 'roles.view',
                'module' => 'roles',
                'description' => 'View roles and permissions',
            ],
            [
                'name' => 'Create Roles',
                'slug' => 'roles.create',
                'module' => 'roles',
                'description' => 'Create roles',
            ],
            [
                'name' => 'Edit Roles',
                'slug' => 'roles.edit',
                'module' => 'roles',
                'description' => 'Edit roles',
            ],
            [
                'name' => 'Delete Roles',
                'slug' => 'roles.delete',
                'module' => 'roles',
                'description' => 'Delete roles',
            ],

            // Settings
            [
                'name' => 'View Settings',
                'slug' => 'settings.view',
                'module' => 'settings',
                'description' => 'View settings',
            ],
            [
                'name' => 'Edit Settings',
                'slug' => 'settings.edit',
                'module' => 'settings',
                'description' => 'Edit settings',
            ],

            // Profile
            [
                'name' => 'View Profile',
                'slug' => 'profile.view',
                'module' => 'profile',
                'description' => 'View profile',
            ],
            [
                'name' => 'Edit Profile',
                'slug' => 'profile.edit',
                'module' => 'profile',
                'description' => 'Edit profile',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | Create Permissions
        |--------------------------------------------------------------------------
        */

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                [
                    'name' => $permission['name'],
                    'module' => $permission['module'],
                    'description' => $permission['description'],
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Super Admin → All Permissions
        |--------------------------------------------------------------------------
        */

        $superAdmin = Role::where('name', 'Super Admin')->first();

        $allPermissions = Permission::all();

        $superAdmin->permissions()->sync(
            $allPermissions->pluck('id')->toArray()
        );

        /*
        |--------------------------------------------------------------------------
        | HR Manager Permissions
        |--------------------------------------------------------------------------
        */

        $hrManager = Role::where('slug', 'hr-manager')->first();
        $hrManagerPermissions = Permission::whereIn('module', ['dashboard', 'employees', 'departments', 'teams', 'tasks', 'attendance', 'leaves', 'payroll', 'holidays', 'roles', 'settings', 'profile'])
            ->pluck('id')
            ->toArray();
        $hrManager->permissions()->sync($hrManagerPermissions);

        /*
        |--------------------------------------------------------------------------
        | Manager Permissions
        |--------------------------------------------------------------------------
        */

        $manager = Role::where('slug', 'manager')->first();
        $managerPermissions = Permission::whereIn('module', ['dashboard', 'employees', 'departments', 'teams', 'tasks', 'attendance', 'leaves', 'payroll', 'holidays', 'profile'])
            ->pluck('id')
            ->toArray();
        $manager->permissions()->sync($managerPermissions);

        /*
        |--------------------------------------------------------------------------
        | Team Leader Permissions
        |--------------------------------------------------------------------------
        */

        $teamLeader = Role::where('slug', 'team-leader')->first();
        $teamLeaderPermissions = Permission::whereIn('module', ['dashboard', 'employees', 'teams', 'tasks', 'attendance', 'leaves', 'profile'])
            ->pluck('id')
            ->toArray();
        $teamLeader->permissions()->sync($teamLeaderPermissions);

        /*
        |--------------------------------------------------------------------------
        | Staff Permissions
        |--------------------------------------------------------------------------
        */

        $staff = Role::where('slug', 'staff')->first();
        $staffPermissions = Permission::whereIn('module', ['dashboard', 'tasks', 'attendance', 'leaves', 'profile'])
            ->pluck('id')
            ->toArray();
        $staff->permissions()->sync($staffPermissions);
    }
}
