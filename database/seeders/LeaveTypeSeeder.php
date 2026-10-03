<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $leaveTypes = [
            [
                'name' => 'Casual Leave',
                'code' => 'CL',
                'annual_allocation' => 12,
                'is_paid' => true,
                'status' => 'active',
            ],
            [
                'name' => 'Sick Leave',
                'code' => 'SL',
                'annual_allocation' => 10,
                'is_paid' => true,
                'status' => 'active',
            ],
            [
                'name' => 'Paid Leave',
                'code' => 'PL',
                'annual_allocation' => 15,
                'is_paid' => true,
                'status' => 'active',
            ],
            [
                'name' => 'Unpaid Leave',
                'code' => 'UL',
                'annual_allocation' => 0,
                'is_paid' => false,
                'status' => 'active',
            ],
            [
                'name' => 'Emergency Leave',
                'code' => 'EL',
                'annual_allocation' => 5,
                'is_paid' => true,
                'status' => 'active',
            ],
        ];

        foreach ($leaveTypes as $leaveType) {
            LeaveType::firstOrCreate(
                ['code' => $leaveType['code']],
                $leaveType
            );
        }
    }
}
