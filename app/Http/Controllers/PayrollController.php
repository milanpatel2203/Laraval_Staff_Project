<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Setting;
use App\Services\CsvExportService;
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
        $totalPaid = $allMonthPayrolls->sum(function ($p) {
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

    /**
     * Get all payroll component configuration from Settings.
     */
    public static function getPayrollConfig(): array
    {
        $pf = (float) Setting::get('payroll_pf_percent', 12.0);
        $pt = (float) Setting::get('payroll_pt_percent', 2.5);
        $esi = (float) Setting::get('payroll_esi_percent', 0.75);
        $tds = (float) Setting::get('payroll_tds_percent', 0.0);
        $otherDed = (float) Setting::get('payroll_other_deduction_percent', 0.0);

        $hra = (float) Setting::get('payroll_hra_percent', 10.0);
        $da = (float) Setting::get('payroll_da_percent', 5.0);
        $conveyance = (float) Setting::get('payroll_conveyance_percent', 3.0);
        $medical = (float) Setting::get('payroll_medical_percent', 2.0);
        $special = (float) Setting::get('payroll_special_allowance_percent', 0.0);

        $totalDed = round($pf + $pt + $esi + $tds + $otherDed, 2);
        $totalAll = round($hra + $da + $conveyance + $medical + $special, 2);

        return [
            // Exact Setting keys & form field names
            'payroll_pf_percent'                => $pf,
            'payroll_pt_percent'                => $pt,
            'payroll_esi_percent'               => $esi,
            'payroll_tds_percent'               => $tds,
            'payroll_other_deduction_percent'   => $otherDed,
            'payroll_hra_percent'               => $hra,
            'payroll_da_percent'                => $da,
            'payroll_conveyance_percent'        => $conveyance,
            'payroll_medical_percent'           => $medical,
            'payroll_special_allowance_percent' => $special,

            // Short aliases
            'pf'                                => $pf,
            'pt'                                => $pt,
            'esi'                               => $esi,
            'tds'                               => $tds,
            'other_deduction'                   => $otherDed,
            'hra'                               => $hra,
            'da'                                => $da,
            'conveyance'                        => $conveyance,
            'medical'                           => $medical,
            'special'                           => $special,

            // Computed totals
            'total_deductions'                  => $totalDed,
            'total_allowances'                  => $totalAll,
        ];
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
        $updatedCount = 0;

        // Read individual component percentages from configuration
        $cfg = self::getPayrollConfig();

        $totalAllowancePercent = $cfg['payroll_hra_percent']
            + $cfg['payroll_da_percent']
            + $cfg['payroll_conveyance_percent']
            + $cfg['payroll_medical_percent']
            + $cfg['payroll_special_allowance_percent'];

        $totalDeductionPercent = $cfg['payroll_pf_percent']
            + $cfg['payroll_pt_percent']
            + $cfg['payroll_esi_percent']
            + $cfg['payroll_tds_percent']
            + $cfg['payroll_other_deduction_percent'];

        foreach ($employees as $emp) {
            $basic = (float) $emp->salary;
            $allowances = round($basic * ($totalAllowancePercent / 100), 2);
            $deductions = round($basic * ($totalDeductionPercent / 100), 2);
            $net = max(0, round($basic + $allowances - $deductions, 2));

            $payroll = Payroll::where('employee_id', $emp->id)->where('month', $month)->first();
            if (!$payroll) {
                Payroll::create([
                    'employee_id' => $emp->id,
                    'month'       => $month,
                    'basic_salary' => $basic,
                    'allowances'   => $allowances,
                    'deductions'   => $deductions,
                    'net_salary'   => $net,
                    'paid_amount'  => 0,
                    'status'       => 'pending',
                ]);
                $createdCount++;
            } elseif ($payroll->status === 'pending' && ($payroll->paid_amount === null || (float)$payroll->paid_amount == 0)) {
                // Update pending unpaid records with the latest rates
                $payroll->update([
                    'basic_salary' => $basic,
                    'allowances'   => $allowances,
                    'deductions'   => $deductions,
                    'net_salary'   => $net,
                ]);
                $updatedCount++;
            }
        }

        ActivityLog::record(
            "Payroll generated for {$month}",
            "Processed payroll slips (Created: {$createdCount}, Updated: {$updatedCount}, Deductions: {$totalDeductionPercent}%, Allowances: {$totalAllowancePercent}%)",
            'money-bill-wave'
        );

        $summaryMsg = "Payroll processed for {$month}: {$createdCount} newly created" . ($updatedCount > 0 ? ", {$updatedCount} pending updated" : "") . ". Deductions: {$totalDeductionPercent}% · Allowances: {$totalAllowancePercent}%";

        return redirect()->route('payroll.index', ['month' => $month])
            ->with('success', $summaryMsg);
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
            if (!$linkedEmployee || (int) $payroll->employee_id !== (int) $linkedEmployee->id) {
                abort(403, 'Unauthorized. You may only view your own payslip.');
            }
        }

        $payroll->load('employee.department');
        $payrollConfig = self::getPayrollConfig();
        $companySettings = [
            'name' => Setting::get('company_name', 'UEST TECHNOLOGIES'),
            'address' => Setting::get('company_address', 'Technology Park, Ahmedabad, Gujarat'),
            'email' => Setting::get('company_email', 'hr@uesthrms.com'),
            'phone' => Setting::get('company_phone', '+91 79 1234 5678'),
        ];
        return view('payrolls.payslip', compact('payroll', 'payrollConfig', 'companySettings'));
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

        return CsvExportService::streamDownload($filename, $headers, $rows);
    }

    /**
     * Show the Payroll Configuration form.
     */
    public function configuration()
    {
        $user = auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Only Super Admin can access payroll configuration.');
        }

        $config = self::getPayrollConfig();

        return view('payrolls.configuration', compact('config'));
    }

    /**
     * Save Payroll Configuration.
     */
    public function updateConfiguration(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Only Super Admin can update payroll configuration.');
        }

        $fields = [
            'payroll_pf_percent',
            'payroll_pt_percent',
            'payroll_esi_percent',
            'payroll_tds_percent',
            'payroll_other_deduction_percent',
            'payroll_hra_percent',
            'payroll_da_percent',
            'payroll_conveyance_percent',
            'payroll_medical_percent',
            'payroll_special_allowance_percent',
        ];

        foreach ($fields as $key) {
            if ($request->has($key)) {
                $value = max(0, min(100, (float) $request->input($key)));
                Setting::set($key, (string) $value, 'payroll');
            }
        }

        // Get latest component rates and totals
        $cfg = self::getPayrollConfig();
        $totalDed = $cfg['total_deductions'];
        $totalAll = $cfg['total_allowances'];

        // Sync legacy aggregate settings for backward compatibility
        Setting::set('payroll_deduction_percent', (string) $totalDed, 'payroll');
        Setting::set('payroll_allowance_percent', (string) $totalAll, 'payroll');

        // Automatically recalculate and update all pending (unpaid) payroll records for the current month
        $currentMonth = now()->format('Y-m');
        $pendingPayrolls = Payroll::with('employee')
            ->where('month', $currentMonth)
            ->where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('paid_amount')->orWhere('paid_amount', 0);
            })
            ->get();

        $updatedCount = 0;
        foreach ($pendingPayrolls as $p) {
            $basic = (float) ($p->employee ? $p->employee->salary : $p->basic_salary);
            $allowances = round($basic * ($totalAll / 100), 2);
            $deductions = round($basic * ($totalDed / 100), 2);
            $net = max(0, round($basic + $allowances - $deductions, 2));

            $p->update([
                'basic_salary' => $basic,
                'allowances'   => $allowances,
                'deductions'   => $deductions,
                'net_salary'   => $net,
            ]);
            $updatedCount++;
        }

        ActivityLog::record(
            'Payroll configuration updated',
            "Component rates updated — Total Deductions: {$totalDed}%, Total Allowances: {$totalAll}% (" . ($updatedCount > 0 ? "Updated {$updatedCount} pending payrolls for {$currentMonth}" : "No pending payrolls") . ")",
            'sliders-h'
        );

        $successMsg = 'Payroll configuration saved successfully!';
        if ($updatedCount > 0) {
            $successMsg .= " Automatically updated {$updatedCount} pending payroll record(s) for {$currentMonth} with the new rates.";
        } else {
            $successMsg .= " New rates will apply to newly generated payrolls.";
        }

        return redirect()->route('payroll.configuration')
            ->with('success', $successMsg);
    }
}
