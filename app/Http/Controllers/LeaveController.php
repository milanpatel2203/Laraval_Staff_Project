<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Services\LeaveApprovalService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LeaveController extends Controller
{
    public function __construct(private LeaveApprovalService $approvalService) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $query = $this->approvalService
            ->visibleLeavesQuery($user)
            ->with(['employee.team', 'currentApprover', 'approvals']);

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

        return view('leaves.index', compact('leaves', 'pendingAssignedCount'));
    }

    public function show(Request $request, Leave $leave)
    {
        $this->approvalService->assertVisible($request->user(), $leave);
        $leave->load(['employee.team', 'currentApprover', 'approvals.approver', 'approvals.approverEmployee']);

        return view('leaves.show', compact('leave'));
    }

    public function create()
    {
        $leaveTypes = LeaveType::active()->get();

        return view('leaves.create', compact('leaveTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'reason' => 'required|string|max:1000',
        ]);

        $employee = Employee::with('team.teamLeader')->where('email', $request->user()->email)->first();

        if (! $employee) {
            return redirect()
                ->back()
                ->with('error', 'Employee record not found.');
        }

        $from = Carbon::parse($request->from_date);
        $to = Carbon::parse($request->to_date);
        $days = $from->diffInDays($to) + 1;

        $leaveType = LeaveType::find($request->leave_type_id);

        if (! $leaveType->is_paid) {
            $balance = LeaveBalance::firstOrCreate(
                ['employee_id' => $employee->id, 'leave_type_id' => $leaveType->id],
                ['allocated' => 0, 'used' => 0, 'remaining' => 0]
            );
        } else {
            $balance = LeaveBalance::firstOrCreate(
                ['employee_id' => $employee->id, 'leave_type_id' => $leaveType->id],
                ['allocated' => $leaveType->annual_allocation, 'used' => 0, 'remaining' => $leaveType->annual_allocation]
            );

            if ($balance->remaining < $days) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', "Insufficient leave balance. You have {$balance->remaining} days remaining for {$leaveType->name}.");
            }
        }

        $this->approvalService->submit($employee, [
            'type' => $leaveType->name,
            'leave_type_id' => $leaveType->id,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'days' => $days,
            'reason' => $request->reason,
        ]);

        return redirect()
            ->route('leaves.index')
            ->with('success', 'Leave request submitted successfully.');
    }

    public function approve(Request $request, Leave $leave)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:1000',
        ]);

        try {
            $this->approvalService->approve($request->user(), $leave, $request->remarks);
        } catch (HttpException $e) {
            abort($e->getStatusCode(), $e->getMessage());
        }

        if ($leave->leave_type_id && $leave->leaveType->is_paid) {
            $balance = LeaveBalance::firstOrCreate(
                ['employee_id' => $leave->employee_id, 'leave_type_id' => $leave->leave_type_id],
                ['allocated' => $leave->leaveType->annual_allocation, 'used' => 0, 'remaining' => $leave->leaveType->annual_allocation]
            );
            $balance->deduct($leave->days);
        }

        $leave->load('employee');

        ActivityLog::record(
            "Leave approved for {$leave->employee->full_name}",
            "{$leave->type} ({$leave->days} days) from {$leave->from_date->format('d M')} to {$leave->to_date->format('d M')}",
            'check-circle'
        );

        return redirect()
            ->back()
            ->with('success', "Leave approved for {$leave->employee->full_name}.");
    }

    public function reject(Request $request, Leave $leave)
    {
        $request->validate([
            'remarks' => 'required|string|max:1000',
        ], [
            'remarks.required' => 'Please provide remarks when rejecting a leave request.',
        ]);

        try {
            $this->approvalService->reject($request->user(), $leave, $request->remarks);
        } catch (HttpException $e) {
            abort($e->getStatusCode(), $e->getMessage());
        }

        $leave->load('employee');

        ActivityLog::record(
            "Leave rejected for {$leave->employee->full_name}",
            "{$leave->type} ({$leave->days} days) was declined",
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
}
