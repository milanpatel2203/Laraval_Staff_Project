<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Roles & Permissions first
        $this->call([
            RolePermissionSeeder::class,
            HrmsDataSeeder::class,
        ]);

        $superAdminRole = \App\Models\Role::where('slug', 'super-admin')->first();
        $superAdminRoleId = $superAdminRole ? $superAdminRole->id : null;

        // Ensure all permissions are synced to Super Admin role
        if ($superAdminRole) {
            $allPermissionIds = \App\Models\Permission::pluck('id');
            $superAdminRole->permissions()->sync($allPermissionIds);
        }

        // Avoid mobile uniqueness collision if existing row has 9876543210
        User::where('mobile', '9876543210')->update(['mobile' => null]);

        // 2. Keval Super Admin Account (Full Permissions)
        User::updateOrCreate(
            ['email' => 'keval192837@gmail.com'],
            [
                'name' => 'Keval Patel',
                'mobile' => '9876543212',
                'phone' => '+91 98765 43212',
                'role_title' => 'Super Administrator',
                'role_id' => $superAdminRoleId,
                'bio' => 'Super Administrator with full unrestricted system permissions.',
                'password' => 'admin123',
            ]
        );

        // 3. Primary Administrator (Super Admin)
        User::updateOrCreate(
            ['email' => 'admin@uest.com'],
            [
                'name' => 'Administrator',
                'mobile' => '9876543210',
                'phone' => '+91 98765 43210',
                'role_title' => 'Super Administrator',
                'role_id' => $superAdminRoleId,
                'bio' => 'System Super Administrator.',
                'password' => 'admin123',
            ]
        );

        // 4. Secondary Admin Account
        User::updateOrCreate(
            ['email' => 'admin@uesthrms.com'],
            [
                'name' => 'Administrator',
                'mobile' => '9876543219',
                'phone' => '+91 98765 43219',
                'role_title' => 'Super Administrator',
                'role_id' => $superAdminRoleId,
                'bio' => 'System Super Administrator.',
                'password' => 'admin123',
            ]
        );

        $hrRoleId = \App\Models\Role::where('slug', 'hr-manager')->value('id');
        $deptRoleId = \App\Models\Role::where('slug', 'department-manager')->value('id');
        $staffRoleId = \App\Models\Role::where('slug', 'employee')->value('id');

        // 5. HR Manager Account (Priya Patel)
        User::updateOrCreate(
            ['email' => 'priya.patel@uesthrms.com'],
            [
                'name' => 'Priya Patel',
                'mobile' => '9811122233',
                'phone' => '+91 98111 22233',
                'role_title' => 'HR Manager',
                'role_id' => $hrRoleId,
                'bio' => 'HR Manager overseeing employees, attendance, payroll, and recruitment.',
                'password' => 'admin123',
            ]
        );

        // Convenient HR login alias
        User::updateOrCreate(
            ['email' => 'hr@uesthrms.com'],
            [
                'name' => 'HR Specialist',
                'mobile' => '9811122234',
                'phone' => '+91 98111 22234',
                'role_title' => 'HR Manager',
                'role_id' => $hrRoleId,
                'bio' => 'HR Specialist account.',
                'password' => 'admin123',
            ]
        );

        // 6. Department Manager Account (Amit Kumar)
        User::updateOrCreate(
            ['email' => 'amit.kumar@uesthrms.com'],
            [
                'name' => 'Amit Kumar',
                'mobile' => '9900088776',
                'phone' => '+91 99000 88776',
                'role_title' => 'Department Manager',
                'role_id' => $deptRoleId,
                'bio' => 'Department Manager supervising team attendance, projects, and leave authorizations.',
                'password' => 'admin123',
            ]
        );

        // Convenient Manager login alias
        User::updateOrCreate(
            ['email' => 'manager@uesthrms.com'],
            [
                'name' => 'Department Lead',
                'mobile' => '9900088777',
                'phone' => '+91 99000 88777',
                'role_title' => 'Department Manager',
                'role_id' => $deptRoleId,
                'bio' => 'Department Lead account.',
                'password' => 'admin123',
            ]
        );

        // 7. Staff / Employee Account (Vikram Singh)
        User::updateOrCreate(
            ['email' => 'vikram.singh@uesthrms.com'],
            [
                'name' => 'Vikram Singh',
                'mobile' => '9845012345',
                'phone' => '+91 98450 12345',
                'role_title' => 'Staff / Employee',
                'role_id' => $staffRoleId,
                'bio' => 'Standard employee account for attendance punch, leaves, and payslips.',
                'password' => 'admin123',
            ]
        );

        // Convenient Staff login alias
        User::updateOrCreate(
            ['email' => 'staff@uesthrms.com'],
            [
                'name' => 'Employee Staff',
                'mobile' => '9845012346',
                'phone' => '+91 98450 12346',
                'role_title' => 'Staff / Employee',
                'role_id' => $staffRoleId,
                'bio' => 'Standard staff account.',
                'password' => 'admin123',
            ]
        );

        // 8. Sample Leaves for Staff (Vikram Singh)
        $vikram = \App\Models\Employee::where('email', 'vikram.singh@uesthrms.com')->first();
        if ($vikram) {
            \App\Models\Leave::firstOrCreate(
                ['employee_id' => $vikram->id, 'from_date' => '2026-10-10', 'to_date' => '2026-10-11'],
                [
                    'type' => 'Casual Leave',
                    'days' => 2,
                    'reason' => 'Attending family wedding function in hometown.',
                    'status' => 'pending',
                ]
            );
            \App\Models\Leave::firstOrCreate(
                ['employee_id' => $vikram->id, 'from_date' => '2026-09-15', 'to_date' => '2026-09-15'],
                [
                    'type' => 'Sick Leave',
                    'days' => 1,
                    'reason' => 'Doctor appointment and fever rest.',
                    'status' => 'approved',
                ]
            );
        }
    }
}
