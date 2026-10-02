<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class HrmsDataSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Engineering', 'code' => 'ENG', 'description' => 'Software & Hardware development teams'],
            ['name' => 'Human Resources', 'code' => 'HR', 'description' => 'Talent acquisition, operations and employee relations'],
            ['name' => 'Marketing', 'code' => 'MKT', 'description' => 'Brand management, growth and content'],
            ['name' => 'Finance', 'code' => 'FIN', 'description' => 'Accounting, treasury and payroll management'],
            ['name' => 'Sales', 'code' => 'SLS', 'description' => 'Client acquisition and partnerships'],
            ['name' => 'Operations', 'code' => 'OPS', 'description' => 'Business operations and logistics'],
        ];

        $deptMap = [];
        foreach ($departments as $dept) {
            $created = Department::updateOrCreate(
                ['code' => $dept['code']],
                ['name' => $dept['name'], 'description' => $dept['description'], 'status' => 'active']
            );
            $deptMap[$dept['code']] = $created->id;
        }

        $employees = [
            [
                'employee_code' => 'EMP-001',
                'first_name' => 'Keval',
                'last_name' => 'Dhandhukya',
                'email' => 'keval@uesthrms.com',
                'phone' => '+91 98765 43210',
                'department_id' => $deptMap['ENG'] ?? null,
                'designation' => 'Lead Full-Stack Engineer',
                'joining_date' => '2023-01-15',
                'salary' => 85000.00,
                'status' => 'active',
                'address' => 'Ahmedabad, Gujarat',
            ],
            [
                'employee_code' => 'EMP-002',
                'first_name' => 'Rahul',
                'last_name' => 'Sharma',
                'email' => 'rahul.sharma@uesthrms.com',
                'phone' => '+91 98234 56781',
                'department_id' => $deptMap['ENG'] ?? null,
                'designation' => 'Backend Developer',
                'joining_date' => '2023-04-10',
                'salary' => 62000.00,
                'status' => 'active',
                'address' => 'Surat, Gujarat',
            ],
            [
                'employee_code' => 'EMP-003',
                'first_name' => 'Priya',
                'last_name' => 'Patel',
                'email' => 'priya.patel@uesthrms.com',
                'phone' => '+91 98111 22233',
                'department_id' => $deptMap['HR'] ?? null,
                'designation' => 'HR Specialist',
                'joining_date' => '2022-09-01',
                'salary' => 48000.00,
                'status' => 'active',
                'address' => 'Vadodara, Gujarat',
            ],
            [
                'employee_code' => 'EMP-004',
                'first_name' => 'Amit',
                'last_name' => 'Kumar',
                'email' => 'amit.kumar@uesthrms.com',
                'phone' => '+91 99000 88776',
                'department_id' => $deptMap['FIN'] ?? null,
                'designation' => 'Senior Accountant',
                'joining_date' => '2022-11-20',
                'salary' => 55000.00,
                'status' => 'active',
                'address' => 'Rajkot, Gujarat',
            ],
            [
                'employee_code' => 'EMP-005',
                'first_name' => 'Neha',
                'last_name' => 'Gupta',
                'email' => 'neha.gupta@uesthrms.com',
                'phone' => '+91 97654 32109',
                'department_id' => $deptMap['MKT'] ?? null,
                'designation' => 'Marketing Lead',
                'joining_date' => now()->subDays(20)->toDateString(),
                'salary' => 58000.00,
                'status' => 'active',
                'address' => 'Gandhinagar, Gujarat',
            ],
            [
                'employee_code' => 'EMP-006',
                'first_name' => 'Vikram',
                'last_name' => 'Singh',
                'email' => 'vikram.singh@uesthrms.com',
                'phone' => '+91 98450 12345',
                'department_id' => $deptMap['SLS'] ?? null,
                'designation' => 'Sales Executive',
                'joining_date' => now()->subDays(4)->toDateString(),
                'salary' => 42000.00,
                'status' => 'active',
                'address' => 'Ahmedabad, Gujarat',
            ],
        ];

        $empModels = [];
        foreach ($employees as $emp) {
            $created = Employee::updateOrCreate(['employee_code' => $emp['employee_code']], $emp);
            $empModels[$emp['employee_code']] = $created;
        }

        // 1. Seed Today's Attendance
        $today = now()->toDateString();
        $statuses = [
            'EMP-001' => ['status' => 'present', 'in' => '09:05:00', 'out' => null],
            'EMP-002' => ['status' => 'present', 'in' => '09:15:00', 'out' => null],
            'EMP-003' => ['status' => 'present', 'in' => '08:55:00', 'out' => null],
            'EMP-004' => ['status' => 'on_leave', 'in' => null, 'out' => null],
            'EMP-005' => ['status' => 'present', 'in' => '09:30:00', 'out' => null],
            'EMP-006' => ['status' => 'absent', 'in' => null, 'out' => null],
        ];

        foreach ($statuses as $code => $data) {
            if (isset($empModels[$code])) {
                \App\Models\Attendance::updateOrCreate(
                    ['employee_id' => $empModels[$code]->id, 'date' => $today],
                    ['clock_in' => $data['in'], 'clock_out' => $data['out'], 'status' => $data['status']]
                );
            }
        }

        // 2. Seed Pending Leaves
        $leaves = [
            [
                'code' => 'EMP-002',
                'type' => 'Sick Leave',
                'from_date' => now()->addDays(1)->toDateString(),
                'to_date' => now()->addDays(2)->toDateString(),
                'days' => 2,
                'reason' => 'Viral fever and doctor consultation',
                'status' => 'pending',
            ],
            [
                'code' => 'EMP-003',
                'type' => 'Casual Leave',
                'from_date' => now()->addDays(3)->toDateString(),
                'to_date' => now()->addDays(3)->toDateString(),
                'days' => 1,
                'reason' => 'Family function in hometown',
                'status' => 'pending',
            ],
            [
                'code' => 'EMP-004',
                'type' => 'Earned Leave',
                'from_date' => now()->toDateString(),
                'to_date' => now()->addDays(3)->toDateString(),
                'days' => 4,
                'reason' => 'Annual vacation planned with family',
                'status' => 'pending',
            ],
        ];

        foreach ($leaves as $l) {
            if (isset($empModels[$l['code']])) {
                \App\Models\Leave::updateOrCreate(
                    [
                        'employee_id' => $empModels[$l['code']]->id,
                        'from_date' => $l['from_date'],
                    ],
                    [
                        'type' => $l['type'],
                        'to_date' => $l['to_date'],
                        'days' => $l['days'],
                        'reason' => $l['reason'],
                        'status' => $l['status'],
                    ]
                );
            }
        }

        // 3. Seed Upcoming Holidays (using current and upcoming months)
        $currentYear = now()->year;
        $holidays = [
            ['name' => 'Gandhi Jayanti', 'date' => "{$currentYear}-10-02", 'type' => 'National', 'description' => 'Celebration of Mahatma Gandhi’s Birthday'],
            ['name' => 'Dussehra (Vijayadashami)', 'date' => "{$currentYear}-10-12", 'type' => 'Gazetted', 'description' => 'Victory of good over evil'],
            ['name' => 'Diwali (Deepavali)', 'date' => "{$currentYear}-11-01", 'type' => 'Gazetted', 'description' => 'Festival of Lights'],
            ['name' => 'Guru Nanak Jayanti', 'date' => "{$currentYear}-11-15", 'type' => 'Gazetted', 'description' => 'Birth of Guru Nanak Dev Ji'],
            ['name' => 'Christmas Day', 'date' => "{$currentYear}-12-25", 'type' => 'Gazetted', 'description' => 'Celebration of the Nativity of Jesus'],
            ['name' => 'New Year’s Day', 'date' => ($currentYear + 1) . "-01-01", 'type' => 'Restricted', 'description' => 'First day of the Gregorian year'],
        ];

        foreach ($holidays as $h) {
            \App\Models\Holiday::updateOrCreate(['name' => $h['name']], $h);
        }

        // 4. Seed Activity Logs
        $activities = [
            ['title' => 'Employee Vikram Singh onboarded', 'description' => 'Joined Sales department as Sales Executive', 'icon' => 'user-plus'],
            ['title' => 'System connected to MySQL', 'description' => 'uest_hrms database initialized and migrated', 'icon' => 'database'],
            ['title' => 'Department Operations added', 'description' => 'Operations team configured with code OPS', 'icon' => 'sitemap'],
            ['title' => 'Attendance policy initialized', 'description' => 'Standard 9:00 AM - 6:00 PM work shift configured', 'icon' => 'clock'],
        ];

        foreach ($activities as $act) {
            \App\Models\ActivityLog::updateOrCreate(['title' => $act['title']], $act);
        }
    }
}
