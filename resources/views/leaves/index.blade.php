@extends('layouts.app')

@section('title', $canApproveLeaves ? 'Leave Management' : 'My Leaves')
@section('page-title', $canApproveLeaves ? 'Leave Management' : 'My Leaves')

@section('content')
<div class="space-y-6">

    {{-- Top Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-[#2D2D2D]">
                {{ $canApproveLeaves ? 'Leave Requests' : 'My Leave Applications' }} ({{ $leaves->total() }})
            </h2>
            <p class="text-xs text-gray-500 mt-0.5">
                {{ $canApproveLeaves ? 'Review, approve, or reject staff leave applications across departments.' : 'Track your submitted leave applications and request new leave.' }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex gap-1.5 bg-gray-100 p-1 rounded border border-gray-200">
                <a href="{{ route('leaves.index') }}" class="px-2.5 py-1 text-xs rounded font-medium {{ !request('status') ? 'bg-[#2D2D2D] text-white shadow-sm' : 'text-gray-600 hover:text-[#2D2D2D]' }}">All</a>
                <a href="{{ route('leaves.index', ['status' => 'pending']) }}" class="px-2.5 py-1 text-xs rounded font-medium {{ request('status') === 'pending' ? 'bg-[#2D2D2D] text-white shadow-sm' : 'text-gray-600 hover:text-[#2D2D2D]' }}">Pending</a>
                <a href="{{ route('leaves.index', ['status' => 'approved']) }}" class="px-2.5 py-1 text-xs rounded font-medium {{ request('status') === 'approved' ? 'bg-[#2D2D2D] text-white shadow-sm' : 'text-gray-600 hover:text-[#2D2D2D]' }}">Approved</a>
                <a href="{{ route('leaves.index', ['status' => 'rejected']) }}" class="px-2.5 py-1 text-xs rounded font-medium {{ request('status') === 'rejected' ? 'bg-[#2D2D2D] text-white shadow-sm' : 'text-gray-600 hover:text-[#2D2D2D]' }}">Rejected</a>
            </div>

            <a href="{{ route('leaves.export', request()->query()) }}" class="border border-gray-300 bg-white hover:bg-gray-50 text-[#2D2D2D] text-xs font-semibold px-3 py-1.5 rounded inline-flex items-center gap-1.5 shadow-sm transition">
                <i class="fas fa-file-excel text-emerald-600"></i> Export Excel
            </a>

            @if($canApplyLeave)
            <button type="button" onclick="openApplyLeaveModal()" class="px-3.5 py-2 bg-[#2D2D2D] text-white rounded text-xs font-semibold hover:bg-black flex items-center gap-1.5 shadow-sm">
                <i class="fas fa-plus"></i> Apply for Leave
            </button>
            @endif
        </div>
    </div>

    {{-- Leaves Table using Common Component --}}
    <x-table 
        :headers="[
            ['label' => 'Employee'],
            ['label' => 'Type'],
            ['label' => 'Duration'],
            ['label' => 'Days'],
            ['label' => 'Reason'],
            ['label' => 'Status'],
            ['label' => 'Actions', 'align' => 'right']
        ]"
        :pagination="$leaves"
        :empty="$leaves->isEmpty()" 
        emptyMessage="No leave requests found.">
        @foreach($leaves as $leave)
        <tr class="hover:bg-gray-50/50">
            <td class="py-3 px-4 font-medium text-[#2D2D2D]">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full overflow-hidden bg-[#2D2D2D] text-white flex items-center justify-center text-[10px] font-bold shrink-0 border border-gray-200">
                        @if($leave->employee && $leave->employee->avatar_url)
                            <img src="{{ $leave->employee->avatar_url }}" alt="{{ $leave->employee->full_name }}" class="w-full h-full object-cover">
                        @else
                            <span>{{ $leave->employee ? $leave->employee->initials : 'U' }}</span>
                        @endif
                    </div>
                    <div>
                        <span class="font-semibold block text-xs">{{ $leave->employee ? $leave->employee->full_name : 'Unknown' }}</span>
                        @if($canApproveLeaves && $leave->employee && $leave->employee->department)
                        <span class="text-[10px] text-gray-500 font-mono">{{ $leave->employee->department->name }}</span>
                        @endif
                    </div>
                </div>
            </td>
            <td class="py-3 px-4 text-[#2D2D2D] font-medium text-xs">{{ $leave->type }}</td>
            <td class="py-3 px-4 text-gray-600 text-xs">{{ $leave->from_date->format('d M') }} - {{ $leave->to_date->format('d M Y') }}</td>
            <td class="py-3 px-4 text-[#2D2D2D] font-medium text-xs">{{ $leave->days }} {{ Str::plural('day', $leave->days) }}</td>
            <td class="py-3 px-4 text-gray-600 text-xs max-w-xs truncate" title="{{ $leave->reason }}">{{ $leave->reason ?: '—' }}</td>
            <td class="py-3 px-4">
                <x-badge :variant="$leave->status">{{ ucfirst($leave->status) }}</x-badge>
            </td>
            <td class="py-3 px-4 text-right">
                @if($leave->status === 'pending')
                    @if($canApproveLeaves)
                    <div class="inline-flex items-center gap-1.5">
                        <form action="{{ route('leaves.approve', $leave->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="w-7 h-7 rounded bg-[#2D2D2D] text-white flex items-center justify-center text-xs hover:bg-[#1a1a1a]" title="Approve Leave">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                        <form action="{{ route('leaves.reject', $leave->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="w-7 h-7 rounded bg-white text-[#2D2D2D] border border-gray-300 flex items-center justify-center text-xs hover:bg-gray-100" title="Reject Leave">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                    </div>
                    @else
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                        <i class="fas fa-clock text-[9px]"></i> Pending Review
                    </span>
                    @endif
                @elseif($leave->status === 'approved')
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <i class="fas fa-check-circle text-[9px]"></i> Approved
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-red-50 text-red-700 border border-red-200">
                        <i class="fas fa-times-circle text-[9px]"></i> Declined
                    </span>
                @endif
            </td>
        </tr>
        @endforeach
    </x-table>
</div>

@if($canApplyLeave)
{{-- Apply for Leave Modal --}}
<div id="applyLeaveModal" class="fixed inset-0 bg-[#2D2D2D]/60 flex items-center justify-center z-50 p-4" style="display: none;">
    <div class="bg-white rounded max-w-md w-full p-6 shadow-xl border border-gray-200">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded bg-gray-100 text-[#2D2D2D] flex items-center justify-center text-sm">
                    <i class="fas fa-calendar-plus"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-[#2D2D2D]">Apply for Leave</h3>
                    <p class="text-[11px] text-gray-500">Submit a leave request for manager authorization</p>
                </div>
            </div>
            <button type="button" onclick="closeApplyLeaveModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form action="{{ route('leaves.store') }}" method="POST" class="mt-4 space-y-4">
            @csrf

            @if($linkedEmployee)
            <div class="p-3 bg-gray-50 rounded border border-gray-200 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-[#2D2D2D] block">{{ $linkedEmployee->full_name }}</span>
                    <span class="text-[11px] text-gray-500 font-mono">{{ $linkedEmployee->employee_code }} • {{ $linkedEmployee->department ? $linkedEmployee->department->name : 'Staff' }}</span>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-200 text-gray-700">Applicant</span>
            </div>
            @endif

            <div>
                <label class="block font-semibold text-xs text-[#2D2D2D] mb-1">Leave Type *</label>
                <select name="type" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D] bg-white">
                    <option value="Casual Leave">Casual Leave</option>
                    <option value="Sick Leave">Sick Leave</option>
                    <option value="Annual Leave">Annual Leave</option>
                    <option value="Earned Leave">Earned Leave</option>
                    <option value="Unpaid Leave">Unpaid Leave</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-xs text-[#2D2D2D] mb-1">From Date *</label>
                    <input type="date" name="from_date" id="leave_from_date" required min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                </div>
                <div>
                    <label class="block font-semibold text-xs text-[#2D2D2D] mb-1">To Date *</label>
                    <input type="date" name="to_date" id="leave_to_date" required min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-xs text-[#2D2D2D] mb-1">Reason for Leave *</label>
                <textarea name="reason" rows="3" required placeholder="Describe reason for leave..." class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                <button type="button" onclick="closeApplyLeaveModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded text-xs font-semibold hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-[#2D2D2D] text-white rounded text-xs font-semibold hover:bg-[#1a1a1a] flex items-center gap-1.5">
                    <i class="fas fa-paper-plane"></i> Submit Application
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openApplyLeaveModal() {
        document.getElementById('applyLeaveModal').style.display = 'flex';
    }
    function closeApplyLeaveModal() {
        document.getElementById('applyLeaveModal').style.display = 'none';
    }
</script>
@endpush
@endif
@endsection
