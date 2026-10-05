@extends('layouts.app')

@section('title', 'Team Details')
@section('page-title', 'Team Details')

@section('content')
<div class="space-y-6">
    {{-- Alerts --}}
    @if(session('success'))
    <div class="bg-white border border-[#2D2D2D] text-[#2D2D2D] px-4 py-3 rounded flex items-center gap-2.5 text-sm font-medium">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-white border border-gray-400 text-[#2D2D2D] px-4 py-3 rounded flex items-center gap-2.5 text-sm font-medium">
        <i class="fas fa-exclamation-circle"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Back Button --}}
    <a href="{{ route('teams.index') }}" class="inline-flex items-center gap-2 text-xs font-medium text-gray-600 hover:text-[#2D2D2D]">
        <i class="fas fa-arrow-left"></i> Back to Teams
    </a>

    {{-- Team Details Card --}}
    <div class="bg-white border border-gray-200 rounded p-6">
        <div class="flex items-start justify-between mb-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <h2 class="text-xl font-bold text-[#2D2D2D]">{{ $team->name }}</h2>
                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold tracking-wider uppercase {{ $team->status === 'active' ? 'bg-[#2D2D2D] text-white' : 'bg-gray-200 text-gray-700' }}">
                        {{ ucfirst($team->status) }}
                    </span>
                </div>
                <p class="text-sm text-gray-500">Code: {{ $team->code }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('teams.edit', $team->id) }}" class="px-3 py-2 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-100 inline-flex items-center gap-1.5">
                    <i class="fas fa-edit"></i> Edit Team
                </a>
            </div>
        </div>

        @if($team->description)
        <div class="mb-6">
            <h3 class="text-xs font-semibold text-[#2D2D2D] mb-2">Description</h3>
            <p class="text-sm text-gray-600">{{ $team->description }}</p>
        </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-xs font-semibold text-[#2D2D2D] mb-2">Department</h3>
                <p class="text-sm text-gray-600">{{ $team->department ? $team->department->name : 'Unassigned' }}</p>
            </div>
            <div>
                <h3 class="text-xs font-semibold text-[#2D2D2D] mb-2">Team Leader</h3>
                <p class="text-sm text-gray-600">{{ $team->teamLeader ? $team->teamLeader->full_name : 'Not assigned' }}</p>
            </div>
        </div>
    </div>

    {{-- Team Members Section --}}
    <div class="bg-white border border-gray-200 rounded p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-bold text-[#2D2D2D]">Team Members ({{ $team->employees->count() }})</h3>
                <p class="text-xs text-gray-500 mt-0.5">Manage team member assignments</p>
            </div>
            <button onclick="toggleAssignModal()" class="bg-[#2D2D2D] hover:bg-[#1a1a1a] text-white text-xs font-semibold px-4 py-2 rounded inline-flex items-center gap-2">
                <i class="fas fa-user-plus"></i> Assign Employee
            </button>
        </div>

        {{-- Members Table --}}
        <div class="border border-gray-200 rounded overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50/50 text-gray-500 uppercase tracking-wider">
                            <th class="py-3 px-4 font-semibold w-24">Code</th>
                            <th class="py-3 px-4 font-semibold">Employee</th>
                            <th class="py-3 px-4 font-semibold">Department</th>
                            <th class="py-3 px-4 font-semibold">Designation</th>
                            <th class="py-3 px-4 font-semibold w-24">Status</th>
                            <th class="py-3 px-4 font-semibold w-24 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-[13px]">
                        @forelse($team->employees as $employee)
                        <tr class="hover:bg-gray-50/50">
                            <td class="py-3 px-4 font-bold text-[#2D2D2D]">{{ $employee->employee_code }}</td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-[#2D2D2D] text-[#F5F5F5] flex items-center justify-center text-xs shrink-0">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-[#2D2D2D]">{{ $employee->full_name }}</span>
                                        @if($team->team_leader_id === $employee->id)
                                        <span class="text-[10px] text-[#2D2D2D] font-semibold">Team Leader</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-[#2D2D2D]">{{ $employee->department ? $employee->department->name : 'Unassigned' }}</td>
                            <td class="py-3 px-4 text-[#2D2D2D]">{{ $employee->designation }}</td>
                            <td class="py-3 px-4">
                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold tracking-wider uppercase {{ $employee->status === 'active' ? 'bg-[#2D2D2D] text-white' : 'bg-gray-200 text-gray-700' }}">
                                    {{ ucfirst($employee->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                @if($team->team_leader_id !== $employee->id)
                                <form action="{{ route('teams.remove-employee', [$team->id, $employee->id]) }}" method="POST" class="inline" onsubmit="return confirm('Remove this employee from the team?');">
                                    @csrf
                                    <button type="submit" class="w-7 h-7 rounded border border-gray-300 bg-white text-gray-500 hover:bg-red-50 hover:text-red-600 hover:border-red-300 flex items-center justify-center text-xs" title="Remove from team">
                                        <i class="fas fa-user-minus"></i>
                                    </button>
                                </form>
                                @else
                                <span class="text-[10px] text-gray-400 italic">Leader</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-gray-400">No team members assigned yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Assign Employee Modal --}}
<div id="assignModal" class="fixed inset-0 bg-[#2D2D2D]/60 flex items-center justify-center z-50 p-4" style="display: none;">
    <div class="bg-white rounded border border-[#2D2D2D] w-full max-w-md overflow-hidden shadow-lg">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200">
            <h3 class="text-sm font-bold text-[#2D2D2D]">Assign Employee to Team</h3>
            <button onclick="toggleAssignModal()" class="text-gray-400 hover:text-[#2D2D2D] text-lg font-bold">&times;</button>
        </div>
        <form action="{{ route('teams.assign-employee', $team->id) }}" method="POST">
            @csrf
            <div class="p-5 space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-[#2D2D2D] mb-1">Select Employee *</label>
                    <select name="employee_id" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                        <option value="">Choose an employee...</option>
                        @php
                            $availableEmployees = \App\Models\Employee::whereNull('team_id')
                                ->where('status', 'active')
                                ->orderBy('first_name')
                                ->get();
                        @endphp
                        @foreach($availableEmployees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->employee_code }})</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-[10px] text-gray-500">Only showing active employees not assigned to any team.</p>
                </div>
            </div>
            <div class="px-5 py-3 bg-gray-50 border-t border-gray-200 flex justify-end gap-2 text-xs">
                <button type="button" onclick="toggleAssignModal()" class="px-4 py-2 border border-gray-300 rounded font-medium text-gray-700 bg-white hover:bg-gray-100">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-[#2D2D2D] text-white rounded font-semibold hover:bg-[#1a1a1a]">Assign Employee</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function toggleAssignModal() {
    const modal = document.getElementById('assignModal');
    modal.style.display = modal.style.display === 'none' ? 'flex' : 'none';
}
</script>
@endpush
@endsection
