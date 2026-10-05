@extends('layouts.app')

@section('title', 'Payroll Management')
@section('page-title', 'Payroll')

@section('content')
<div class="space-y-6">

    @if(session('success'))
    <div class="bg-white border border-[#2D2D2D] text-[#2D2D2D] px-4 py-3 rounded flex items-center justify-between text-sm font-medium">
        <div class="flex items-center gap-2.5">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-xs text-gray-500 hover:text-black">&times;</button>
    </div>
    @endif

    {{-- Header & Month Selector --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-[#2D2D2D]">Payroll — {{ \Carbon\Carbon::parse($currentMonth . '-01')->format('F Y') }}</h2>
            <p class="text-xs text-gray-500 mt-0.5">Generate salary slips, manage disbursements, and view payslips.</p>
        </div>
        <div class="flex items-center gap-3">
            <form action="{{ route('payroll.index') }}" method="GET" class="flex items-center gap-2">
                <input type="month" name="month" value="{{ $currentMonth }}" class="px-3 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                <button type="submit" class="px-3 py-1.5 border border-gray-300 bg-white text-xs font-medium rounded hover:bg-gray-100">Go</button>
            </form>
            <form action="{{ route('payroll.generate') }}" method="POST">
                @csrf
                <input type="hidden" name="month" value="{{ $currentMonth }}">
                <button type="submit" class="px-4 py-2 bg-[#2D2D2D] text-white rounded text-xs font-semibold hover:bg-[#1a1a1a] flex items-center gap-1.5">
                    <i class="fas fa-sync-alt"></i> Generate Payroll
                </button>
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-200 rounded p-4">
            <span class="text-[11px] text-gray-500 font-medium">Total Net Payroll</span>
            <p class="text-xl font-bold text-[#2D2D2D] mt-1">₹{{ number_format($totalNet, 2) }}</p>
            <span class="text-[10px] text-gray-400 mt-1 block">{{ $totalCount }} records processed</span>
        </div>
        <div class="bg-white border border-gray-200 rounded p-4">
            <span class="text-[11px] text-gray-500 font-medium">Total Disbursed (Paid)</span>
            <p class="text-xl font-bold text-[#2D2D2D] mt-1">₹{{ number_format($totalPaid, 2) }}</p>
            <span class="text-[10px] text-gray-400 mt-1 block">{{ $paidCount }} of {{ $totalCount }} employees paid</span>
        </div>
        <div class="bg-white border border-gray-200 rounded p-4">
            <span class="text-[11px] text-gray-500 font-medium">Pending Disbursement</span>
            <p class="text-xl font-bold text-[#2D2D2D] mt-1">₹{{ number_format($totalPending, 2) }}</p>
            <span class="text-[10px] text-gray-400 mt-1 block">{{ $totalCount - $paidCount }} pending</span>
        </div>
        <div class="bg-white border border-gray-200 rounded p-4">
            <span class="text-[11px] text-gray-500 font-medium">Disbursement Progress</span>
            <div class="flex items-center gap-3 mt-2">
                <div class="flex-1 h-3 bg-gray-100 rounded overflow-hidden">
                    @php $pct = $totalCount > 0 ? round(($paidCount / $totalCount) * 100) : 0; @endphp
                    <div class="h-full bg-[#2D2D2D]" style="width: {{ $pct }}%"></div>
                </div>
                <span class="text-xs font-bold text-[#2D2D2D]">{{ $pct }}%</span>
            </div>
        </div>
    </div>

    {{-- Payroll Table --}}
    <div class="bg-white border border-gray-200 rounded overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50/50 text-gray-500 uppercase tracking-wider">
                        <th class="py-3 px-4 font-semibold w-24">Code</th>
                        <th class="py-3 px-4 font-semibold">Employee</th>
                        <th class="py-3 px-4 font-semibold">Department</th>
                        <th class="py-3 px-4 font-semibold">Basic</th>
                        <th class="py-3 px-4 font-semibold">Allowances (+)</th>
                        <th class="py-3 px-4 font-semibold">Deductions (-)</th>
                        <th class="py-3 px-4 font-semibold">Net Salary</th>
                        <th class="py-3 px-4 font-semibold w-24">Status</th>
                        <th class="py-3 px-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-[13px]">
                    @forelse($payrolls as $pr)
                    <tr class="hover:bg-gray-50/50">
                        <td class="py-3 px-4 font-bold text-[#2D2D2D]">{{ $pr->employee->employee_code }}</td>
                        <td class="py-3 px-4 font-medium text-[#2D2D2D]">{{ $pr->employee->full_name }}</td>
                        <td class="py-3 px-4 text-gray-600">{{ $pr->employee->department ? $pr->employee->department->name : '—' }}</td>
                        <td class="py-3 px-4 text-[#2D2D2D]">₹{{ number_format($pr->basic_salary, 2) }}</td>
                        <td class="py-3 px-4 text-gray-600">+₹{{ number_format($pr->allowances, 2) }}</td>
                        <td class="py-3 px-4 text-gray-600">-₹{{ number_format($pr->deductions, 2) }}</td>
                        <td class="py-3 px-4 font-bold text-[#2D2D2D]">₹{{ number_format($pr->net_salary, 2) }}</td>
                        <td class="py-3 px-4">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold tracking-wider uppercase {{ $pr->status === 'paid' ? 'bg-[#2D2D2D] text-white' : 'bg-gray-200 text-gray-700' }}">
                                {{ ucfirst($pr->status) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                @if($pr->status === 'pending')
                                <form action="{{ route('payroll.pay', $pr->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 bg-[#2D2D2D] text-white rounded text-xs hover:bg-[#1a1a1a]" title="Mark Paid">
                                        Pay
                                    </button>
                                </form>
                                @endif
                                <a href="{{ route('payroll.payslip', $pr->id) }}" target="_blank" class="px-2.5 py-1 border border-gray-300 rounded text-xs text-[#2D2D2D] hover:bg-gray-100 flex items-center gap-1" title="Print Payslip">
                                    <i class="fas fa-file-invoice"></i> Slip
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-8 text-center text-gray-400">
                            No payroll generated for {{ \Carbon\Carbon::parse($currentMonth . '-01')->format('F Y') }}.
                            <br>
                            Click <strong>"Generate Payroll"</strong> above to auto-create slips for all active employees.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payrolls->hasPages())
        <div class="p-4 border-t border-gray-200">
            {{ $payrolls->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
