@extends('layouts.app')

@section('title', 'Edit Team')
@section('page-title', 'Edit Team')

@section('content')
<div class="space-y-6">
    {{-- Alerts --}}
    @if($errors->any())
    <div class="bg-white border border-gray-400 text-[#2D2D2D] px-4 py-3 rounded flex items-center gap-2.5 text-sm font-medium">
        <i class="fas fa-exclamation-circle"></i>
        <span>{{ $errors->first() }}</span>
    </div>
    @endif

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

    {{-- Edit Form --}}
    <div class="bg-white border border-gray-200 rounded p-6">
        <form action="{{ route('teams.update', $team->id) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Team Name *</label>
                    <input type="text" name="name" value="{{ old('name', $team->name) }}" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" placeholder="e.g. Development Team">
                    @error('name') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Team Code *</label>
                    <input type="text" name="code" value="{{ old('code', $team->code) }}" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" placeholder="e.g. DEV">
                    @error('code') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Description</label>
                <textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" placeholder="Team responsibilities and scope...">{{ old('description', $team->description) }}</textarea>
                @error('description') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Department</label>
                    <select name="department_id" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                        <option value="">Select Department (Optional)</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ old('department_id', $team->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    @error('department_id') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Team Leader</label>
                    <select name="team_leader_id" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                        <option value="">Select Team Leader (Optional)</option>
                        @foreach($leaders as $leader)
                        <option value="{{ $leader->id }}" {{ old('team_leader_id', $team->team_leader_id) == $leader->id ? 'selected' : '' }}>{{ $leader->full_name }} ({{ $leader->employee_code }})</option>
                        @endforeach
                    </select>
                    @error('team_leader_id') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Status *</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                    <option value="active" {{ old('status', $team->status) === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $team->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('status') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('teams.index') }}" class="px-4 py-2 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-100">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-[#2D2D2D] text-white rounded text-xs font-semibold hover:bg-[#1a1a1a]">Update Team</button>
            </div>
        </form>
    </div>

    {{-- Team Members Section --}}
    <div class="bg-white border border-gray-200 rounded p-6">
        <h3 class="text-sm font-bold text-[#2D2D2D] mb-4">Team Members ({{ $team->employees->count() }})</h3>

        {{-- Add Member Form --}}
        <form action="{{ route('teams.assign-employee', $team->id) }}" method="POST" class="flex items-end gap-3 mb-6">
            @csrf
            <div class="flex-1">
                <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Add Team Member</label>
                <select name="employee_id" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                    <option value="">Select Employee...</option>
                    @php
                        $availableEmployees = \App\Models\Employee::where(function($query) use ($team) {
                            $query->whereNull('team_id')
                                ->orWhere('team_id', $team->id);
                        })
                        ->where('status', 'active')
                        ->orderBy('first_name')
                        ->get();
                    @endphp
                    @foreach($availableEmployees as $emp)
                        @if($emp->team_id != $team->id)
                        <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->employee_code }})</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-[#2D2D2D] text-white rounded text-xs font-semibold hover:bg-[#1a1a1a] h-9">Add</button>
        </form>

        {{-- Current Members --}}
        @if($team->employees->count() > 0)
        <div class="border border-gray-200 rounded overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50/50 text-gray-500 uppercase tracking-wider">
                            <th class="py-3 px-4 font-semibold">Employee</th>
                            <th class="py-3 px-4 font-semibold">Code</th>
                            <th class="py-3 px-4 font-semibold">Department</th>
                            <th class="py-3 px-4 font-semibold w-24 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-[13px]">
                        @foreach($team->employees as $employee)
                        <tr class="hover:bg-gray-50/50">
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
                            <td class="py-3 px-4 font-bold text-[#2D2D2D]">{{ $employee->employee_code }}</td>
                            <td class="py-3 px-4 text-[#2D2D2D]">{{ $employee->department ? $employee->department->name : 'Unassigned' }}</td>
                            <td class="py-3 px-4 text-right">
                                @if($team->team_leader_id !== $employee->id)
                                <form action="{{ route('teams.remove-employee', [$team->id, $employee->id]) }}" method="POST" class="inline" onsubmit="return confirm('Remove {{ $employee->full_name }} from this team?');">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-red-50 hover:text-red-600 hover:border-red-300">Remove</button>
                                </form>
                                @else
                                <span class="text-[10px] text-gray-400 italic">Leader</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="py-8 text-center text-gray-400 border border-gray-200 rounded">
            No team members assigned yet.
        </div>
        @endif
    </div>
</div>
@endsection
