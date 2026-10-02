<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', now()->toDateString());
        $employees = Employee::where('status', 'active')->orderBy('first_name')->get();
        $attendances = Attendance::where('date', $date)->get()->keyBy('employee_id');

        return view('attendance.index', compact('employees', 'attendances', 'date'));
    }

    public function mark(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'status' => 'required|in:present,absent,half_day,on_leave',
            'date' => 'required|date',
        ]);

        $clockIn = ($validated['status'] === 'present' || $validated['status'] === 'half_day') ? now()->format('H:i:s') : null;

        Attendance::updateOrCreate(
            ['employee_id' => $validated['employee_id'], 'date' => $validated['date']],
            ['status' => $validated['status'], 'clock_in' => $clockIn]
        );

        $emp = Employee::find($validated['employee_id']);
        ActivityLog::record(
            "Attendance marked for {$emp->full_name}",
            "Status updated to " . ucfirst(str_replace('_', ' ', $validated['status'])),
            'clock'
        );

        return redirect()->back()->with('success', "Attendance updated for {$emp->full_name}.");
    }
}
