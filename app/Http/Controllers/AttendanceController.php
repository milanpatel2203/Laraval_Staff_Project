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
        $user = auth()->user();
        if (!$user->hasPermission('attendance.view')) {
            abort(403, 'Unauthorized. You do not have permission to view attendance.');
        }

        $date = $request->get('date', now()->toDateString());
        $canMarkAttendance = $user->hasPermission('attendance.mark');

        // Role-based data scoping
        if (!$user->isSuperAdmin() && !$user->hasPermission('settings.manage') && !$user->isHRManager()) {
            if ($user->isDepartmentManager() && $user->linked_employee?->department_id) {
                $employees = Employee::where('status', 'active')
                    ->where('department_id', $user->linked_employee->department_id)
                    ->orderBy('first_name')->get();
            } elseif ($user->linked_employee) {
                $employees = collect([$user->linked_employee]);
            } else {
                $employees = collect();
            }
        } else {
            $employees = Employee::where('status', 'active')->orderBy('first_name')->get();
        }

        $attendances = Attendance::where('date', $date)->get()->keyBy('employee_id');

        return view('attendance.index', compact('employees', 'attendances', 'date', 'canMarkAttendance'));
    }

    public function mark(Request $request)
    {
        $user = auth()->user();
        $canMarkAttendance = $user->hasPermission('attendance.mark');

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'status' => 'required|in:present,absent,half_day,on_leave',
            'date' => 'required|date',
        ]);

        if (!$user->isSuperAdmin() && !$user->hasPermission('settings.manage') && !$user->isHRManager()) {
            if (!$canMarkAttendance) {
                $linkedEmployee = $user->linked_employee;
                if (!$linkedEmployee || (int)$validated['employee_id'] !== (int)$linkedEmployee->id) {
                    return redirect()->back()->with('error', 'Unauthorized. You can only mark attendance for yourself.');
                }
            } elseif ($user->isDepartmentManager() && $user->linked_employee?->department_id) {
                $targetEmp = Employee::find($validated['employee_id']);
                if (!$targetEmp || $targetEmp->department_id !== $user->linked_employee->department_id) {
                    return redirect()->back()->with('error', 'Unauthorized. You can only mark attendance for members of your department.');
                }
            }
        }

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

    public function export(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasPermission('attendance.view')) {
            abort(403, 'Unauthorized. You do not have permission to view attendance.');
        }

        $date = $request->get('date', now()->toDateString());

        // Role-based data scoping for employees
        if (!$user->isSuperAdmin() && !$user->hasPermission('settings.manage') && !$user->isHRManager()) {
            if ($user->isDepartmentManager() && $user->linked_employee?->department_id) {
                $employees = Employee::with('department')->where('status', 'active')
                    ->where('department_id', $user->linked_employee->department_id)
                    ->orderBy('first_name')->get();
            } elseif ($user->linked_employee) {
                $employees = Employee::with('department')->where('id', $user->linked_employee->id)->get();
            } else {
                $employees = collect();
            }
        } else {
            $employees = Employee::with('department')->where('status', 'active')->orderBy('first_name')->get();
        }

        $attendances = Attendance::where('date', $date)
            ->whereIn('employee_id', $employees->pluck('id'))
            ->get()
            ->keyBy('employee_id');

        $headers = [
            'Date',
            'Employee Code',
            'Employee Name',
            'Department',
            'Designation',
            'Status',
            'Clock In',
            'Clock Out',
            'Remarks',
        ];

        $rows = [];
        foreach ($employees as $emp) {
            $att = $attendances->get($emp->id);
            $rows[] = [
                $date,
                $emp->employee_code,
                $emp->full_name,
                $emp->department ? $emp->department->name : 'N/A',
                $emp->designation,
                $att ? ucfirst(str_replace('_', ' ', $att->status)) : 'Not Marked',
                $att && $att->clock_in ? \Carbon\Carbon::parse($att->clock_in)->format('h:i A') : '—',
                $att && $att->clock_out ? \Carbon\Carbon::parse($att->clock_out)->format('h:i A') : '—',
                $att->remarks ?? '',
            ];
        }

        $filename = 'attendance_export_' . $date . '.csv';

        return \App\Services\CsvExportService::streamDownload($filename, $headers, $rows);
    }
}
