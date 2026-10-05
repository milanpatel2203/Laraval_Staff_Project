<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $currentMonth = $request->get('month', now()->format('Y-m'));
        $status = $request->get('status');

        $query = Payroll::with('employee.department')->where('month', $currentMonth);
        if ($status) {
            $query->where('status', $status);
        }

        $payrolls = $query->paginate(15)->withQueryString();

        // Summary metrics for the month
        $allMonthPayrolls = Payroll::where('month', $currentMonth)->get();
        $totalGross = $allMonthPayrolls->sum('basic_salary') + $allMonthPayrolls->sum('allowances');
        $totalNet = $allMonthPayrolls->sum('net_salary');
        $totalPaid = $allMonthPayrolls->where('status', 'paid')->sum('net_salary');
        $totalPending = $allMonthPayrolls->where('status', 'pending')->sum('net_salary');
        $paidCount = $allMonthPayrolls->where('status', 'paid')->count();
        $totalCount = $allMonthPayrolls->count();

        return view('payrolls.index', compact(
            'payrolls',
            'currentMonth',
            'totalGross',
            'totalNet',
            'totalPaid',
            'totalPending',
            'paidCount',
            'totalCount'
        ));
    }

    public function generate(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $employees = Employee::where('status', 'active')->get();
        $createdCount = 0;

        foreach ($employees as $emp) {
            $basic = (float) $emp->salary;
            // Standard allowances: 15% HRA, 5% Special
            $allowances = round($basic * 0.20, 2);
            // Standard deductions: 10% PF & Tax
            $deductions = round($basic * 0.10, 2);
            $net = $basic + $allowances - $deductions;

            $payroll = Payroll::firstOrCreate(
                ['employee_id' => $emp->id, 'month' => $month],
                [
                    'basic_salary' => $basic,
                    'allowances' => $allowances,
                    'deductions' => $deductions,
                    'net_salary' => $net,
                    'status' => 'pending',
                ]
            );

            if ($payroll->wasRecentlyCreated) {
                $createdCount++;
            }
        }

        ActivityLog::record(
            "Payroll generated for {$month}",
            "Processed {$createdCount} employee payroll slips for period {$month}",
            'money-bill-wave'
        );

        return redirect()->route('payroll.index', ['month' => $month])
            ->with('success', "Payroll generated successfully for {$month} ({$createdCount} new records created).");
    }

    public function markPaid(Request $request, Payroll $payroll)
    {
        $payroll->update([
            'status' => 'paid',
            'payment_date' => now()->toDateString(),
            'payment_method' => $request->get('payment_method', 'Bank Transfer'),
            'remarks' => $request->get('remarks', 'Disbursed via Direct Deposit'),
        ]);

        ActivityLog::record(
            "Salary disbursed to {$payroll->employee->full_name}",
            "₹" . number_format($payroll->net_salary, 2) . " paid for period {$payroll->month}",
            'check-double'
        );

        return redirect()->back()->with('success', "Salary marked as paid for {$payroll->employee->full_name}.");
    }

    public function payslip(Payroll $payroll)
    {
        $payroll->load('employee.department');
        return view('payrolls.payslip', compact('payroll'));
    }
}
