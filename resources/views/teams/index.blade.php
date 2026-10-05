@extends('layouts.app')

@section('title', 'Teams')
@section('page-title', 'Teams')

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

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-[#2D2D2D]">Teams ({{ $teams->count() }})</h2>
            <p class="text-xs text-gray-500 mt-0.5">Manage organization teams and team member assignments.</p>
        </div>
        <a href="{{ route('teams.create') }}" class="bg-[#2D2D2D] hover:bg-[#1a1a1a] text-white text-xs font-semibold px-4 py-2 rounded inline-flex items-center gap-2">
            <i class="fas fa-plus"></i> Create Team
        </a>
    </div>

    {{-- Filter Form --}}
    <div class="bg-white border border-gray-200 rounded p-4">
        <form action="{{ route('teams.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="w-full sm:flex-1">
                <input type="text" name="search" value="{{ request('search') }}" class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" placeholder="Search by name, code or description...">
            </div>
            <div class="w-full sm:w-48">
                <select name="department_id" class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full sm:w-36">
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="submit" class="px-4 py-2 bg-[#2D2D2D] text-white rounded text-xs font-semibold hover:bg-[#1a1a1a] flex items-center gap-1.5">
                    <i class="fas fa-search"></i> Filter
                </button>
                @if(request()->hasAny(['search', 'department_id', 'status']))
                <a href="{{ route('teams.index') }}" class="px-3 py-2 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-100">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Teams Table --}}
    <div class="bg-white border border-gray-200 rounded overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50/50 text-gray-500 uppercase tracking-wider">
                        <th class="py-3 px-4 font-semibold w-24">Code</th>
                        <th class="py-3 px-4 font-semibold">Team Name</th>
                        <th class="py-3 px-4 font-semibold">Department</th>
                        <th class="py-3 px-4 font-semibold">Team Lead</th>
                        <th class="py-3 px-4 font-semibold w-24">Members</th>
                        <th class="py-3 px-4 font-semibold w-24">Status</th>
                        <th class="py-3 px-4 font-semibold w-28 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-[13px]">
                    @forelse($teams as $team)
                    <tr class="hover:bg-gray-50/50">
                        <td class="py-3 px-4 font-bold text-[#2D2D2D]">{{ $team->code }}</td>
                        <td class="py-3 px-4">
                            <div class="flex flex-col">
                                <span class="font-semibold text-[#2D2D2D]">{{ $team->name }}</span>
                                @if($team->description)
                                <span class="text-[11px] text-gray-500 truncate max-w-xs">{{ $team->description }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="py-3 px-4 text-[#2D2D2D]">{{ $team->department ? $team->department->name : 'Unassigned' }}</td>
                        <td class="py-3 px-4 text-[#2D2D2D]">{{ $team->teamLeader ? $team->teamLeader->full_name : 'Not assigned' }}</td>
                        <td class="py-3 px-4 text-[#2D2D2D]">{{ $team->employees_count }} {{ Str::plural('member', $team->employees_count) }}</td>
                        <td class="py-3 px-4">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold tracking-wider uppercase {{ $team->status === 'active' ? 'bg-[#2D2D2D] text-white' : 'bg-gray-200 text-gray-700' }}">
                                {{ ucfirst($team->status) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('teams.show', $team->id) }}" class="px-3 py-1.5 bg-[#2D2D2D] text-white rounded text-xs font-medium hover:bg-[#1a1a1a] inline-flex items-center gap-1.5" title="View Team & Manage Members">
                                    <i class="fas fa-users"></i> View
                                </a>
                                <a href="{{ route('teams.edit', $team->id) }}" class="w-7 h-7 rounded border border-gray-300 bg-white text-[#2D2D2D] hover:bg-[#2D2D2D] hover:text-white flex items-center justify-center text-xs" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('teams.destroy', $team->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this team? This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-7 h-7 rounded border border-gray-300 bg-white text-gray-500 hover:bg-[#2D2D2D] hover:text-white flex items-center justify-center text-xs" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-gray-400">No teams found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
