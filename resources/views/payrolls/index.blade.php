@extends('layouts.app')

@section('title', $canViewAllPayroll ? 'Payroll Management' : 'My Payslips')
@section('page-title', $canViewAllPayroll ? 'Payroll' : 'My Payslips')

@section('content')
<div class="space-y-6">

    {{-- Header & Month Selector --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-[#2D2D2D]">{{ $canViewAllPayroll ? 'Payroll' : 'My Payslips' }} — {{ \Carbon\Carbon::parse($currentMonth . '-01')->format('F Y') }}</h2>
            <p class="text-xs text-gray-500 mt-0.5">
                {{ $canViewAllPayroll ? 'Generate salary slips, set manual payment amounts, and manage disbursements.' : 'View and download your monthly salary slips.' }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('payroll.export', request()->query()) }}" class="border border-gray-300 bg-white hover:bg-gray-50 text-[#2D2D2D] text-xs font-semibold px-3 py-1.5 rounded inline-flex items-center gap-1.5 shadow-sm transition">
                <i class="fas fa-file-excel text-emerald-600"></i> Export Excel
            </a>
            <form action="{{ route('payroll.index') }}" method="GET" class="flex items-center gap-2">
                <input type="month" name="month" value="{{ $currentMonth }}" class="px-3 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                <button type="submit" class="px-3 py-1.5 border border-gray-300 bg-white text-xs font-medium rounded hover:bg-gray-100">Go</button>
            </form>
            @if($canGeneratePayroll)
            <form action="{{ route('payroll.generate') }}" method="POST">
                @csrf
                <input type="hidden" name="month" value="{{ $currentMonth }}">
                <button type="submit" class="px-4 py-2 bg-[#2D2D2D] text-white rounded text-xs font-semibold hover:bg-[#1a1a1a] flex items-center gap-1.5">
                    <i class="fas fa-sync-alt"></i> Generate Payroll
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-200 rounded p-4">
            <span class="text-[11px] text-gray-500 font-medium">Total Net Payable</span>
            <p class="text-xl font-bold text-[#2D2D2D] mt-1">₹{{ number_format($totalNet, 2) }}</p>
            <span class="text-[10px] text-gray-400 mt-1 block">{{ $totalCount }} records processed</span>
        </div>
        <div class="bg-white border border-gray-200 rounded p-4">
            <span class="text-[11px] text-gray-500 font-medium">Total Disbursed (Paid)</span>
            <p class="text-xl font-bold text-emerald-700 mt-1">₹{{ number_format($totalPaid, 2) }}</p>
            <span class="text-[10px] text-gray-500 mt-1 block font-medium">
                {{ $paidCount }} Paid &middot; {{ $partialCount ?? 0 }} Partial
            </span>
        </div>
        <div class="bg-white border border-gray-200 rounded p-4">
            <span class="text-[11px] text-gray-500 font-medium">Pending / Outstanding</span>
            <p class="text-xl font-bold text-amber-700 mt-1">₹{{ number_format($totalPending, 2) }}</p>
            <span class="text-[10px] text-gray-400 mt-1 block">{{ max(0, $totalCount - $paidCount) }} pending settlement</span>
        </div>
        <div class="bg-white border border-gray-200 rounded p-4">
            <span class="text-[11px] text-gray-500 font-medium">Disbursement Progress</span>
            <div class="flex items-center gap-3 mt-2">
                <div class="flex-1 h-3 bg-gray-100 rounded overflow-hidden">
                    @php $pct = $totalNet > 0 ? round(($totalPaid / $totalNet) * 100) : 0; @endphp
                    <div class="h-full bg-[#2D2D2D]" style="width: {{ min(100, $pct) }}%"></div>
                </div>
                <span class="text-xs font-bold text-[#2D2D2D]">{{ $pct }}%</span>
            </div>
            <span class="text-[10px] text-gray-400 mt-1 block">Based on actual amount disbursed</span>
        </div>
    </div>

    {{-- Payroll Table using Common Component --}}
    <x-table 
        :headers="[
            ['label' => 'Code', 'class' => 'w-20'],
            ['label' => 'Employee'],
            ['label' => 'Department'],
            ['label' => 'Basic'],
            ['label' => 'Allowances (+)'],
            ['label' => 'Deductions (-)'],
            ['label' => 'Net Salary'],
            ['label' => 'Disbursed / Due'],
            ['label' => 'Status', 'class' => 'w-24'],
            ['label' => 'Actions', 'align' => 'right']
        ]"
        :pagination="$payrolls"
        :empty="$payrolls->isEmpty()" 
        emptyMessage="No payroll generated for this period. Click 'Generate Payroll' above to create slips.">
        @foreach($payrolls as $pr)
        @php
            $actualPaid = (float)($pr->paid_amount ?: ($pr->status === 'paid' ? $pr->net_salary : 0));
            $remDue = max(0, (float)$pr->net_salary - $actualPaid);
        @endphp
        <tr class="hover:bg-gray-50/50">
            <td class="py-3 px-4 font-bold text-[#2D2D2D]">{{ $pr->employee->employee_code }}</td>
            <td class="py-3 px-4 font-medium text-[#2D2D2D]">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full overflow-hidden bg-[#2D2D2D] text-white flex items-center justify-center text-[10px] font-bold shrink-0 border border-gray-200">
                        @if($pr->employee->avatar_url)
                            <img src="{{ $pr->employee->avatar_url }}" alt="{{ $pr->employee->full_name }}" class="w-full h-full object-cover">
                        @else
                            <span>{{ $pr->employee->initials }}</span>
                        @endif
                    </div>
                    <span>{{ $pr->employee->full_name }}</span>
                </div>
            </td>
            <td class="py-3 px-4 text-gray-600">{{ $pr->employee->department ? $pr->employee->department->name : '—' }}</td>
            <td class="py-3 px-4 text-[#2D2D2D]">₹{{ number_format($pr->basic_salary, 2) }}</td>
            <td class="py-3 px-4 text-emerald-700">+₹{{ number_format($pr->allowances, 2) }}</td>
            <td class="py-3 px-4 text-rose-700">-₹{{ number_format($pr->deductions, 2) }}</td>
            <td class="py-3 px-4 font-bold text-[#2D2D2D]">₹{{ number_format($pr->net_salary, 2) }}</td>
            <td class="py-3 px-4">
                <div class="flex flex-col">
                    <span class="font-semibold text-xs {{ $actualPaid > 0 ? 'text-emerald-700' : 'text-gray-500' }}">
                        Paid: ₹{{ number_format($actualPaid, 2) }}
                    </span>
                    @if($remDue > 0 && $actualPaid > 0)
                        <span class="text-[10px] font-medium text-amber-700">Due: ₹{{ number_format($remDue, 2) }}</span>
                    @elseif($actualPaid == 0)
                        <span class="text-[10px] text-gray-400">Due: ₹{{ number_format($pr->net_salary, 2) }}</span>
                    @endif
                </div>
            </td>
            <td class="py-3 px-4">
                <x-badge :variant="$pr->status">{{ $pr->status === 'partial' ? 'Partially Paid' : ucfirst($pr->status) }}</x-badge>
            </td>
            <td class="py-3 px-4 text-right">
                <div class="inline-flex items-center gap-1.5">
                    @if($canDisburse && $pr->status !== 'paid')
                    <button type="button" 
                        onclick="openDisburseModal({{ $pr->id }}, '{{ addslashes($pr->employee->full_name) }}', '{{ $pr->employee->employee_code }}', {{ (float)$pr->net_salary }}, {{ $actualPaid }}, {{ $remDue }}, '{{ $pr->month }}')"
                        class="px-2.5 py-1 bg-[#2D2D2D] text-white rounded text-xs hover:bg-[#1a1a1a] flex items-center gap-1 font-semibold transition-colors" 
                        title="{{ $pr->status === 'partial' ? 'Pay Remaining / Add Payment' : 'Disburse Payment' }}">
                        <i class="fas fa-hand-holding-usd text-[10px]"></i> {{ $pr->status === 'partial' ? 'Pay More' : 'Pay' }}
                    </button>
                    @endif

                    @if($canDisburse || $canGeneratePayroll)
                    <button type="button" 
                        onclick="openEditPayrollModal({{ $pr->id }}, '{{ addslashes($pr->employee->full_name) }}', '{{ $pr->employee->employee_code }}', {{ (float)$pr->basic_salary }}, {{ (float)$pr->allowances }}, {{ (float)$pr->deductions }}, {{ (float)$pr->net_salary }}, {{ $actualPaid }}, '{{ $pr->status }}', '{{ $pr->payment_method ?? 'Bank Transfer' }}', '{{ $pr->payment_date ? $pr->payment_date->format('Y-m-d') : now()->toDateString() }}', '{{ addslashes($pr->remarks ?? '') }}')"
                        class="px-2 py-1 border border-gray-300 rounded text-xs text-[#2D2D2D] hover:bg-gray-100 flex items-center gap-1 transition-colors" 
                        title="Manually Adjust / Edit Figures">
                        <i class="fas fa-edit text-[10px]"></i> Adjust
                    </button>
                    @endif

                    <a href="{{ route('payroll.payslip', $pr->id) }}" target="_blank" class="px-2.5 py-1 border border-gray-300 rounded text-xs text-[#2D2D2D] hover:bg-gray-100 flex items-center gap-1 transition-colors" title="Print Payslip">
                        <i class="fas fa-file-invoice text-[10px]"></i> Slip
                    </a>
                </div>
            </td>
        </tr>
        @endforeach
    </x-table>
</div>

{{-- Disburse Payment Modal (Manual or Full Payment) --}}
<div id="disburseModal" class="fixed inset-0 bg-[#2D2D2D]/60 flex items-center justify-center z-50 p-4" style="display: none;">
    <div class="bg-white rounded-lg max-w-lg w-full p-6 shadow-2xl border border-gray-200">
        <div class="flex items-center justify-between pb-3 border-b border-gray-200">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded bg-emerald-50 text-emerald-700 flex items-center justify-center text-sm font-bold">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-[#2D2D2D]">Disburse Salary Payment</h3>
                    <p id="disburseEmpInfo" class="text-xs text-gray-500 font-medium">Employee</p>
                </div>
            </div>
            <button type="button" onclick="closeDisburseModal()" class="text-gray-400 hover:text-gray-600 text-sm p-1">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="disburseForm" method="POST" class="mt-4 space-y-4">
            @csrf

            {{-- Summary Card --}}
            <div class="grid grid-cols-3 gap-2 p-3 bg-gray-50 rounded border border-gray-200 text-xs">
                <div>
                    <span class="text-gray-500 block text-[10px] uppercase font-bold">Net Salary</span>
                    <span id="disburseNetSalary" class="font-bold text-[#2D2D2D] text-sm">₹0.00</span>
                </div>
                <div>
                    <span class="text-gray-500 block text-[10px] uppercase font-bold">Already Paid</span>
                    <span id="disburseAlreadyPaid" class="font-bold text-emerald-700 text-sm">₹0.00</span>
                </div>
                <div>
                    <span class="text-gray-500 block text-[10px] uppercase font-bold">Due Balance</span>
                    <span id="disburseDueBalance" class="font-bold text-amber-700 text-sm">₹0.00</span>
                </div>
            </div>

            {{-- Payment Mode Choice --}}
            <div>
                <label class="block font-semibold text-xs text-[#2D2D2D] mb-1.5">Payment Option</label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center gap-2 p-2.5 border rounded border-gray-300 cursor-pointer hover:bg-gray-50 text-xs" id="labelFullPay">
                        <input type="radio" name="payment_type" value="full" id="radioFullPay" onchange="handlePaymentTypeChange()" class="text-[#2D2D2D] focus:ring-0">
                        <div>
                            <span class="font-bold text-[#2D2D2D] block">Pay Full Remaining</span>
                            <span id="textFullPayAmount" class="text-[11px] text-gray-500">₹0.00</span>
                        </div>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 border rounded border-gray-300 cursor-pointer hover:bg-gray-50 text-xs" id="labelCustomPay">
                        <input type="radio" name="payment_type" value="custom" id="radioCustomPay" checked onchange="handlePaymentTypeChange()" class="text-[#2D2D2D] focus:ring-0">
                        <div>
                            <span class="font-bold text-[#2D2D2D] block">Manual Amount</span>
                            <span class="text-[11px] text-gray-500">Set custom / partial payment</span>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Amount Input --}}
            <div>
                <label class="block font-semibold text-xs text-[#2D2D2D] mb-1">
                    Amount to Disburse (₹) *
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 font-bold text-sm">₹</span>
                    <input type="number" step="0.01" min="0.01" name="paid_amount" id="disburseAmountInput" required oninput="calculateDisbursementFeedback()"
                        class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded text-sm font-bold text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                </div>
                <div id="disbursementFeedback" class="mt-1.5 p-2 bg-blue-50 border border-blue-200 rounded text-[11px] text-blue-800 flex items-center justify-between">
                    <span id="feedbackText">Feedback</span>
                    <span id="feedbackBadge" class="font-bold">Status</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-xs text-[#2D2D2D] mb-1">Payment Date *</label>
                    <input type="date" name="payment_date" id="disburseDateInput" required value="{{ now()->toDateString() }}"
                        class="w-full px-3 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                </div>
                <div>
                    <label class="block font-semibold text-xs text-[#2D2D2D] mb-1">Payment Method *</label>
                    <select name="payment_method" id="disburseMethodSelect" class="w-full px-3 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D] bg-white">
                        <option value="Bank Transfer">Bank Transfer (NEFT/RTGS)</option>
                        <option value="Cash">Cash</option>
                        <option value="Cheque">Cheque</option>
                        <option value="UPI">UPI / Digital Wallet</option>
                        <option value="Direct Deposit">Direct Deposit</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-semibold text-xs text-[#2D2D2D] mb-1">Remarks / Reference (Optional)</label>
                <input type="text" name="remarks" id="disburseRemarksInput" placeholder="e.g. 1st installment, Advance deduction adjusted, Txn Ref #12345"
                    class="w-full px-3 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
            </div>

            <div class="pt-3 border-t border-gray-200 flex items-center justify-end gap-2">
                <button type="button" onclick="closeDisburseModal()" class="px-4 py-2 border border-gray-300 rounded text-xs font-semibold text-gray-700 hover:bg-gray-100">
                    Cancel
                </button>
                <button type="submit" id="btnSubmitDisburse" class="px-5 py-2 bg-[#2D2D2D] text-white rounded text-xs font-bold hover:bg-[#1a1a1a] flex items-center gap-1.5 shadow-sm">
                    <i class="fas fa-check"></i> Confirm Payment
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Edit / Manual Adjust Payroll Figures Modal --}}
<div id="editPayrollModal" class="fixed inset-0 bg-[#2D2D2D]/60 flex items-center justify-center z-50 p-4" style="display: none;">
    <div class="bg-white rounded-lg max-w-xl w-full p-6 shadow-2xl border border-gray-200">
        <div class="flex items-center justify-between pb-3 border-b border-gray-200">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded bg-gray-100 text-[#2D2D2D] flex items-center justify-center text-sm font-bold">
                    <i class="fas fa-sliders-h"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-[#2D2D2D]">Adjust Payroll Figures Manually</h3>
                    <p id="editEmpInfo" class="text-xs text-gray-500 font-medium">Employee</p>
                </div>
            </div>
            <button type="button" onclick="closeEditPayrollModal()" class="text-gray-400 hover:text-gray-600 text-sm p-1">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="editPayrollForm" method="POST" class="mt-4 space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block font-semibold text-xs text-[#2D2D2D] mb-1">Basic Salary (₹) *</label>
                    <input type="number" step="0.01" min="0" name="basic_salary" id="editBasicInput" required oninput="calculateEditNetSalary()"
                        class="w-full px-3 py-1.5 border border-gray-300 rounded text-xs font-semibold text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                </div>
                <div>
                    <label class="block font-semibold text-xs text-[#2D2D2D] mb-1">Allowances (+) (₹) *</label>
                    <input type="number" step="0.01" min="0" name="allowances" id="editAllowancesInput" required oninput="calculateEditNetSalary()"
                        class="w-full px-3 py-1.5 border border-gray-300 rounded text-xs font-semibold text-emerald-700 focus:outline-none focus:border-[#2D2D2D]">
                </div>
                <div>
                    <label class="block font-semibold text-xs text-[#2D2D2D] mb-1">Deductions (-) (₹) *</label>
                    <input type="number" step="0.01" min="0" name="deductions" id="editDeductionsInput" required oninput="calculateEditNetSalary()"
                        class="w-full px-3 py-1.5 border border-gray-300 rounded text-xs font-semibold text-rose-700 focus:outline-none focus:border-[#2D2D2D]">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 bg-gray-50 rounded border border-gray-200">
                <div>
                    <label class="block font-bold text-xs text-[#2D2D2D] mb-1">Calculated Net Salary (₹)</label>
                    <input type="number" step="0.01" min="0" name="net_salary" id="editNetSalaryInput" required
                        class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm font-bold text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D] bg-white">
                    <span class="text-[10px] text-gray-500 mt-0.5 block">Auto calculated: Basic + Allowances - Deductions (or override manually)</span>
                </div>
                <div>
                    <label class="block font-bold text-xs text-[#2D2D2D] mb-1">Amount Paid So Far (₹)</label>
                    <input type="number" step="0.01" min="0" name="paid_amount" id="editPaidAmountInput"
                        class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm font-bold text-emerald-700 focus:outline-none focus:border-[#2D2D2D] bg-white">
                    <span class="text-[10px] text-gray-500 mt-0.5 block">Total amount actually disbursed</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block font-semibold text-xs text-[#2D2D2D] mb-1">Status</label>
                    <select name="status" id="editStatusSelect" class="w-full px-3 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D] bg-white font-semibold">
                        <option value="pending">Pending</option>
                        <option value="partial">Partially Paid</option>
                        <option value="paid">Paid (Full)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-xs text-[#2D2D2D] mb-1">Payment Date</label>
                    <input type="date" name="payment_date" id="editPaymentDateInput"
                        class="w-full px-3 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                </div>
                <div>
                    <label class="block font-semibold text-xs text-[#2D2D2D] mb-1">Payment Method</label>
                    <select name="payment_method" id="editPaymentMethodSelect" class="w-full px-3 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D] bg-white">
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Cash">Cash</option>
                        <option value="Cheque">Cheque</option>
                        <option value="UPI">UPI</option>
                        <option value="Direct Deposit">Direct Deposit</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-semibold text-xs text-[#2D2D2D] mb-1">Remarks / Adjustment Notes</label>
                <input type="text" name="remarks" id="editRemarksInput" placeholder="e.g. Unpaid leave deduction applied, Incentive added"
                    class="w-full px-3 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
            </div>

            <div class="pt-3 border-t border-gray-200 flex items-center justify-end gap-2">
                <button type="button" onclick="closeEditPayrollModal()" class="px-4 py-2 border border-gray-300 rounded text-xs font-semibold text-gray-700 hover:bg-gray-100">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 bg-[#2D2D2D] text-white rounded text-xs font-bold hover:bg-[#1a1a1a] flex items-center gap-1.5 shadow-sm">
                    <i class="fas fa-save"></i> Save Adjustments
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentDisburseContext = {
        id: 0,
        netSalary: 0,
        alreadyPaid: 0,
        remDue: 0
    };

    function openDisburseModal(id, empName, empCode, netSalary, alreadyPaid, remDue, month) {
        currentDisburseContext = { id, netSalary, alreadyPaid, remDue };

        document.getElementById('disburseForm').action = `/payroll/${id}/pay`;
        document.getElementById('disburseEmpInfo').textContent = `${empName} (${empCode}) — ${month}`;
        document.getElementById('disburseNetSalary').textContent = '₹' + netSalary.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('disburseAlreadyPaid').textContent = '₹' + alreadyPaid.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('disburseDueBalance').textContent = '₹' + remDue.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('textFullPayAmount').textContent = '₹' + remDue.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        // Default to custom/manual amount input populated with remDue
        document.getElementById('radioCustomPay').checked = true;
        const amountInput = document.getElementById('disburseAmountInput');
        amountInput.value = remDue > 0 ? remDue.toFixed(2) : netSalary.toFixed(2);
        amountInput.readOnly = false;

        calculateDisbursementFeedback();

        document.getElementById('disburseModal').style.display = 'flex';
        setTimeout(() => amountInput.focus(), 50);
    }

    function closeDisburseModal() {
        document.getElementById('disburseModal').style.display = 'none';
    }

    function handlePaymentTypeChange() {
        const isFull = document.getElementById('radioFullPay').checked;
        const amountInput = document.getElementById('disburseAmountInput');
        if (isFull) {
            amountInput.value = currentDisburseContext.remDue > 0 ? currentDisburseContext.remDue.toFixed(2) : currentDisburseContext.netSalary.toFixed(2);
            amountInput.readOnly = true;
        } else {
            amountInput.readOnly = false;
            amountInput.focus();
        }
        calculateDisbursementFeedback();
    }

    function calculateDisbursementFeedback() {
        const inputVal = parseFloat(document.getElementById('disburseAmountInput').value) || 0;
        const newTotalPaid = currentDisburseContext.alreadyPaid + inputVal;
        const newRemaining = Math.max(0, currentDisburseContext.netSalary - newTotalPaid);
        const feedbackEl = document.getElementById('feedbackText');
        const badgeEl = document.getElementById('feedbackBadge');

        if (inputVal <= 0) {
            feedbackEl.textContent = 'Please enter a valid disbursement amount greater than 0.';
            badgeEl.textContent = 'Invalid';
            badgeEl.className = 'font-bold text-rose-600';
            return;
        }

        if (newTotalPaid >= currentDisburseContext.netSalary) {
            feedbackEl.textContent = `Full payment settled. Net salary (₹${currentDisburseContext.netSalary.toLocaleString('en-IN')}) will be 100% paid.`;
            badgeEl.textContent = 'Full Payment (Paid)';
            badgeEl.className = 'font-bold text-emerald-700';
        } else {
            feedbackEl.textContent = `Partial payment. Remaining balance after this disbursement: ₹${newRemaining.toLocaleString('en-IN', { minimumFractionDigits: 2 })}`;
            badgeEl.textContent = 'Partial Payment';
            badgeEl.className = 'font-bold text-amber-700';
        }
    }

    function openEditPayrollModal(id, empName, empCode, basic, allowances, deductions, netSalary, paidAmount, status, method, date, remarks) {
        document.getElementById('editPayrollForm').action = `/payroll/${id}`;
        document.getElementById('editEmpInfo').textContent = `${empName} (${empCode})`;
        document.getElementById('editBasicInput').value = basic.toFixed(2);
        document.getElementById('editAllowancesInput').value = allowances.toFixed(2);
        document.getElementById('editDeductionsInput').value = deductions.toFixed(2);
        document.getElementById('editNetSalaryInput').value = netSalary.toFixed(2);
        document.getElementById('editPaidAmountInput').value = paidAmount.toFixed(2);
        document.getElementById('editStatusSelect').value = status;
        document.getElementById('editPaymentMethodSelect').value = method || 'Bank Transfer';
        document.getElementById('editPaymentDateInput').value = date || '';
        document.getElementById('editRemarksInput').value = remarks || '';

        document.getElementById('editPayrollModal').style.display = 'flex';
    }

    function closeEditPayrollModal() {
        document.getElementById('editPayrollModal').style.display = 'none';
    }

    function calculateEditNetSalary() {
        const basic = parseFloat(document.getElementById('editBasicInput').value) || 0;
        const allowances = parseFloat(document.getElementById('editAllowancesInput').value) || 0;
        const deductions = parseFloat(document.getElementById('editDeductionsInput').value) || 0;
        const net = Math.max(0, basic + allowances - deductions);
        document.getElementById('editNetSalaryInput').value = net.toFixed(2);
    }

    // Close on Escape or click outside
    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDisburseModal();
            closeEditPayrollModal();
        }
    });

    document.getElementById('disburseModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeDisburseModal();
    });

    document.getElementById('editPayrollModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeEditPayrollModal();
    });
</script>
@endsection
