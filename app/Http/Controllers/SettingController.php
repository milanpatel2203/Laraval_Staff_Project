<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();

        // Defaults if not yet customized
        $defaults = [
            'company_name' => 'UEST Technologies Pvt Ltd',
            'company_email' => 'admin@uesthrms.com',
            'company_phone' => '+91 79 1234 5678',
            'company_address' => 'Technology Park, Ahmedabad, Gujarat, India',
            'work_start_time' => '09:00',
            'work_end_time' => '18:00',
            'standard_working_days' => '5',
            'currency_symbol' => '₹',
            'annual_leave_quota' => '18',
            'sick_leave_quota' => '12',
            'probation_period_months' => '3',
        ];

        $data = array_merge($defaults, $settings);

        return view('settings.index', compact('data'));
    }

    public function update(Request $request)
    {
        $fields = [
            'company_name' => 'company',
            'company_email' => 'company',
            'company_phone' => 'company',
            'company_address' => 'company',
            'work_start_time' => 'attendance',
            'work_end_time' => 'attendance',
            'standard_working_days' => 'attendance',
            'currency_symbol' => 'payroll',
            'annual_leave_quota' => 'leave',
            'sick_leave_quota' => 'leave',
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
