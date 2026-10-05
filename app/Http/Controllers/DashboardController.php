<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Holiday;
use App\Services\LeaveApprovalService;

class DashboardController extends Controller
{
    /**
     * Display the dynamic HRMS dashboard.
     */
    public function index()
    {
        $today = now()->toDateString();

        // 1. Core Counts
        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('status', 'active')->count();
        $totalDepartments = Department::count();

        // 2. Attendance Metrics for Today
        $todayAttendances = Attendance::where('date', $today)->get();
        $presentToday = $todayAttendances->whereIn('status', ['present', 'half_day'])->count();
        $absentToday = $todayAttendances->where('status', 'absent')->count();
        $onLeaveToday = $todayAttendances->where('status', 'on_leave')->count();

        $trackedTotal = max(1, $totalEmployees);
        $presentPercentage = round(($presentToday / $trackedTotal) * 100);
        $absentPercentage = round(($absentToday / $trackedTotal) * 100);
        $leavePercentage = max(0, 100 - $presentPercentage - $absentPercentage);

        // 3. Department-wise Attendance breakdown
        $departments = Department::with(['employees.attendances' => function ($q) use ($today) {
            $q->where('date', $today);
        }])->get();

        $departmentAttendance = $departments->map(function ($dept) {
            $deptEmployees = $dept->employees;
            $present = 0;
            $absent = 0;
            $leave = 0;

            foreach ($deptEmployees as $emp) {
                $att = $emp->attendances->first();
                if ($att) {
                    if ($att->status === 'present' || $att->status === 'half_day') {
                        $present++;
                    } elseif ($att->status === 'absent') {
                        $absent++;
                    } elseif ($att->status === 'on_leave') {
                        $leave++;
                    }
                }
            }

            return [
                'name' => $dept->name,
                'total' => $deptEmployees->count(),
                'present' => $present,
                'absent' => $absent,
                'leave' => $leave,
            ];
        });

        // 4. Pending Leaves assigned to the current approver
        $approvalService = app(LeaveApprovalService::class);
        $user = request()->user();
        $pendingLeaves = $approvalService
            ->pendingAssignedQuery($user)
            ->with('employee')
            ->latest()
            ->take(5)
            ->get();
        $pendingLeavesCount = $approvalService->pendingAssignedCount($user);

        // 5. New Hires in last 30 days
        $newHires = Employee::where('joining_date', '>=', now()->subDays(30))->count();

        // 6. Monthly Payroll Sum
        $totalPayrollSum = (float) Employee::where('status', 'active')->sum('salary');
        if ($totalPayrollSum >= 100000) {
            $formattedPayroll = round($totalPayrollSum / 100000, 2).'L';
        } else {
            $formattedPayroll = number_format($totalPayrollSum, 0);
        }

        // 7. Upcoming Holidays
        $upcomingHolidays = Holiday::where('date', '>=', $today)
            ->orderBy('date')
            ->take(4)
            ->get();

        // 8. Recent Activities
        $recentActivities = ActivityLog::latest()->take(6)->get();

        return view('dashboard', compact(
            'totalEmployees',
            'activeEmployees',
            'totalDepartments',
            'presentToday',
            'absentToday',
            'onLeaveToday',
            'presentPercentage',
            'absentPercentage',
            'leavePercentage',
            'departmentAttendance',
            'pendingLeaves',
            'pendingLeavesCount',
            'newHires',
            'formattedPayroll',
            'upcomingHolidays',
            'recentActivities'
        ));
    }
}
