<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\Leave;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    /**
     * List leaves (filtered by role).
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasPermission('leaves.view')) {
            abort(403, 'Unauthorized. You do not have permission to view leaves.');
        }

        $query = Leave::with('employee');

        // Check permissions
        $canApproveLeaves = $user->hasPermission('leaves.approve');
        $canApplyLeave = $user->hasPermission('leaves.apply');
        $linkedEmployee = $user->linked_employee;

        // Role-based data scoping
        if (!$user->isSuperAdmin() && !$user->hasPermission('settings.manage') && !$user->isHRManager()) {
            if ($user->isDepartmentManager() && $user->linked_employee?->department_id) {
                $deptId = $user->linked_employee->department_id;
                $query->whereHas('employee', function ($q) use ($deptId) {
                    $q->where('department_id', $deptId);
                });
            } elseif ($linkedEmployee) {
                $query->where('employee_id', $linkedEmployee->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $leaves = $query->latest()->paginate(10)->withQueryString();

        return view('leaves.index', compact('leaves', 'canApproveLeaves', 'canApplyLeave', 'linkedEmployee'));
    }

    /**
     * Submit a new leave request (Staff / Employee).
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasPermission('leaves.apply')) {
            return redirect()->back()->with('error', 'Unauthorized. You do not have permission to apply for leave.');
        }

        $linkedEmployee = $user->linked_employee;

        $validated = $request->validate([
            'employee_id' => $linkedEmployee ? 'nullable' : 'required|exists:employees,id',
            'type' => 'required|string|max:100',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'reason' => 'required|string|max:500',
        ]);

        $empId = $linkedEmployee ? $linkedEmployee->id : $validated['employee_id'];
        $from = Carbon::parse($validated['from_date']);
        $to = Carbon::parse($validated['to_date']);
        $days = $from->diffInDays($to) + 1;

        $leave = Leave::create([
            'employee_id' => $empId,
            'type' => $validated['type'],
            'from_date' => $from,
            'to_date' => $to,
            'days' => $days,
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        $emp = Employee::find($empId);

        ActivityLog::record(
            "Leave application submitted: {$leave->type}",
            "{$leave->days} day(s) by " . ($emp ? $emp->full_name : $user->name),
            'calendar-plus'
        );

        return redirect()->route('leaves.index')->with('success', 'Leave application submitted successfully.');
    }

    /**
     * Approve a pending leave request.
     */
    public function approve(Leave $leave)
    {
        $user = auth()->user();
        if (!$user->hasPermission('leaves.approve')) {
            return redirect()->back()->with('error', 'Unauthorized. You do not have permission to approve leaves.');
        }

        if (!$user->isSuperAdmin() && !$user->hasPermission('settings.manage') && !$user->isHRManager()) {
            if ($user->isDepartmentManager() && $user->linked_employee?->department_id) {
                if ($leave->employee && $leave->employee->department_id !== $user->linked_employee->department_id) {
                    return redirect()->back()->with('error', 'Unauthorized. You can only approve leaves for members of your department.');
                }
            }
        }

        $leave->update(['status' => 'approved']);

        ActivityLog::record(
            "Leave approved for {$leave->employee->full_name}",
            "{$leave->type} ({$leave->days} days) approved by {$user->name}",
            'check-circle'
        );

        return redirect()->back()->with('success', "Leave approved for {$leave->employee->full_name}.");
    }

    /**
     * Reject a pending leave request.
     */
    public function reject(Leave $leave)
    {
        $user = auth()->user();
        if (!$user->hasPermission('leaves.approve')) {
            return redirect()->back()->with('error', 'Unauthorized. You do not have permission to reject leaves.');
        }

        if (!$user->isSuperAdmin() && !$user->hasPermission('settings.manage') && !$user->isHRManager()) {
            if ($user->isDepartmentManager() && $user->linked_employee?->department_id) {
                if ($leave->employee && $leave->employee->department_id !== $user->linked_employee->department_id) {
                    return redirect()->back()->with('error', 'Unauthorized. You can only reject leaves for members of your department.');
                }
            }
        }

        $leave->update(['status' => 'rejected']);

        ActivityLog::record(
            "Leave rejected for {$leave->employee->full_name}",
            "{$leave->type} ({$leave->days} days) declined by {$user->name}",
            'times-circle'
        );

        return redirect()->back()->with('success', "Leave rejected for {$leave->employee->full_name}.");
    }

    public function export(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasPermission('leaves.view')) {
            abort(403, 'Unauthorized. You do not have permission to view leaves.');
        }

        $query = Leave::with(['employee.department']);

        $linkedEmployee = $user->linked_employee;

        // Role-based data scoping
        if (!$user->isSuperAdmin() && !$user->hasPermission('settings.manage') && !$user->isHRManager()) {
            if ($user->isDepartmentManager() && $user->linked_employee?->department_id) {
                $deptId = $user->linked_employee->department_id;
                $query->whereHas('employee', function ($q) use ($deptId) {
                    $q->where('department_id', $deptId);
                });
            } elseif ($linkedEmployee) {
                $query->where('employee_id', $linkedEmployee->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $leaves = $query->latest()->get();

        $headers = [
            'Employee Code',
            'Employee Name',
            'Department',
            'Leave Type',
            'From Date',
            'To Date',
            'Total Days',
            'Reason',
            'Status',
            'Admin Remarks',
            'Applied Date',
        ];

        $rows = [];
        foreach ($leaves as $l) {
            $rows[] = [
                $l->employee ? $l->employee->employee_code : 'N/A',
                $l->employee ? $l->employee->full_name : 'N/A',
                $l->employee && $l->employee->department ? $l->employee->department->name : 'N/A',
                ucfirst($l->type),
                $l->from_date ? $l->from_date->format('Y-m-d') : '',
                $l->to_date ? $l->to_date->format('Y-m-d') : '',
                $l->days,
                $l->reason ?? '',
                ucfirst($l->status),
                $l->admin_remarks ?? '',
                $l->created_at ? $l->created_at->format('Y-m-d H:i') : '',
            ];
        }

        $filename = 'leaves_export_' . now()->format('Y_m_d_His') . '.csv';

        return \App\Services\CsvExportService::streamDownload($filename, $headers, $rows);
    }
}
