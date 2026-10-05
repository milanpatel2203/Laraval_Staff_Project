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
            .no-print { display: none !important; }
            body { background-color: #FFFFFF !important; }
        }
    </style>
</head>
<body class="bg-[#F5F5F5] text-[#2D2D2D] p-6 font-sans antialiased">
    <div class="max-w-2xl mx-auto space-y-4">

        {{-- Actions --}}
        <div class="no-print flex items-center justify-between">
            <a href="{{ route('payroll.index', ['month' => $payroll->month]) }}" class="px-3 py-1.5 border border-gray-300 rounded text-xs bg-white text-gray-700 hover:bg-gray-100 flex items-center gap-1.5">
                <i class="fas fa-arrow-left"></i> Back to Payroll
            </a>
            <button onclick="window.print()" class="px-4 py-1.5 bg-[#2D2D2D] text-white rounded text-xs font-semibold hover:bg-[#1a1a1a] flex items-center gap-1.5">
                <i class="fas fa-print"></i> Print Payslip
            </button>
        </div>

        {{-- Payslip Document --}}
        <div class="bg-white border border-gray-300 rounded p-8 shadow-sm">
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-gray-200 pb-6 mb-6">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-[#2D2D2D]">UEST TECHNOLOGIES</h1>
                    <p class="text-xs text-gray-500 mt-0.5">Technology Park, Ahmedabad, Gujarat</p>
                    <p class="text-xs text-gray-500">hr@uesthrms.com | +91 79 1234 5678</p>
                </div>
                <div class="text-right">
                    <span class="inline-block px-2.5 py-1 text-xs font-bold uppercase rounded {{ $payroll->status === 'paid' ? 'bg-[#2D2D2D] text-white' : 'bg-gray-200 text-gray-800' }}">
                        {{ ucfirst($payroll->status) }}
                    </span>
                    <p class="text-sm font-semibold text-[#2D2D2D] mt-2">Payslip for {{ \Carbon\Carbon::parse($payroll->month . '-01')->format('F Y') }}</p>
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
                    <span class="font-medium text-[#2D2D2D]">{{ $payroll->employee->department ? $payroll->employee->department->name : 'General' }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block">Designation:</span>
                    <span class="font-medium text-[#2D2D2D]">{{ $payroll->employee->designation }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block">Payment Date:</span>
                    <span class="font-medium text-[#2D2D2D]">{{ $payroll->payment_date ? $payroll->payment_date->format('d M Y') : 'Pending' }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block">Payment Method:</span>
                    <span class="font-medium text-[#2D2D2D]">{{ $payroll->payment_method ?: 'Direct Bank Transfer' }}</span>
                </div>
            </div>

            {{-- Salary Breakdown Tables --}}
            <div class="grid grid-cols-2 gap-6 mb-6">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#2D2D2D] border-b border-gray-200 pb-2 mb-3">Earnings</h3>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Basic Salary</span>
                            <span class="font-medium text-[#2D2D2D]">₹{{ number_format($payroll->basic_salary, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Allowances (HRA + Special)</span>
                            <span class="font-medium text-[#2D2D2D]">₹{{ number_format($payroll->allowances, 2) }}</span>
                        </div>
                        <div class="flex justify-between border-t border-gray-200 pt-2 font-bold">
                            <span>Total Gross Earnings</span>
                            <span>₹{{ number_format($payroll->basic_salary + $payroll->allowances, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#2D2D2D] border-b border-gray-200 pb-2 mb-3">Deductions</h3>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Provident Fund (PF)</span>
                            <span class="font-medium text-[#2D2D2D]">₹{{ number_format($payroll->deductions * 0.6, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Professional Tax (PT)</span>
                            <span class="font-medium text-[#2D2D2D]">₹{{ number_format($payroll->deductions * 0.4, 2) }}</span>
                        </div>
                        <div class="flex justify-between border-t border-gray-200 pt-2 font-bold">
                            <span>Total Deductions</span>
                            <span>₹{{ number_format($payroll->deductions, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Net Pay Box --}}
            <div class="p-4 bg-[#2D2D2D] text-[#F5F5F5] rounded flex items-center justify-between mb-8">
                <div>
                    <span class="text-xs uppercase tracking-wider text-gray-300 block">Net Payable Amount</span>
                    <span class="text-xs text-gray-400">Total Gross Earnings - Total Deductions</span>
                </div>
                <div class="text-2xl font-bold">
                    ₹{{ number_format($payroll->net_salary, 2) }}
                </div>
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
</body>
</html>
