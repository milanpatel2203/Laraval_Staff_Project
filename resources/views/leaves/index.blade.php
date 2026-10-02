@extends('layouts.app')

@section('title', 'Leave Management')
@section('page-title', 'Leaves')

@section('content')
<div class="space-y-6">
    @if(session('success'))
    <div class="bg-white border border-[#2D2D2D] text-[#2D2D2D] px-4 py-3 rounded flex items-center gap-2.5 text-sm font-medium">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-[#2D2D2D]">Leave Requests ({{ $leaves->total() }})</h2>
            <p class="text-xs text-gray-500 mt-0.5">Review, approve, or reject staff leave applications.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('leaves.index') }}" class="px-3 py-1.5 text-xs rounded border {{ !request('status') ? 'bg-[#2D2D2D] text-white border-[#2D2D2D]' : 'bg-white text-gray-700 border-gray-300' }}">All</a>
            <a href="{{ route('leaves.index', ['status' => 'pending']) }}" class="px-3 py-1.5 text-xs rounded border {{ request('status') === 'pending' ? 'bg-[#2D2D2D] text-white border-[#2D2D2D]' : 'bg-white text-gray-700 border-gray-300' }}">Pending</a>
            <a href="{{ route('leaves.index', ['status' => 'approved']) }}" class="px-3 py-1.5 text-xs rounded border {{ request('status') === 'approved' ? 'bg-[#2D2D2D] text-white border-[#2D2D2D]' : 'bg-white text-gray-700 border-gray-300' }}">Approved</a>
            <a href="{{ route('leaves.index', ['status' => 'rejected']) }}" class="px-3 py-1.5 text-xs rounded border {{ request('status') === 'rejected' ? 'bg-[#2D2D2D] text-white border-[#2D2D2D]' : 'bg-white text-gray-700 border-gray-300' }}">Rejected</a>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50/50 text-gray-500 uppercase tracking-wider">
                        <th class="py-3 px-4 font-semibold">Employee</th>
                        <th class="py-3 px-4 font-semibold">Type</th>
                        <th class="py-3 px-4 font-semibold">Duration</th>
                        <th class="py-3 px-4 font-semibold">Days</th>
                        <th class="py-3 px-4 font-semibold">Reason</th>
                        <th class="py-3 px-4 font-semibold">Status</th>
                        <th class="py-3 px-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-[13px]">
                    @forelse($leaves as $leave)
                    <tr class="hover:bg-gray-50/50">
                        <td class="py-3 px-4 font-medium text-[#2D2D2D]">{{ $leave->employee->full_name }}</td>
                        <td class="py-3 px-4 text-[#2D2D2D]">{{ $leave->type }}</td>
                        <td class="py-3 px-4 text-gray-600">{{ $leave->from_date->format('d M') }} - {{ $leave->to_date->format('d M Y') }}</td>
                        <td class="py-3 px-4 text-[#2D2D2D]">{{ $leave->days }} days</td>
                        <td class="py-3 px-4 text-gray-600">{{ $leave->reason ?: '—' }}</td>
                        <td class="py-3 px-4">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold tracking-wider uppercase 
                                {{ $leave->status === 'approved' ? 'bg-[#2D2D2D] text-white' : ($leave->status === 'pending' ? 'bg-gray-200 text-gray-800' : 'bg-gray-100 text-gray-500 line-through') }}">
                                {{ ucfirst($leave->status) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            @if($leave->status === 'pending')
                            <div class="inline-flex items-center gap-1.5">
                                <form action="{{ route('leaves.approve', $leave->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="w-7 h-7 rounded bg-[#2D2D2D] text-white flex items-center justify-center text-xs hover:bg-[#1a1a1a]" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                <form action="{{ route('leaves.reject', $leave->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="w-7 h-7 rounded bg-white text-[#2D2D2D] border border-gray-300 flex items-center justify-center text-xs hover:bg-gray-100" title="Reject">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            </div>
                            @else
                            <span class="text-xs text-gray-400">Processed</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-6 text-center text-gray-400">No leave requests found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leaves->hasPages())
        <div class="p-4 border-t border-gray-200">
            {{ $leaves->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
