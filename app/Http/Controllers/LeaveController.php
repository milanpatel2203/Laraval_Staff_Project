<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Services\CsvExportService;
use App\Services\LeaveApprovalService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LeaveController extends Controller
{
    protected LeaveApprovalService $approvalService;

    public function __construct(LeaveApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

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

        $hasApprovalWorkflow = Schema::hasTable('leaves')
            && Schema::hasColumn('leaves', 'approval_stage')
            && Schema::hasColumn('leaves', 'current_approver_id');

        if ($hasApprovalWorkflow) {
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

            if ($request->get('queue') === 'escalated' && Schema::hasColumn('leaves', 'is_escalated')) {
                $query->where('is_escalated', true);
            }

            $pendingAssignedCount = $this->approvalService->pendingAssignedCount($user);
        } else {
            $pendingAssignedCount = Leave::where('status', 'pending')->count();
        }

        $leaves = $query->latest()->paginate(10)->withQueryString();

        return view('leaves.index', compact('leaves', 'canApproveLeaves', 'canApplyLeave', 'linkedEmployee', 'pendingAssignedCount'));
    }

    /**
     * Store a newly created leave request.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasPermission('leaves.apply')) {
            return redirect()->back()->with('error', 'Unauthorized. You do not have permission to apply for leaves.');
        }

        $request->validate([
            'type' => 'required|string|max:100',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'reason' => 'required|string|max:1000',
        ]);

        $employee = $user->linked_employee;

        if (!$employee) {
            return redirect()
                ->back()
                ->with('error', 'Employee record not found for your account.');
        }

        $from = Carbon::parse($request->from_date);
        $to = Carbon::parse($request->to_date);
        $days = $from->diffInDays($to) + 1;

        $leave = Leave::create([
            'employee_id' => $employee->id,
            'type' => $request->type,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'days' => $days,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        ActivityLog::record(
            "Leave requested by {$employee->full_name}",
            "{$leave->type} ({$days} days) requested from {$from->format('d M')} to {$to->format('d M')}",
            'calendar-plus'
        );

        return redirect()
            ->route('leaves.index')
            ->with('success', 'Leave request submitted successfully.');
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
        $user = auth()->user();
        if (!$user->hasPermission('leaves.approve')) {
            return redirect()->back()->with('error', 'Unauthorized. You do not have permission to reject leaves.');
        }

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
        $user = auth()->user();
        $employeeId = $user->linked_employee?->id ?? $user->employee?->id;

        if ($leave->employee_id !== $employeeId) {
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
