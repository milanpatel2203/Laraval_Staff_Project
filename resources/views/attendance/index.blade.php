@use('Carbon\Carbon')
@extends('layouts.app')

@section('title', $canMarkAttendance ? 'Attendance' : 'My Attendance')
@section('page-title', $canMarkAttendance ? 'Attendance' : 'My Attendance')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-[#2D2D2D]">
                {{ $canMarkAttendance ? 'Attendance Log' : 'My Attendance Log' }} ({{ Carbon::parse($date)->format('d M Y') }})
            </h2>
            <p class="text-xs text-gray-500 mt-0.5">
                {{ $canMarkAttendance ? 'Track employee check-ins, leaves, and absences across the organization.' : 'Check your daily attendance records and punch clock.' }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('attendance.export', ['date' => $date]) }}" class="border border-gray-300 bg-white hover:bg-gray-50 text-[#2D2D2D] text-xs font-semibold px-3 py-1.5 rounded inline-flex items-center gap-1.5 shadow-sm transition">
                <i class="fas fa-file-excel text-emerald-600"></i> Export Excel
            </a>
            <form method="GET" action="{{ route('attendance.index') }}" class="flex items-center gap-2">
                <input type="date" name="date" value="{{ $date }}" class="px-3 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D]">
                <button type="submit" class="px-3 py-1.5 bg-[#2D2D2D] text-white rounded text-xs font-semibold hover:bg-[#1a1a1a]">Change Date</button>
            </form>
        </div>
    </div>

    {{-- Attendance Table using Common Component --}}
    <x-table 
        :headers="[
            ['label' => 'Code', 'class' => 'w-24'],
            ['label' => 'Employee'],
            ['label' => 'Department'],
            ['label' => 'Clock In'],
            ['label' => 'Status'],
            ['label' => $canMarkAttendance ? 'Quick Mark' : 'Action', 'align' => 'right']
        ]"
        :empty="$employees->isEmpty()" 
        emptyMessage="No attendance records found for this date.">
        @foreach($employees as $emp)
        @php $att = $attendances->get($emp->id); @endphp
        <tr class="hover:bg-gray-50/50">
            <td class="py-3 px-4 font-bold text-[#2D2D2D]">{{ $emp->employee_code }}</td>
            <td class="py-3 px-4 font-medium text-[#2D2D2D]">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full overflow-hidden bg-[#2D2D2D] text-white flex items-center justify-center text-[10px] font-bold shrink-0 border border-gray-200">
                        @if($emp->avatar_url)
                            <img src="{{ $emp->avatar_url }}" alt="{{ $emp->full_name }}" class="w-full h-full object-cover">
                        @else
                            <span>{{ $emp->initials }}</span>
                        @endif
                    </div>
                    <span>{{ $emp->full_name }}</span>
                </div>
            </td>
            <td class="py-3 px-4 text-[#2D2D2D]">{{ $emp->department ? $emp->department->name : '—' }}</td>
            <td class="py-3 px-4 text-gray-600">{{ $att && $att->clock_in ? Carbon::parse($att->clock_in)->format('h:i A') : '—' }}</td>
            <td class="py-3 px-4">
                @if($att)
                <x-badge :variant="$att->status">
                    {{ ucfirst(str_replace('_', ' ', $att->status)) }}
                </x-badge>
                @else
                <span class="text-xs text-gray-400">Not Marked</span>
                @endif
            </td>
            <td class="py-3 px-4 text-right">
                @if($canMarkAttendance)
                <form action="{{ route('attendance.mark') }}" method="POST" class="inline-flex gap-1">
                    @csrf
                    <input type="hidden" name="employee_id" value="{{ $emp->id }}">
                    <input type="hidden" name="date" value="{{ $date }}">
                    <button type="submit" name="status" value="present" class="px-2 py-1 border border-gray-300 rounded text-[11px] hover:bg-[#2D2D2D] hover:text-white" title="Mark Present">P</button>
                    <button type="submit" name="status" value="absent" class="px-2 py-1 border border-gray-300 rounded text-[11px] hover:bg-[#2D2D2D] hover:text-white" title="Mark Absent">A</button>
                    <button type="submit" name="status" value="half_day" class="px-2 py-1 border border-gray-300 rounded text-[11px] hover:bg-[#2D2D2D] hover:text-white" title="Mark Half-Day">HD</button>
                    <button type="submit" name="status" value="on_leave" class="px-2 py-1 border border-gray-300 rounded text-[11px] hover:bg-[#2D2D2D] hover:text-white" title="Mark On Leave">L</button>
                </form>
                @elseif(auth()->user()->linked_employee && auth()->user()->linked_employee->id === $emp->id)
                    @if(!$att || $att->status !== 'present')
                    <form action="{{ route('attendance.mark') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="employee_id" value="{{ $emp->id }}">
                        <input type="hidden" name="date" value="{{ $date }}">
                        <button type="submit" name="status" value="present" class="px-2.5 py-1 bg-[#2D2D2D] text-white rounded text-xs hover:bg-[#1a1a1a]" title="Punch In Present">
                            <i class="fas fa-sign-in-alt mr-1"></i> Punch In
                        </button>
                    </form>
                    @else
                    <span class="text-xs text-emerald-600 font-semibold flex items-center justify-end gap-1">
                        <i class="fas fa-check-circle"></i> Clocked In
                    </span>
                    @endif
                @else
                    <span class="text-xs text-gray-400">—</span>
                @endif
            </td>
        </tr>
        @endforeach
    </x-table>
</div>
@endsection
