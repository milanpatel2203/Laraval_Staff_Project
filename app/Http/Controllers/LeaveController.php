<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\Leave;
<<<<<<< HEAD
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Services\LeaveApprovalService;
=======
use App\Services\CsvExportService;
>>>>>>> 58d9b53 (declare all imports at top with use statements and remove inline namespaces)
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

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

        if ($request->get('queue') === 'assigned') {
            $employee = $this->approvalService->employeeFor($user);
            $query->where('status', 'pending');

            if ($this->approvalService->isSuperAdmin($user)) {
                $query->where('approval_stage', LeaveApprovalService::STAGE_SUPER_ADMIN);
            } else {
                $query->where('current_approver_id', $employee?->id);
            }
        }

        if ($request->get('queue') === 'waiting_super_admin') {
            $query->where('status', 'pending')
                ->where('approval_stage', LeaveApprovalService::STAGE_SUPER_ADMIN);
        }

        if ($request->get('queue') === 'escalated') {
            $query->where('is_escalated', true);
        }

        $leaves = $query->latest()->paginate(10)->withQueryString();
        $pendingAssignedCount = $this->approvalService->pendingAssignedCount($user);

        return view('leaves.index', compact('leaves'));
    }

    /**
     * Approve a pending leave request.
     */
    public function approve(Leave $leave)
    {
        $leave->update(['status' => 'approved']);

        ActivityLog::record(
            "Leave approved for {$leave->employee->full_name}",
            "{$leave->type} ({$leave->days} days) approved by {$user->name}",
            'check-circle'
        );

        return redirect()
            ->back()
            ->with('success', "Leave approved for {$leave->employee->full_name}.");
    }

    /**
     * Reject a pending leave request.
     */
    public function reject(Leave $leave)
    {
        $leave->update(['status' => 'rejected']);

        ActivityLog::record(
            "Leave rejected for {$leave->employee->full_name}",
            "{$leave->type} ({$leave->days} days) declined by {$user->name}",
            'times-circle'
        );

        return redirect()
            ->back()
            ->with('success', "Leave rejected for {$leave->employee->full_name}.");
    }

    public function cancel(Leave $leave)
    {
        if ($leave->employee_id !== auth()->user()->employee?->id) {
            abort(403, 'You can only cancel your own leave requests.');
        }

        if ($leave->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending leave requests can be cancelled.');
        }

        $leave->update(['status' => 'cancelled']);

        return redirect()->back()->with('success', 'Leave request cancelled successfully.');
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

        return CsvExportService::streamDownload($filename, $headers, $rows);
    }
}
