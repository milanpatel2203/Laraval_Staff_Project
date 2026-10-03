<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\Leave;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the dynamic HRMS dashboard.
     */
    public function index()
    {
        $today = now()->toDateString();
        $user = auth()->user();
        $isGlobal = $user->isSuperAdmin() || $user->isHRManager() || $user->hasPermission('settings.manage');
        $isDeptMgr = !$isGlobal && $user->isDepartmentManager();
        $isStaff = !$isGlobal && !$isDeptMgr;
        $deptId = $user->linked_employee?->department_id;
        $empId = $user->linked_employee?->id;

        // 1. Core Counts
        $empQuery = Employee::query();
        if ($isDeptMgr && $deptId) {
            $empQuery->where('department_id', $deptId);
        } elseif ($isStaff && $empId) {
            $empQuery->where('id', $empId);
        }
        $totalEmployees = $empQuery->count();
        $activeEmployees = (clone $empQuery)->where('status', 'active')->count();

        $deptQuery = Department::query();
        if ($isDeptMgr && $deptId) {
            $deptQuery->where('id', $deptId);
        }
        $totalDepartments = $deptQuery->count();

        // 2. Attendance Metrics for Today
        $attQuery = Attendance::where('date', $today);
        if ($isDeptMgr && $deptId) {
            $attQuery->whereHas('employee', fn($q) => $q->where('department_id', $deptId));
        } elseif ($isStaff && $empId) {
            $attQuery->where('employee_id', $empId);
        }
        $todayAttendances = $attQuery->get();
        $presentToday = $todayAttendances->whereIn('status', ['present', 'half_day'])->count();
        $absentToday = $todayAttendances->where('status', 'absent')->count();
        $onLeaveToday = $todayAttendances->where('status', 'on_leave')->count();

        $trackedTotal = max(1, $totalEmployees);
        $presentPercentage = round(($presentToday / $trackedTotal) * 100);
        $absentPercentage = round(($absentToday / $trackedTotal) * 100);
        $leavePercentage = max(0, 100 - $presentPercentage - $absentPercentage);

        // 3. Department-wise Attendance breakdown
        $departments = $deptQuery->with(['employees.attendances' => function ($q) use ($today) {
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

        // 4. Pending Leaves
        $leaveQuery = Leave::with('employee')->where('status', 'pending');
        if ($isDeptMgr && $deptId) {
            $leaveQuery->whereHas('employee', fn($q) => $q->where('department_id', $deptId));
        } elseif ($isStaff && $empId) {
            $leaveQuery->where('employee_id', $empId);
        }
        $pendingLeaves = (clone $leaveQuery)->latest()->take(5)->get();
        $pendingLeavesCount = $leaveQuery->count();

        // 5. New Hires in last 30 days
        $newHires = (clone $empQuery)->where('joining_date', '>=', now()->subDays(30))->count();

        // 6. Monthly Payroll Sum
        $payrollSumQuery = Employee::where('status', 'active');
        if ($isDeptMgr && $deptId) {
            $payrollSumQuery->where('department_id', $deptId);
        } elseif ($isStaff && $empId) {
            $payrollSumQuery->where('id', $empId);
        }
        $totalPayrollSum = (float) $payrollSumQuery->sum('salary');
        if ($totalPayrollSum >= 100000) {
            $formattedPayroll = round($totalPayrollSum / 100000, 2) . 'L';
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
