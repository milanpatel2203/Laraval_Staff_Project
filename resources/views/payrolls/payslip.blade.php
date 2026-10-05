<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - {{ $payroll->employee->full_name }} ({{ $payroll->month }})</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background-color: #FFFFFF !important;
            }
        }
    </style>
</head>

<body class="bg-[#F5F5F5] text-[#2D2D2D] p-6 font-sans antialiased">
    <div class="max-w-2xl mx-auto space-y-4">

        {{-- Actions --}}
        <div class="no-print flex items-center justify-between">
            <a href="{{ route('payroll.index', ['month' => $payroll->month]) }}"
                class="px-3.5 py-1.5 border border-gray-300 rounded text-xs bg-white text-gray-700 hover:bg-gray-100 flex items-center gap-1.5 transition-colors">
                <i class="fas fa-arrow-left"></i> Back to Payroll
            </a>
            <div class="flex items-center gap-2">
                <button id="downloadPdfBtn" onclick="downloadPayslipPdf()"
                    class="px-4 py-1.5 bg-[#2D2D2D] text-white rounded text-xs font-semibold hover:bg-[#1a1a1a] flex items-center gap-1.5 transition-colors shadow-sm">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </button>
                <button onclick="window.print()"
                    class="px-4 py-1.5 border border-gray-300 bg-white text-[#2D2D2D] rounded text-xs font-semibold hover:bg-gray-100 flex items-center gap-1.5 transition-colors">
                    <i class="fas fa-print"></i> Print Payslip
                </button>
            </div>
        </div>

        {{-- Payslip Document --}}
        <div id="payslipDocument" class="bg-white border border-gray-300 rounded p-8 shadow-sm">
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-gray-200 pb-6 mb-6">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-[#2D2D2D]">{{ \App\Models\Setting::get('company_name', 'UEST TECHNOLOGIES') }}</h1>
                    <p class="text-xs text-gray-500 mt-0.5">{{ \App\Models\Setting::get('company_address', 'Technology Park, Ahmedabad, Gujarat') }}</p>
                    <p class="text-xs text-gray-500">{{ \App\Models\Setting::get('company_email', 'hr@uesthrms.com') }} | {{ \App\Models\Setting::get('company_phone', '+91 79 1234 5678') }}</p>
                </div>
                <div class="text-right">
                    @if($payroll->status === 'paid')
                        <span
                            class="inline-block px-2.5 py-1 text-xs font-bold uppercase rounded bg-emerald-700 text-white">PAID (FULL)</span>
                    @elseif($payroll->status === 'partial')
                        <span
                            class="inline-block px-2.5 py-1 text-xs font-bold uppercase rounded bg-amber-600 text-white">PARTIALLY PAID</span>
                    @else
                        <span
                            class="inline-block px-2.5 py-1 text-xs font-bold uppercase rounded bg-gray-200 text-gray-800">PENDING</span>
                    @endif
                    <p class="text-sm font-semibold text-[#2D2D2D] mt-2">Payslip for
                        {{ \Carbon\Carbon::parse($payroll->month . '-01')->format('F Y') }}</p>
                </div>
            </div>

            {{-- Employee Details --}}
            <div class="grid grid-cols-2 gap-4 text-xs mb-6 p-4 bg-gray-50 rounded border border-gray-200">
                <div>
                    <span class="text-gray-500 block">Employee Name:</span>
                    <strong class="text-sm text-[#2D2D2D]">{{ $payroll->employee->full_name }}</strong>
                </div>
                <div>
                    <span class="text-gray-500 block">Employee Code:</span>
                    <strong class="text-sm text-[#2D2D2D]">{{ $payroll->employee->employee_code }}</strong>
                </div>
                <div>
                    <span class="text-gray-500 block">Department:</span>
                    <span
                        class="font-medium text-[#2D2D2D]">{{ $payroll->employee->department ? $payroll->employee->department->name : 'General' }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block">Designation:</span>
                    <span class="font-medium text-[#2D2D2D]">{{ $payroll->employee->designation }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block">Payment Date:</span>
                    <span
                        class="font-medium text-[#2D2D2D]">{{ $payroll->payment_date ? $payroll->payment_date->format('d M Y') : 'Pending' }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block">Payment Method:</span>
                    <span
                        class="font-medium text-[#2D2D2D]">{{ $payroll->payment_method ?: 'Direct Bank Transfer' }}</span>
                </div>
            </div>

            {{-- Salary Breakdown Tables --}}
            @php
                $cfg = \App\Http\Controllers\PayrollController::getPayrollConfig();
                $basic = (float) $payroll->basic_salary;

                // Allowance components (actual ₹ amounts based on configured %)
                $hraAmt = round($basic * $cfg['payroll_hra_percent'] / 100, 2);
                $daAmt = round($basic * $cfg['payroll_da_percent'] / 100, 2);
                $convAmt = round($basic * $cfg['payroll_conveyance_percent'] / 100, 2);
                $medAmt = round($basic * $cfg['payroll_medical_percent'] / 100, 2);
                $specAmt = round($basic * $cfg['payroll_special_allowance_percent'] / 100, 2);

                // Deduction components
                $pfAmt = round($basic * $cfg['payroll_pf_percent'] / 100, 2);
                $ptAmt = round($basic * $cfg['payroll_pt_percent'] / 100, 2);
                $esiAmt = round($basic * $cfg['payroll_esi_percent'] / 100, 2);
                $tdsAmt = round($basic * $cfg['payroll_tds_percent'] / 100, 2);
                $otherDedAmt = round($basic * $cfg['payroll_other_deduction_percent'] / 100, 2);
            @endphp
            <div class="grid grid-cols-2 gap-6 mb-6">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#2D2D2D] border-b border-gray-200 pb-2 mb-3">Earnings</h3>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Basic Salary</span>
                            <span class="font-medium text-[#2D2D2D]">₹{{ number_format($basic, 2) }}</span>
                        </div>
                        @if($hraAmt > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600">HRA ({{ $cfg['payroll_hra_percent'] }}%)</span>
                            <span class="font-medium text-[#2D2D2D]">₹{{ number_format($hraAmt, 2) }}</span>
                        </div>
                        @endif
                        @if($daAmt > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600">DA ({{ $cfg['payroll_da_percent'] }}%)</span>
                            <span class="font-medium text-[#2D2D2D]">₹{{ number_format($daAmt, 2) }}</span>
                        </div>
                        @endif
                        @if($convAmt > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Conveyance ({{ $cfg['payroll_conveyance_percent'] }}%)</span>
                            <span class="font-medium text-[#2D2D2D]">₹{{ number_format($convAmt, 2) }}</span>
                        </div>
                        @endif
                        @if($medAmt > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Medical ({{ $cfg['payroll_medical_percent'] }}%)</span>
                            <span class="font-medium text-[#2D2D2D]">₹{{ number_format($medAmt, 2) }}</span>
                        </div>
                        @endif
                        @if($specAmt > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Special Allowance ({{ $cfg['payroll_special_allowance_percent'] }}%)</span>
                            <span class="font-medium text-[#2D2D2D]">₹{{ number_format($specAmt, 2) }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between border-t border-gray-200 pt-2 font-bold">
                            <span>Total Gross Earnings</span>
                            <span>₹{{ number_format($payroll->basic_salary + $payroll->allowances, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#2D2D2D] border-b border-gray-200 pb-2 mb-3">Deductions</h3>
                    <div class="space-y-2 text-xs">
                        @if($pfAmt > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Provident Fund ({{ $cfg['payroll_pf_percent'] }}%)</span>
                            <span class="font-medium text-[#2D2D2D]">₹{{ number_format($pfAmt, 2) }}</span>
                        </div>
                        @endif
                        @if($ptAmt > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Professional Tax ({{ $cfg['payroll_pt_percent'] }}%)</span>
                            <span class="font-medium text-[#2D2D2D]">₹{{ number_format($ptAmt, 2) }}</span>
                        </div>
                        @endif
                        @if($esiAmt > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600">ESI ({{ $cfg['payroll_esi_percent'] }}%)</span>
                            <span class="font-medium text-[#2D2D2D]">₹{{ number_format($esiAmt, 2) }}</span>
                        </div>
                        @endif
                        @if($tdsAmt > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600">TDS / Income Tax ({{ $cfg['payroll_tds_percent'] }}%)</span>
                            <span class="font-medium text-[#2D2D2D]">₹{{ number_format($tdsAmt, 2) }}</span>
                        </div>
                        @endif
                        @if($otherDedAmt > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Other Deductions ({{ $cfg['payroll_other_deduction_percent'] }}%)</span>
                            <span class="font-medium text-[#2D2D2D]">₹{{ number_format($otherDedAmt, 2) }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between border-t border-gray-200 pt-2 font-bold">
                            <span>Total Deductions</span>
                            <span>₹{{ number_format($payroll->deductions, 2) }}</span>
                        </div>
                        @php
                            $effectiveDeductionRate = $basic > 0 ? round(($payroll->deductions / $basic) * 100, 1) : 0;
                        @endphp
                        <div class="text-[10px] text-gray-400 pt-1">
                            Effective Rate: <strong class="text-gray-600">{{ $effectiveDeductionRate }}%</strong> of Basic Salary
                        </div>
                    </div>
                </div>
            </div>

            {{-- Net Pay & Disbursement Summary Box --}}
            <div class="p-4 bg-[#2D2D2D] text-[#F5F5F5] rounded space-y-3 mb-8">
                <div class="flex items-center justify-between border-b border-gray-700/80 pb-3">
                    <div>
                        <span class="text-xs uppercase tracking-wider text-gray-300 block">Total Net Payable</span>
                        <span class="text-[11px] text-gray-400">Gross Earnings - Total Deductions</span>
                    </div>
                    <div class="text-xl font-bold">
                        ₹{{ number_format($payroll->net_salary, 2) }}
                    </div>
                </div>

                @php
                    $actualPaid = (float) ($payroll->paid_amount ?: ($payroll->status === 'paid' ? $payroll->net_salary : 0));
                    $remDue = max(0, (float) $payroll->net_salary - $actualPaid);
                @endphp

                <div class="grid grid-cols-2 gap-4 text-xs pt-1">
                    <div>
                        <span class="text-gray-400 block text-[10px] uppercase font-bold">Amount Disbursed</span>
                        <span class="text-base font-bold text-emerald-400">₹{{ number_format($actualPaid, 2) }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-gray-400 block text-[10px] uppercase font-bold">Remaining Due</span>
                        <span class="text-base font-bold {{ $remDue > 0 ? 'text-amber-400' : 'text-gray-400' }}">
                            ₹{{ number_format($remDue, 2) }}
                        </span>
                    </div>
                </div>

                @if($payroll->remarks)
                    <div class="pt-2 border-t border-gray-700/60 text-[11px] text-gray-300 flex items-center gap-1.5">
                        <i class="fas fa-info-circle text-gray-400"></i>
                        <span><strong>Note:</strong> {{ $payroll->remarks }}</span>
                    </div>
                @endif
            </div>

            {{-- Footer / Signatures --}}
            <div class="pt-8 border-t border-gray-200 flex justify-between text-xs text-gray-500">
                <div class="text-center">
                    <div class="h-10 border-b border-gray-300 w-36 mb-1"></div>
                    <span>Employee Signature</span>
                </div>
                <div class="text-center">
                    <div class="h-10 border-b border-gray-300 w-36 mb-1"></div>
                    <span>Authorized Signatory</span>
                </div>
            </div>
        </div>
    </div>

    {{-- PDF & Alert Dependencies --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function downloadPayslipPdf() {
            const btn = document.getElementById('downloadPdfBtn');
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';

            const element = document.getElementById('payslipDocument');
            const employeeName = "{{ Str::slug($payroll->employee->full_name) }}";
            const month = "{{ $payroll->month }}";
            const filename = `Payslip-${employeeName}-${month}.pdf`;

            const opt = {
                margin: [10, 10, 10, 10],
                filename: filename,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true, logging: false },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            html2pdf().set(opt).from(element).save().then(() => {
                btn.disabled = false;
                btn.innerHTML = originalHtml;

                // SweetAlert after PDF download completes
                Swal.fire({
                    icon: 'success',
                    title: 'PDF Downloaded!',
                    text: `Payslip (${filename}) has been downloaded successfully.`,
                    timer: 3500,
                    timerProgressBar: true,
                    confirmButtonColor: '#2D2D2D',
                    confirmButtonText: 'Done'
                });
            }).catch((err) => {
                btn.disabled = false;
                btn.innerHTML = originalHtml;

                Swal.fire({
                    icon: 'error',
                    title: 'Download Failed',
                    text: 'Unable to generate PDF automatically. Please try the Print Payslip option and select "Save as PDF".',
                    confirmButtonColor: '#2D2D2D'
                });
            });
        }

        // Print dialog completion alert
        window.addEventListener('afterprint', () => {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Payslip document processed!',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        });
    </script>
</body>

</html>
showConfirmButton: false,
timer: 3000,
timerProgressBar: true
});
});
</script>
</body>

</html>