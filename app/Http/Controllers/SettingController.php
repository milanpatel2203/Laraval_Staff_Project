<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user || !$user->hasPermission('settings.manage')) {
            abort(403, 'Unauthorized. You do not have permission to manage settings.');
        }

        $settings = Setting::all()->pluck('value', 'key')->toArray();

        // Defaults if not yet customized
        $defaults = [
            'company_name' => 'UEST Technologies Pvt Ltd',
            'company_email' => 'admin@uesthrms.com',
            'company_phone' => '+91 79 1234 5678',
            'company_address' => 'Technology Park, Ahmedabad, Gujarat, India',
            'company_website' => 'https://uesthrms.com',
            'company_tax_id' => '24AAACU1234F1Z5',
            'work_start_time' => '09:00',
            'work_end_time' => '18:00',
            'standard_working_days' => '5',
            'attendance_grace_minutes' => '15',
            'half_day_hours' => '4.5',
            'currency_symbol' => '₹',
            'payroll_cycle_date' => '1',
            'annual_leave_quota' => '18',
            'sick_leave_quota' => '12',
            'casual_leave_quota' => '8',
            'probation_period_months' => '3',
        ];

        $data = array_merge($defaults, $settings);

        return view('settings.index', compact('data'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->hasPermission('settings.manage')) {
            abort(403, 'Unauthorized. You do not have permission to manage settings.');
        }
        $fields = [
            'company_name' => 'company',
            'company_email' => 'company',
            'company_phone' => 'company',
            'company_address' => 'company',
            'company_website' => 'company',
            'company_tax_id' => 'company',
            'work_start_time' => 'attendance',
            'work_end_time' => 'attendance',
            'standard_working_days' => 'attendance',
            'attendance_grace_minutes' => 'attendance',
            'half_day_hours' => 'attendance',
            'currency_symbol' => 'payroll',
            'payroll_cycle_date' => 'payroll',
            'annual_leave_quota' => 'leave',
            'sick_leave_quota' => 'leave',
            'casual_leave_quota' => 'leave',
            'probation_period_months' => 'general',
        ];

        foreach ($fields as $key => $group) {
            if ($request->has($key)) {
                Setting::set($key, $request->input($key), $group);
            }
        }

        ActivityLog::record(
            'System settings updated',
            'Company profile and policy parameters updated by administrator',
            'cog'
        );

        return redirect()->route('settings.index')->with('success', 'HRMS system configuration updated successfully.');
    }
}
