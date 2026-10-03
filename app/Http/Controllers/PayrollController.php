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
        $user = auth()->user();

        $canViewAllPayroll = $user->hasPermission('payroll.view');
        $canGeneratePayroll = $user->hasPermission('payroll.generate');
        $canDisburse = $user->hasPermission('payroll.disburse');

        $query = Payroll::with('employee.department')->where('month', $currentMonth);
        if ($status) {
            $query->where('status', $status);
        }

        if (!$canViewAllPayroll) {
            $linkedEmployee = $user->linked_employee;
            if ($linkedEmployee) {
                $query->where('employee_id', $linkedEmployee->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $payrolls = $query->paginate(15)->withQueryString();

        // Summary metrics for the month
        $summaryQuery = Payroll::where('month', $currentMonth);
        if (!$canViewAllPayroll) {
            $linkedEmployee = $user->linked_employee;
            if ($linkedEmployee) {
                $summaryQuery->where('employee_id', $linkedEmployee->id);
            } else {
                $summaryQuery->whereRaw('1 = 0');
            }
        }
        $allMonthPayrolls = $summaryQuery->get();
        $totalGross = $allMonthPayrolls->sum('basic_salary') + $allMonthPayrolls->sum('allowances');
        $totalNet = $allMonthPayrolls->sum('net_salary');
        $totalPaid = $allMonthPayrolls->sum(function($p) {
            if ($p->paid_amount !== null && $p->paid_amount > 0) {
                return (float) $p->paid_amount;
            }
            return $p->status === 'paid' ? (float) $p->net_salary : 0;
        });
        $totalPending = max(0, $totalNet - $totalPaid);
        $paidCount = $allMonthPayrolls->filter(fn($p) => $p->is_fully_paid)->count();
        $partialCount = $allMonthPayrolls->filter(fn($p) => $p->is_partially_paid)->count();
        $totalCount = $allMonthPayrolls->count();

        return view('payrolls.index', compact(
            'payrolls',
            'currentMonth',
            'totalGross',
            'totalNet',
            'totalPaid',
            'totalPending',
            'paidCount',
            'partialCount',
            'totalCount',
            'canViewAllPayroll',
            'canGeneratePayroll',
            'canDisburse'
        ));
    }

    public function generate(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasPermission('payroll.generate')) {
            return redirect()->back()->with('error', 'Unauthorized. You do not have permission to generate payroll.');
        }

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
                     'paid_amount' => 0,
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
        $user = auth()->user();
        if (!$user->hasPermission('payroll.disburse')) {
            return redirect()->back()->with('error', 'Unauthorized. You do not have permission to disburse salary.');
        }

        $validated = $request->validate([
            'payment_type' => 'nullable|in:full,custom',
            'paid_amount' => 'nullable|numeric|min:0.01',
            'payment_date' => 'nullable|date',
            'payment_method' => 'nullable|string|max:50',
            'remarks' => 'nullable|string|max:500',
        ]);

        $netSalary = (float) $payroll->net_salary;
        $paidAmount = isset($validated['paid_amount']) && $validated['paid_amount'] !== null && $validated['paid_amount'] !== ''
            ? round((float) $validated['paid_amount'], 2)
            : $netSalary;

        // Determine status: full payment if >= net salary, otherwise partial
        if ($paidAmount >= $netSalary) {
            $status = 'paid';
        } else {
            $status = 'partial';
        }

        $paymentDate = !empty($validated['payment_date']) ? $validated['payment_date'] : now()->toDateString();
        $paymentMethod = !empty($validated['payment_method']) ? $validated['payment_method'] : 'Bank Transfer';
        $remarks = !empty($validated['remarks']) ? $validated['remarks'] : ($status === 'paid' ? 'Full payment disbursed' : 'Manual partial payment disbursed');

        $payroll->update([
            'paid_amount' => $paidAmount,
            'status' => $status,
            'payment_date' => $paymentDate,
            'payment_method' => $paymentMethod,
            'remarks' => $remarks,
        ]);

        $statusText = $status === 'paid' ? 'in full' : "partially (₹" . number_format($paidAmount, 2) . " of ₹" . number_format($netSalary, 2) . ")";
        ActivityLog::record(
            "Salary payment disbursed to {$payroll->employee->full_name}",
            "₹" . number_format($paidAmount, 2) . " paid {$statusText} for period {$payroll->month}",
            'check-double'
        );

        return redirect()->back()->with('success', "Payment of ₹" . number_format($paidAmount, 2) . " recorded successfully for {$payroll->employee->full_name} ({$statusText}).");
    }

    public function update(Request $request, Payroll $payroll)
    {
        $user = auth()->user();
        if (!$user->hasPermission('payroll.generate') && !$user->hasPermission('payroll.disburse')) {
            return redirect()->back()->with('error', 'Unauthorized. You do not have permission to adjust payroll.');
        }

        $validated = $request->validate([
            'basic_salary' => 'required|numeric|min:0',
            'allowances' => 'required|numeric|min:0',
            'deductions' => 'required|numeric|min:0',
            'net_salary' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:pending,partial,paid',
            'payment_date' => 'nullable|date',
            'payment_method' => 'nullable|string|max:50',
            'remarks' => 'nullable|string|max:500',
        ]);

        $basic = round((float) $validated['basic_salary'], 2);
        $allowances = round((float) $validated['allowances'], 2);
        $deductions = round((float) $validated['deductions'], 2);

        // Auto calculate net salary unless explicitly provided
        $netSalary = array_key_exists('net_salary', $validated) && $validated['net_salary'] !== null && $validated['net_salary'] !== ''
            ? round((float) $validated['net_salary'], 2)
            : max(0, round($basic + $allowances - $deductions, 2));

        $paidAmount = isset($validated['paid_amount']) && $validated['paid_amount'] !== null && $validated['paid_amount'] !== ''
            ? round((float) $validated['paid_amount'], 2)
            : (float) ($payroll->paid_amount ?? 0);

        // Auto determine status if not explicitly passed
        if (!empty($validated['status'])) {
            $status = $validated['status'];
        } else {
            if ($paidAmount >= $netSalary && $netSalary > 0) {
                $status = 'paid';
            } elseif ($paidAmount > 0) {
                $status = 'partial';
            } else {
                $status = 'pending';
            }
        }

        $updateData = [
            'basic_salary' => $basic,
            'allowances' => $allowances,
            'deductions' => $deductions,
            'net_salary' => $netSalary,
            'paid_amount' => $paidAmount,
            'status' => $status,
            'remarks' => $validated['remarks'] ?? $payroll->remarks,
        ];

        if (!empty($validated['payment_date'])) {
            $updateData['payment_date'] = $validated['payment_date'];
        }
        if (!empty($validated['payment_method'])) {
            $updateData['payment_method'] = $validated['payment_method'];
        }

        $payroll->update($updateData);

        ActivityLog::record(
            "Payroll manually adjusted for {$payroll->employee->full_name}",
            "Net: ₹" . number_format($netSalary, 2) . ", Paid: ₹" . number_format($paidAmount, 2) . " ({$status})",
            'edit'
        );

        return redirect()->back()->with('success', "Payroll record for {$payroll->employee->full_name} updated successfully.");
    }

    public function payslip(Payroll $payroll)
    {
        $user = auth()->user();
        if (!$user->hasPermission('payroll.view')) {
            $linkedEmployee = $user->linked_employee;
            if (!$linkedEmployee || (int)$payroll->employee_id !== (int)$linkedEmployee->id) {
                abort(403, 'Unauthorized. You may only view your own payslip.');
            }
        }

        $payroll->load('employee.department');
        return view('payrolls.payslip', compact('payroll'));
    }

    public function export(Request $request)
    {
        $currentMonth = $request->get('month', now()->format('Y-m'));
        $status = $request->get('status');
        $user = auth()->user();

        $canViewAllPayroll = $user->hasPermission('payroll.view');

        $query = Payroll::with('employee.department')->where('month', $currentMonth);
        if ($status) {
            $query->where('status', $status);
        }

        if (!$canViewAllPayroll) {
            $linkedEmployee = $user->linked_employee;
            if ($linkedEmployee) {
                $query->where('employee_id', $linkedEmployee->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $payrolls = $query->get();

        $headers = [
            'Payroll Month',
            'Employee Code',
            'Employee Name',
            'Department',
            'Designation',
            'Basic Salary',
            'Allowances',
            'Deductions',
            'Net Salary',
            'Paid Amount',
            'Remaining Balance',
            'Status',
            'Payment Date',
            'Payment Method',
            'Remarks',
        ];

        $rows = [];
        foreach ($payrolls as $p) {
            $paid = (float) ($p->paid_amount !== null && $p->paid_amount > 0 ? $p->paid_amount : ($p->status === 'paid' ? $p->net_salary : 0));
            $rem = max(0, (float) $p->net_salary - $paid);

            $rows[] = [
                $p->month,
                $p->employee ? $p->employee->employee_code : 'N/A',
                $p->employee ? $p->employee->full_name : 'N/A',
                $p->employee && $p->employee->department ? $p->employee->department->name : 'N/A',
                $p->employee ? $p->employee->designation : 'N/A',
                $p->basic_salary,
                $p->allowances,
                $p->deductions,
                $p->net_salary,
                $paid,
                $rem,
                ucfirst($p->status),
                $p->payment_date ? $p->payment_date->format('Y-m-d') : '',
                $p->payment_method ?? '',
                $p->remarks ?? '',
            ];
        }

        $filename = 'payroll_export_' . $currentMonth . '.csv';

        return \App\Services\CsvExportService::streamDownload($filename, $headers, $rows);
    }
}
