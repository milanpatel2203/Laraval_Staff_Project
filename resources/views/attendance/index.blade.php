@extends('layouts.app')

@section('title', 'Attendance')
@section('page-title', 'Attendance')

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
            <h2 class="text-lg font-bold text-[#2D2D2D]">Attendance Log ({{ \Carbon\Carbon::parse($date)->format('d M Y') }})</h2>
            <p class="text-xs text-gray-500 mt-0.5">Track employee check-ins, leaves, and absences.</p>
        </div>
        <form method="GET" action="{{ route('attendance.index') }}" class="flex items-center gap-2">
            <input type="date" name="date" value="{{ $date }}" class="px-3 py-1.5 border border-gray-300 rounded text-xs text-[#2D2D2D]">
            <button type="submit" class="px-3 py-1.5 bg-[#2D2D2D] text-white rounded text-xs font-semibold hover:bg-[#1a1a1a]">Change Date</button>
        </form>
    </div>

    <div class="bg-white border border-gray-200 rounded overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50/50 text-gray-500 uppercase tracking-wider">
                        <th class="py-3 px-4 font-semibold w-24">Code</th>
                        <th class="py-3 px-4 font-semibold">Employee</th>
                        <th class="py-3 px-4 font-semibold">Department</th>
                        <th class="py-3 px-4 font-semibold">Clock In</th>
                        <th class="py-3 px-4 font-semibold">Status</th>
                        <th class="py-3 px-4 font-semibold text-right">Quick Mark</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-[13px]">
                    @forelse($employees as $emp)
                    @php $att = $attendances->get($emp->id); @endphp
                    <tr class="hover:bg-gray-50/50">
                        <td class="py-3 px-4 font-bold text-[#2D2D2D]">{{ $emp->employee_code }}</td>
                        <td class="py-3 px-4 font-medium text-[#2D2D2D]">{{ $emp->full_name }}</td>
                        <td class="py-3 px-4 text-[#2D2D2D]">{{ $emp->department ? $emp->department->name : '—' }}</td>
                        <td class="py-3 px-4 text-gray-600">{{ $att && $att->clock_in ? \Carbon\Carbon::parse($att->clock_in)->format('h:i A') : '—' }}</td>
                        <td class="py-3 px-4">
                            @if($att)
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold tracking-wider uppercase 
                                {{ $att->status === 'present' ? 'bg-[#2D2D2D] text-white' : ($att->status === 'on_leave' ? 'bg-gray-300 text-gray-800' : 'bg-gray-100 text-gray-600') }}">
                                {{ ucfirst(str_replace('_', ' ', $att->status)) }}
                            </span>
                            @else
                            <span class="text-xs text-gray-400">Not Marked</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right">
                            <form action="{{ route('attendance.mark') }}" method="POST" class="inline-flex gap-1">
                                @csrf
                                <input type="hidden" name="employee_id" value="{{ $emp->id }}">
                                <input type="hidden" name="date" value="{{ $date }}">
                                <button type="submit" name="status" value="present" class="px-2 py-1 border border-gray-300 rounded text-[11px] hover:bg-[#2D2D2D] hover:text-white" title="Mark Present">P</button>
                                <button type="submit" name="status" value="absent" class="px-2 py-1 border border-gray-300 rounded text-[11px] hover:bg-[#2D2D2D] hover:text-white" title="Mark Absent">A</button>
                                <button type="submit" name="status" value="half_day" class="px-2 py-1 border border-gray-300 rounded text-[11px] hover:bg-[#2D2D2D] hover:text-white" title="Mark Half-Day">HD</button>
                                <button type="submit" name="status" value="on_leave" class="px-2 py-1 border border-gray-300 rounded text-[11px] hover:bg-[#2D2D2D] hover:text-white" title="Mark On Leave">L</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-gray-400">No active employees found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
