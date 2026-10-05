@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Flash Notifications --}}
    @if(session('success'))
    <div class="bg-white border border-[#2D2D2D] text-[#2D2D2D] px-4 py-3 rounded flex items-center justify-between text-sm font-medium">
        <div class="flex items-center gap-2.5">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-xs text-gray-500 hover:text-black">&times;</button>
    </div>
    @endif

    {{-- Stats Cards Row (Dynamic & Clickable) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <a href="{{ route('employees.index') }}" class="bg-white border border-gray-200 rounded p-4 flex items-start gap-3.5 hover:border-[#2D2D2D] transition-none group">
            <div class="w-10 h-10 rounded bg-[#2D2D2D] text-[#F5F5F5] flex items-center justify-center text-base shrink-0">
                <i class="fas fa-users"></i>
            </div>
            <div class="flex flex-col">
                <span class="text-xl font-bold leading-tight text-[#2D2D2D]">{{ $totalEmployees }}</span>
                <span class="text-[11px] text-gray-500 font-medium mt-0.5 group-hover:text-[#2D2D2D]">Total Employees</span>
            </div>
        </a>

        <a href="{{ route('departments.index') }}" class="bg-white border border-gray-200 rounded p-4 flex items-start gap-3.5 hover:border-[#2D2D2D] transition-none group">
            <div class="w-10 h-10 rounded bg-[#2D2D2D] text-[#F5F5F5] flex items-center justify-center text-base shrink-0">
                <i class="fas fa-sitemap"></i>
            </div>
            <div class="flex flex-col">
                <span class="text-xl font-bold leading-tight text-[#2D2D2D]">{{ $totalDepartments }}</span>
                <span class="text-[11px] text-gray-500 font-medium mt-0.5 group-hover:text-[#2D2D2D]">Departments</span>
            </div>
        </a>

        <a href="{{ route('attendance.index') }}" class="bg-white border border-gray-200 rounded p-4 flex items-start gap-3.5 hover:border-[#2D2D2D] transition-none group">
            <div class="w-10 h-10 rounded bg-[#2D2D2D] text-[#F5F5F5] flex items-center justify-center text-base shrink-0">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="flex flex-col">
                <span class="text-xl font-bold leading-tight text-[#2D2D2D]">{{ $presentToday }}</span>
                <span class="text-[11px] text-gray-500 font-medium mt-0.5 group-hover:text-[#2D2D2D]">Present Today</span>
            </div>
        </a>

        <a href="{{ route('leaves.index', ['status' => 'pending']) }}" class="bg-white border border-gray-200 rounded p-4 flex items-start gap-3.5 hover:border-[#2D2D2D] transition-none group">
            <div class="w-10 h-10 rounded bg-[#2D2D2D] text-[#F5F5F5] flex items-center justify-center text-base shrink-0">
                <i class="fas fa-calendar-minus"></i>
            </div>
            <div class="flex flex-col">
                <span class="text-xl font-bold leading-tight text-[#2D2D2D]">{{ $pendingLeavesCount }}</span>
                <span class="text-[11px] text-gray-500 font-medium mt-0.5 group-hover:text-[#2D2D2D]">Pending Leaves</span>
            </div>
        </a>

        <a href="{{ route('employees.index', ['filter' => 'new_hires']) }}" class="bg-white border border-gray-200 rounded p-4 flex items-start gap-3.5 hover:border-[#2D2D2D] transition-none group" title="View New Hires">
            <div class="w-10 h-10 rounded bg-[#2D2D2D] text-[#F5F5F5] flex items-center justify-center text-base shrink-0">
                <i class="fas fa-user-plus"></i>
            </div>
            <div class="flex flex-col">
                <span class="text-xl font-bold leading-tight text-[#2D2D2D]">{{ $newHires }}</span>
                <span class="text-[11px] text-gray-500 font-medium mt-0.5 group-hover:text-[#2D2D2D]">New Hires (30d)</span>
            </div>
        </a>

        <a href="{{ route('payroll.index') }}" class="bg-white border border-gray-200 rounded p-4 flex items-start gap-3.5 hover:border-[#2D2D2D] transition-none group">
            <div class="w-10 h-10 rounded bg-[#2D2D2D] text-[#F5F5F5] flex items-center justify-center text-base shrink-0">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="flex flex-col">
                <span class="text-xl font-bold leading-tight text-[#2D2D2D]">₹{{ $formattedPayroll }}</span>
                <span class="text-[11px] text-gray-500 font-medium mt-0.5 group-hover:text-[#2D2D2D]">Payroll (Month)</span>
            </div>
        </a>
    </div>

    {{-- Main Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Dynamic Today's Attendance Widget --}}
        <div class="bg-white border border-gray-200 rounded overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200">
                <div class="flex items-center gap-2">
                    <h2 class="text-sm font-semibold text-[#2D2D2D]">Today's Attendance</h2>
                    <span class="text-[11px] px-2 py-0.5 bg-gray-100 rounded text-gray-600 font-medium">{{ now()->format('d M Y') }}</span>
                </div>
                <a href="{{ route('attendance.index') }}" class="text-xs font-medium text-[#2D2D2D] border-b border-[#2D2D2D]">Manage</a>
            </div>
            <div class="p-5">
                <div class="mb-5">
                    <div class="flex h-7 rounded overflow-hidden mb-3 bg-gray-100">
                        @if($presentPercentage > 0)
                        <div class="bg-[#2D2D2D] text-white flex items-center justify-center text-[11px] font-semibold" style="width: {{ $presentPercentage }}%">
                            {{ $presentPercentage }}%
                        </div>
                        @endif
                        @if($absentPercentage > 0)
                        <div class="bg-gray-500 text-white flex items-center justify-center text-[11px] font-semibold" style="width: {{ $absentPercentage }}%">
                            {{ $absentPercentage }}%
                        </div>
                        @endif
                        @if($leavePercentage > 0)
                        <div class="bg-gray-300 text-[#2D2D2D] flex items-center justify-center text-[11px] font-semibold" style="width: {{ $leavePercentage }}%">
                            {{ $leavePercentage }}%
                        </div>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-4 text-xs text-gray-600">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-sm bg-[#2D2D2D]"></span>
                            <span>Present ({{ $presentToday }})</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-sm bg-gray-500"></span>
                            <span>Absent ({{ $absentToday }})</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-sm bg-gray-300"></span>
                            <span>On Leave ({{ $onLeaveToday }})</span>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-500 uppercase tracking-wider">
                                <th class="py-2.5 px-3 font-semibold">Department</th>
                                <th class="py-2.5 px-3 font-semibold">Total</th>
                                <th class="py-2.5 px-3 font-semibold">Present</th>
                                <th class="py-2.5 px-3 font-semibold">Absent</th>
                                <th class="py-2.5 px-3 font-semibold">Leave</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-[13px]">
                            @forelse($departmentAttendance as $dept)
                            <tr>
                                <td class="py-2.5 px-3 font-medium text-[#2D2D2D]">{{ $dept['name'] }}</td>
                                <td class="py-2.5 px-3 text-gray-600">{{ $dept['total'] }}</td>
                                <td class="py-2.5 px-3 text-[#2D2D2D] font-medium">{{ $dept['present'] }}</td>
                                <td class="py-2.5 px-3 text-gray-600">{{ $dept['absent'] }}</td>
                                <td class="py-2.5 px-3 text-gray-600">{{ $dept['leave'] }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-gray-400">No departments configured</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Workable Pending Leave Requests Widget --}}
        <div class="bg-white border border-gray-200 rounded overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200">
                <div class="flex items-center gap-2">
                    <h2 class="text-sm font-semibold text-[#2D2D2D]">Pending Leave Requests</h2>
                    <span class="text-[11px] px-2 py-0.5 bg-[#2D2D2D] text-white rounded font-bold">{{ $pendingLeavesCount }}</span>
                </div>
                <a href="{{ route('leaves.index') }}" class="text-xs font-medium text-[#2D2D2D] border-b border-[#2D2D2D]">View All</a>
            </div>
            <div class="p-5 divide-y divide-gray-100">
                @forelse($pendingLeaves as $leave)
                <div class="py-3 first:pt-0 last:pb-0 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-[#2D2D2D] text-[#F5F5F5] flex items-center justify-center text-xs shrink-0 font-bold">
                            {{ substr($leave->employee->first_name, 0, 1) }}{{ substr($leave->employee->last_name, 0, 1) }}
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[13px] font-semibold text-[#2D2D2D]">{{ $leave->employee->full_name }}</span>
                            <span class="text-[11px] text-gray-500">{{ $leave->type }} &middot; {{ $leave->days }} {{ Str::plural('day', $leave->days) }}</span>
                        </div>
                    </div>
                    <span class="text-xs text-gray-500 hidden sm:inline">{{ $leave->from_date->format('d M') }} - {{ $leave->to_date->format('d M') }}</span>
                    <div class="flex gap-1.5 shrink-0">
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
                </div>
                @empty
                <div class="py-8 text-center text-gray-400 text-xs">
                    <i class="fas fa-check-circle text-lg mb-1 block"></i>
                    No pending leave requests at this time.
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Bottom Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Dynamic Upcoming Holidays --}}
        <div class="bg-white border border-gray-200 rounded overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200">
                <h2 class="text-sm font-semibold text-[#2D2D2D]">Upcoming Holidays</h2>
                <a href="{{ route('holidays.index') }}" class="text-xs font-medium text-[#2D2D2D] border-b border-[#2D2D2D]">View Calendar</a>
            </div>
            <div class="p-5 divide-y divide-gray-100">
                @forelse($upcomingHolidays as $holiday)
                <div class="py-3 first:pt-0 last:pb-0 flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded bg-[#2D2D2D] text-[#F5F5F5] flex flex-col items-center justify-center shrink-0">
                        <span class="text-base font-bold leading-tight">{{ $holiday->date->format('d') }}</span>
                        <span class="text-[10px] uppercase font-semibold">{{ $holiday->date->format('M') }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[13px] font-semibold text-[#2D2D2D]">{{ $holiday->name }}</span>
                        <span class="text-[11px] text-gray-500">{{ $holiday->date->format('l') }} &middot; {{ $holiday->type }}</span>
                    </div>
                </div>
                @empty
                <div class="py-6 text-center text-gray-400 text-xs">No upcoming holidays scheduled.</div>
                @endforelse
            </div>
        </div>

        {{-- Dynamic Recent Activity --}}
        <div class="bg-white border border-gray-200 rounded overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200">
                <h2 class="text-sm font-semibold text-[#2D2D2D]">Recent Activity</h2>
                <span class="text-xs text-gray-500">Live Audit Trail</span>
            </div>
            <div class="p-5 divide-y divide-gray-100">
                @forelse($recentActivities as $activity)
                <div class="py-3 first:pt-0 last:pb-0 flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-gray-100 border border-gray-200 text-[#2D2D2D] flex items-center justify-center text-xs shrink-0 mt-0.5">
                        <i class="fas fa-{{ $activity->icon ?: 'info-circle' }}"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[13px] text-[#2D2D2D] leading-relaxed font-medium">{{ $activity->title }}</span>
                        @if($activity->description)
                        <span class="text-xs text-gray-500">{{ $activity->description }}</span>
                        @endif
                        <span class="text-[10px] text-gray-400 mt-0.5">{{ $activity->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                @empty
                <div class="py-6 text-center text-gray-400 text-xs">No recent activities logged.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
