@extends('layouts.app')

@section('title', 'Payroll Configuration')
@section('page-title', 'Payroll Configuration')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-[#2D2D2D]">Payroll Configuration</h2>
            <p class="text-xs text-gray-500 mt-0.5">
                Configure salary component percentages. Changes apply to all future payroll generations.
            </p>
        </div>
        <a href="{{ route('payroll.index') }}" class="px-3.5 py-1.5 border border-gray-300 rounded text-xs bg-white text-gray-700 hover:bg-gray-100 flex items-center gap-1.5 transition-colors shadow-sm">
            <i class="fas fa-arrow-left"></i> Back to Payroll
        </a>
    </div>

    <form action="{{ route('payroll.configuration.update') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- ============================================================= --}}
            {{-- DEDUCTION COMPONENTS --}}
            {{-- ============================================================= --}}
            <div class="bg-white border border-gray-200 rounded overflow-hidden shadow-xs">
                <div class="px-5 py-3.5 bg-gray-50/70 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-[#2D2D2D] flex items-center gap-2">
                        <i class="fas fa-minus-circle text-rose-600"></i>
                        <span>Deduction Components</span>
                    </h3>
                    <span class="text-[10px] font-medium text-gray-400 uppercase tracking-wider">% of Basic Salary</span>
                </div>
                <div class="p-5 space-y-4">

                    {{-- Provident Fund (PF) --}}
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex-1">
                            <label class="block font-semibold text-xs text-[#2D2D2D] mb-0.5">Provident Fund (PF)</label>
                            <span class="text-[10px] text-gray-400">Employee's PF contribution deducted from salary</span>
                        </div>
                        <div class="relative w-28">
                            <input type="number" step="0.01" min="0" max="100" name="payroll_pf_percent"
                                value="{{ $config['payroll_pf_percent'] }}"
                                class="w-full pl-3 pr-7 py-2 border border-gray-300 rounded text-xs text-rose-700 font-bold focus:outline-none focus:border-[#2D2D2D] text-right">
                            <span class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-gray-400 text-xs font-bold">%</span>
                        </div>
                    </div>

                    <hr class="border-gray-100">

                    {{-- Professional Tax (PT) --}}
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex-1">
                            <label class="block font-semibold text-xs text-[#2D2D2D] mb-0.5">Professional Tax (PT)</label>
                            <span class="text-[10px] text-gray-400">State-mandated professional tax deduction</span>
                        </div>
                        <div class="relative w-28">
                            <input type="number" step="0.01" min="0" max="100" name="payroll_pt_percent"
                                value="{{ $config['payroll_pt_percent'] }}"
                                class="w-full pl-3 pr-7 py-2 border border-gray-300 rounded text-xs text-rose-700 font-bold focus:outline-none focus:border-[#2D2D2D] text-right">
                            <span class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-gray-400 text-xs font-bold">%</span>
                        </div>
                    </div>

                    <hr class="border-gray-100">

                    {{-- ESI --}}
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex-1">
                            <label class="block font-semibold text-xs text-[#2D2D2D] mb-0.5">ESI (Employee State Insurance)</label>
                            <span class="text-[10px] text-gray-400">Insurance contribution for eligible employees</span>
                        </div>
                        <div class="relative w-28">
                            <input type="number" step="0.01" min="0" max="100" name="payroll_esi_percent"
                                value="{{ $config['payroll_esi_percent'] }}"
                                class="w-full pl-3 pr-7 py-2 border border-gray-300 rounded text-xs text-rose-700 font-bold focus:outline-none focus:border-[#2D2D2D] text-right">
                            <span class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-gray-400 text-xs font-bold">%</span>
                        </div>
                    </div>

                    <hr class="border-gray-100">

                    {{-- TDS / Income Tax --}}
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex-1">
                            <label class="block font-semibold text-xs text-[#2D2D2D] mb-0.5">TDS / Income Tax</label>
                            <span class="text-[10px] text-gray-400">Tax deducted at source (average slab rate)</span>
                        </div>
                        <div class="relative w-28">
                            <input type="number" step="0.01" min="0" max="100" name="payroll_tds_percent"
                                value="{{ $config['payroll_tds_percent'] }}"
                                class="w-full pl-3 pr-7 py-2 border border-gray-300 rounded text-xs text-rose-700 font-bold focus:outline-none focus:border-[#2D2D2D] text-right">
                            <span class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-gray-400 text-xs font-bold">%</span>
                        </div>
                    </div>

                    <hr class="border-gray-100">

                    {{-- Other Deductions --}}
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex-1">
                            <label class="block font-semibold text-xs text-[#2D2D2D] mb-0.5">Other Deductions</label>
                            <span class="text-[10px] text-gray-400">Loan recovery, advance adjustments, etc.</span>
                        </div>
                        <div class="relative w-28">
                            <input type="number" step="0.01" min="0" max="100" name="payroll_other_deduction_percent"
                                value="{{ $config['payroll_other_deduction_percent'] }}"
                                class="w-full pl-3 pr-7 py-2 border border-gray-300 rounded text-xs text-rose-700 font-bold focus:outline-none focus:border-[#2D2D2D] text-right">
                            <span class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-gray-400 text-xs font-bold">%</span>
                        </div>
                    </div>

                    {{-- Total Deduction Preview --}}
                    <div class="mt-2 p-3 bg-rose-50 border border-rose-200 rounded flex items-center justify-between">
                        <span class="text-xs font-bold text-rose-800 flex items-center gap-1.5">
                            <i class="fas fa-calculator text-rose-500"></i>
                            Total Deduction Rate
                        </span>
                        <span id="totalDeductionPreview" class="text-sm font-bold text-rose-700">0%</span>
                    </div>
                </div>
            </div>

            {{-- ============================================================= --}}
            {{-- ALLOWANCE COMPONENTS --}}
            {{-- ============================================================= --}}
            <div class="bg-white border border-gray-200 rounded overflow-hidden shadow-xs">
                <div class="px-5 py-3.5 bg-gray-50/70 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-[#2D2D2D] flex items-center gap-2">
                        <i class="fas fa-plus-circle text-emerald-600"></i>
                        <span>Allowance Components</span>
                    </h3>
                    <span class="text-[10px] font-medium text-gray-400 uppercase tracking-wider">% of Basic Salary</span>
                </div>
                <div class="p-5 space-y-4">

                    {{-- HRA --}}
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex-1">
                            <label class="block font-semibold text-xs text-[#2D2D2D] mb-0.5">House Rent Allowance (HRA)</label>
                            <span class="text-[10px] text-gray-400">Housing and rent support allowance</span>
                        </div>
                        <div class="relative w-28">
                            <input type="number" step="0.01" min="0" max="100" name="payroll_hra_percent"
                                value="{{ $config['payroll_hra_percent'] }}"
                                class="w-full pl-3 pr-7 py-2 border border-gray-300 rounded text-xs text-emerald-700 font-bold focus:outline-none focus:border-[#2D2D2D] text-right">
                            <span class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-gray-400 text-xs font-bold">%</span>
                        </div>
                    </div>

                    <hr class="border-gray-100">

                    {{-- Dearness Allowance (DA) --}}
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex-1">
                            <label class="block font-semibold text-xs text-[#2D2D2D] mb-0.5">Dearness Allowance (DA)</label>
                            <span class="text-[10px] text-gray-400">Cost of living adjustment allowance</span>
                        </div>
                        <div class="relative w-28">
                            <input type="number" step="0.01" min="0" max="100" name="payroll_da_percent"
                                value="{{ $config['payroll_da_percent'] }}"
                                class="w-full pl-3 pr-7 py-2 border border-gray-300 rounded text-xs text-emerald-700 font-bold focus:outline-none focus:border-[#2D2D2D] text-right">
                            <span class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-gray-400 text-xs font-bold">%</span>
                        </div>
                    </div>

                    <hr class="border-gray-100">

                    {{-- Conveyance Allowance --}}
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex-1">
                            <label class="block font-semibold text-xs text-[#2D2D2D] mb-0.5">Conveyance Allowance</label>
                            <span class="text-[10px] text-gray-400">Travel and transport allowance</span>
                        </div>
                        <div class="relative w-28">
                            <input type="number" step="0.01" min="0" max="100" name="payroll_conveyance_percent"
                                value="{{ $config['payroll_conveyance_percent'] }}"
                                class="w-full pl-3 pr-7 py-2 border border-gray-300 rounded text-xs text-emerald-700 font-bold focus:outline-none focus:border-[#2D2D2D] text-right">
                            <span class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-gray-400 text-xs font-bold">%</span>
                        </div>
                    </div>

                    <hr class="border-gray-100">

                    {{-- Medical Allowance --}}
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex-1">
                            <label class="block font-semibold text-xs text-[#2D2D2D] mb-0.5">Medical Allowance</label>
                            <span class="text-[10px] text-gray-400">Health and medical expense support</span>
                        </div>
                        <div class="relative w-28">
                            <input type="number" step="0.01" min="0" max="100" name="payroll_medical_percent"
                                value="{{ $config['payroll_medical_percent'] }}"
                                class="w-full pl-3 pr-7 py-2 border border-gray-300 rounded text-xs text-emerald-700 font-bold focus:outline-none focus:border-[#2D2D2D] text-right">
                            <span class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-gray-400 text-xs font-bold">%</span>
                        </div>
                    </div>

                    <hr class="border-gray-100">

                    {{-- Special Allowance --}}
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex-1">
                            <label class="block font-semibold text-xs text-[#2D2D2D] mb-0.5">Special Allowance</label>
                            <span class="text-[10px] text-gray-400">Performance bonus, incentive, or special pay</span>
                        </div>
                        <div class="relative w-28">
                            <input type="number" step="0.01" min="0" max="100" name="payroll_special_allowance_percent"
                                value="{{ $config['payroll_special_allowance_percent'] }}"
                                class="w-full pl-3 pr-7 py-2 border border-gray-300 rounded text-xs text-emerald-700 font-bold focus:outline-none focus:border-[#2D2D2D] text-right">
                            <span class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-gray-400 text-xs font-bold">%</span>
                        </div>
                    </div>

                    {{-- Total Allowance Preview --}}
                    <div class="mt-2 p-3 bg-emerald-50 border border-emerald-200 rounded flex items-center justify-between">
                        <span class="text-xs font-bold text-emerald-800 flex items-center gap-1.5">
                            <i class="fas fa-calculator text-emerald-500"></i>
                            Total Allowance Rate
                        </span>
                        <span id="totalAllowancePreview" class="text-sm font-bold text-emerald-700">0%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Live Salary Preview --}}
        <div class="mt-6 bg-white border border-gray-200 rounded overflow-hidden shadow-xs">
            <div class="px-5 py-3.5 bg-gray-50/70 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-xs font-bold text-[#2D2D2D] flex items-center gap-2">
                    <i class="fas fa-eye text-blue-600"></i>
                    <span>Live Salary Preview</span>
                </h3>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] text-gray-500">Sample Basic:</span>
                    <input type="number" id="sampleBasic" value="50000" min="0" step="1000"
                        class="w-28 px-2 py-1 border border-gray-300 rounded text-xs text-[#2D2D2D] font-bold focus:outline-none focus:border-[#2D2D2D] text-right"
                        oninput="recalcPreview()">
                </div>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- Earnings Column --}}
                    <div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-2">Earnings</span>
                        <div class="space-y-1.5 text-xs">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Basic Salary</span>
                                <span id="pvBasic" class="font-semibold text-[#2D2D2D]">₹50,000</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">HRA</span>
                                <span id="pvHra" class="font-semibold text-emerald-700">₹0</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">DA</span>
                                <span id="pvDa" class="font-semibold text-emerald-700">₹0</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Conveyance</span>
                                <span id="pvConveyance" class="font-semibold text-emerald-700">₹0</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Medical</span>
                                <span id="pvMedical" class="font-semibold text-emerald-700">₹0</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Special</span>
                                <span id="pvSpecial" class="font-semibold text-emerald-700">₹0</span>
                            </div>
                            <div class="flex justify-between border-t border-gray-200 pt-1.5 font-bold">
                                <span>Total Earnings</span>
                                <span id="pvTotalEarnings" class="text-[#2D2D2D]">₹50,000</span>
                            </div>
                        </div>
                    </div>

                    {{-- Deductions Column --}}
                    <div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-2">Deductions</span>
                        <div class="space-y-1.5 text-xs">
                            <div class="flex justify-between">
                                <span class="text-gray-500">PF</span>
                                <span id="pvPf" class="font-semibold text-rose-700">₹0</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">PT</span>
                                <span id="pvPt" class="font-semibold text-rose-700">₹0</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">ESI</span>
                                <span id="pvEsi" class="font-semibold text-rose-700">₹0</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">TDS</span>
                                <span id="pvTds" class="font-semibold text-rose-700">₹0</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Other</span>
                                <span id="pvOther" class="font-semibold text-rose-700">₹0</span>
                            </div>
                            <div class="flex justify-between border-t border-gray-200 pt-1.5 font-bold">
                                <span>Total Deductions</span>
                                <span id="pvTotalDeductions" class="text-rose-700">₹0</span>
                            </div>
                        </div>
                    </div>

                    {{-- Net Pay Summary --}}
                    <div class="sm:col-span-2">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-2">Net Pay Summary</span>
                        <div class="p-4 bg-[#2D2D2D] text-white rounded space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-300">Gross Earnings</span>
                                <span id="pvGross" class="text-sm font-bold text-emerald-400">₹0</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-300">Total Deductions</span>
                                <span id="pvDedSummary" class="text-sm font-bold text-rose-400">-₹0</span>
                            </div>
                            <hr class="border-gray-600">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold text-white">Net Payable (Take Home)</span>
                                <span id="pvNetPay" class="text-xl font-bold text-white">₹0</span>
                            </div>
                        </div>
                        <div class="mt-3 p-2.5 bg-blue-50 border border-blue-200 rounded text-[11px] text-blue-800 leading-relaxed">
                            <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                            This preview shows how payroll will be calculated using the above percentages. Adjust the sample basic salary to see the effect at different salary levels.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Save Button --}}
        <div class="mt-6 flex items-center justify-between">
            <div class="text-xs text-gray-500">
                <i class="fas fa-bolt text-amber-500 mr-1"></i>
                Saving will immediately recalculate all <strong>pending (unpaid)</strong> payroll records for {{ now()->format('F Y') }} with the new rates.
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('payroll.index') }}" class="px-4 py-2 border border-gray-300 rounded text-xs font-semibold text-gray-700 hover:bg-gray-100 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-[#2D2D2D] text-white rounded text-xs font-bold hover:bg-[#1a1a1a] flex items-center gap-2 shadow-sm transition-colors">
                    <i class="fas fa-save"></i> Save Configuration
                </button>
            </div>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
    function getVal(name) {
        const el = document.querySelector('input[name="' + name + '"]');
        return el ? parseFloat(el.value) || 0 : 0;
    }

    function fmt(n) {
        return '₹' + Math.round(n).toLocaleString('en-IN');
    }

    function recalcPreview() {
        const basic = parseFloat(document.getElementById('sampleBasic').value) || 0;

        // Deductions
        const pf = basic * getVal('payroll_pf_percent') / 100;
        const pt = basic * getVal('payroll_pt_percent') / 100;
        const esi = basic * getVal('payroll_esi_percent') / 100;
        const tds = basic * getVal('payroll_tds_percent') / 100;
        const other = basic * getVal('payroll_other_deduction_percent') / 100;
        const totalDed = pf + pt + esi + tds + other;

        // Allowances
        const hra = basic * getVal('payroll_hra_percent') / 100;
        const da = basic * getVal('payroll_da_percent') / 100;
        const conveyance = basic * getVal('payroll_conveyance_percent') / 100;
        const medical = basic * getVal('payroll_medical_percent') / 100;
        const special = basic * getVal('payroll_special_allowance_percent') / 100;
        const totalAll = hra + da + conveyance + medical + special;

        const gross = basic + totalAll;
        const net = Math.max(0, gross - totalDed);

        // Update deduction preview
        document.getElementById('pvPf').textContent = fmt(pf);
        document.getElementById('pvPt').textContent = fmt(pt);
        document.getElementById('pvEsi').textContent = fmt(esi);
        document.getElementById('pvTds').textContent = fmt(tds);
        document.getElementById('pvOther').textContent = fmt(other);
        document.getElementById('pvTotalDeductions').textContent = fmt(totalDed);

        // Update allowance preview
        document.getElementById('pvBasic').textContent = fmt(basic);
        document.getElementById('pvHra').textContent = fmt(hra);
        document.getElementById('pvDa').textContent = fmt(da);
        document.getElementById('pvConveyance').textContent = fmt(conveyance);
        document.getElementById('pvMedical').textContent = fmt(medical);
        document.getElementById('pvSpecial').textContent = fmt(special);
        document.getElementById('pvTotalEarnings').textContent = fmt(gross);

        // Summary
        document.getElementById('pvGross').textContent = fmt(gross);
        document.getElementById('pvDedSummary').textContent = '-' + fmt(totalDed);
        document.getElementById('pvNetPay').textContent = fmt(net);

        // Totals bar
        const totalDedPct = getVal('payroll_pf_percent') + getVal('payroll_pt_percent') + getVal('payroll_esi_percent') + getVal('payroll_tds_percent') + getVal('payroll_other_deduction_percent');
        const totalAllPct = getVal('payroll_hra_percent') + getVal('payroll_da_percent') + getVal('payroll_conveyance_percent') + getVal('payroll_medical_percent') + getVal('payroll_special_allowance_percent');

        document.getElementById('totalDeductionPreview').textContent = totalDedPct.toFixed(2) + '%';
        document.getElementById('totalAllowancePreview').textContent = totalAllPct.toFixed(2) + '%';
    }

    // Attach to all inputs
    document.querySelectorAll('input[type="number"]').forEach(function(el) {
        el.addEventListener('input', recalcPreview);
    });

    // Initial calculation
    recalcPreview();
</script>
@endpush
