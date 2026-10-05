<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Holiday;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PaginationTestSeeder extends Seeder
{
    public function run(): void
    {
        // --- Dummy Roles (15 extra) ---
        $roles = [
            ['name' => 'Content Manager', 'description' => 'Manages website and internal content publishing.'],
            ['name' => 'Finance Lead', 'description' => 'Oversees financial reporting and budgets.'],
            ['name' => 'IT Support', 'description' => 'Handles IT infrastructure and employee tech support.'],
            ['name' => 'Marketing Coordinator', 'description' => 'Plans and executes marketing campaigns.'],
            ['name' => 'Operations Analyst', 'description' => 'Analyzes business operations and suggests improvements.'],
            ['name' => 'Quality Assurance', 'description' => 'Ensures product and service quality standards.'],
            ['name' => 'Recruitment Specialist', 'description' => 'Manages hiring pipeline and interviews.'],
            ['name' => 'Sales Executive', 'description' => 'Drives revenue through client acquisition.'],
            ['name' => 'Training Coordinator', 'description' => 'Organizes employee training and development.'],
            ['name' => 'Project Manager', 'description' => 'Leads cross-functional project delivery.'],
            ['name' => 'Data Analyst', 'description' => 'Performs data analysis and generates insights.'],
            ['name' => 'Legal Advisor', 'description' => 'Handles legal compliance and contracts.'],
            ['name' => 'Facilities Manager', 'description' => 'Manages office facilities and maintenance.'],
            ['name' => 'Security Officer', 'description' => 'Oversees physical and digital security protocols.'],
            ['name' => 'Customer Support Lead', 'description' => 'Manages customer support team and escalations.'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['slug' => Str::slug($role['name'])],
                [
                    'name' => $role['name'],
                    'slug' => Str::slug($role['name']),
                    'description' => $role['description'],
                    'is_system' => false,
                ]
            );
        }

        // --- Dummy Departments (15 extra) ---
        $departments = [
            ['name' => 'Research & Development', 'code' => 'RND', 'description' => 'Innovation and product development.'],
            ['name' => 'Public Relations', 'code' => 'PR', 'description' => 'Media relations and brand image.'],
            ['name' => 'Supply Chain', 'code' => 'SCM', 'description' => 'Logistics and supply chain management.'],
            ['name' => 'Customer Success', 'code' => 'CS', 'description' => 'Client onboarding and retention.'],
            ['name' => 'Business Development', 'code' => 'BD', 'description' => 'New market and partnership opportunities.'],
            ['name' => 'Quality Control', 'code' => 'QC', 'description' => 'Product quality inspection and testing.'],
            ['name' => 'Compliance', 'code' => 'COMP', 'description' => 'Regulatory compliance and audits.'],
            ['name' => 'Training & Dev', 'code' => 'TND', 'description' => 'Employee training programs.'],
            ['name' => 'Procurement', 'code' => 'PROC', 'description' => 'Vendor management and purchasing.'],
            ['name' => 'Internal Audit', 'code' => 'IA', 'description' => 'Internal process auditing.'],
            ['name' => 'Corporate Strategy', 'code' => 'CSTR', 'description' => 'Long-term strategic planning.'],
            ['name' => 'Data Science', 'code' => 'DS', 'description' => 'Data analytics and machine learning.'],
            ['name' => 'Facilities', 'code' => 'FAC', 'description' => 'Office and facility management.'],
            ['name' => 'Health & Safety', 'code' => 'HS', 'description' => 'Workplace safety and wellness.'],
            ['name' => 'Product Design', 'code' => 'PD', 'description' => 'UI/UX and product design.'],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(
                ['code' => $dept['code']],
                array_merge($dept, ['status' => 'active'])
            );
        }

        // --- Dummy Holidays (15 extra for current year) ---
        $year = now()->year;
        $holidays = [
            ['name' => 'New Year Day', 'date' => "$year-01-01", 'type' => 'National', 'description' => 'Start of the new year.'],
            ['name' => 'Makar Sankranti', 'date' => "$year-01-14", 'type' => 'Gazetted', 'description' => 'Harvest festival.'],
            ['name' => 'Republic Day', 'date' => "$year-01-26", 'type' => 'National', 'description' => 'Constitution adoption day.'],
            ['name' => 'Maha Shivaratri', 'date' => "$year-02-26", 'type' => 'Gazetted', 'description' => 'Hindu festival.'],
            ['name' => 'Holi', 'date' => "$year-03-14", 'type' => 'Gazetted', 'description' => 'Festival of colors.'],
            ['name' => 'Good Friday', 'date' => "$year-04-18", 'type' => 'Restricted', 'description' => 'Christian observance.'],
            ['name' => 'Labour Day', 'date' => "$year-05-01", 'type' => 'National', 'description' => 'International Workers Day.'],
            ['name' => 'Eid ul-Fitr', 'date' => "$year-06-07", 'type' => 'Gazetted', 'description' => 'End of Ramadan.'],
            ['name' => 'Rath Yatra', 'date' => "$year-07-07", 'type' => 'Restricted', 'description' => 'Chariot festival.'],
            ['name' => 'Raksha Bandhan', 'date' => "$year-08-09", 'type' => 'Optional', 'description' => 'Bond between siblings.'],
            ['name' => 'Janmashtami', 'date' => "$year-08-16", 'type' => 'Gazetted', 'description' => 'Birth of Lord Krishna.'],
            ['name' => 'Onam', 'date' => "$year-09-05", 'type' => 'Restricted', 'description' => 'Kerala harvest festival.'],
            ['name' => 'Navratri Start', 'date' => "$year-10-02", 'type' => 'Optional', 'description' => 'Nine nights of worship.'],
            ['name' => 'Christmas Eve', 'date' => "$year-12-24", 'type' => 'Optional', 'description' => 'Day before Christmas.'],
            ['name' => 'New Year Eve', 'date' => "$year-12-31", 'type' => 'Optional', 'description' => 'Last day of the year.'],
        ];

        foreach ($holidays as $holiday) {
            Holiday::firstOrCreate(
                ['name' => $holiday['name'], 'date' => $holiday['date']],
                $holiday
            );
        }

        $this->command->info('✅ 15 dummy Roles, 15 Departments, and 15 Holidays seeded for pagination testing.');
    }
}
