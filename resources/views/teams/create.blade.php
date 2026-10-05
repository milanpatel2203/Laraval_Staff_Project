@extends('layouts.app')

@section('title', 'Create Team')
@section('page-title', 'Create Team')

@section('content')
<div class="space-y-6">
    {{-- Alerts --}}
    @if($errors->any())
    <div class="bg-white border border-gray-400 text-[#2D2D2D] px-4 py-3 rounded flex items-center gap-2.5 text-sm font-medium">
        <i class="fas fa-exclamation-circle"></i>
        <span>{{ $errors->first() }}</span>
    </div>
    @endif

    {{-- Back Button --}}
    <a href="{{ route('teams.index') }}" class="inline-flex items-center gap-2 text-xs font-medium text-gray-600 hover:text-[#2D2D2D]">
        <i class="fas fa-arrow-left"></i> Back to Teams
    </a>

    <div class="bg-white border border-gray-200 rounded p-4 text-xs text-gray-500">
        <i class="fas fa-info-circle mr-2"></i>
        Create the team first, then you can add team members from the Edit Team page.
    </div>

    {{-- Create Form --}}
    <div class="bg-white border border-gray-200 rounded p-6">
        <form action="{{ route('teams.store') }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Team Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" placeholder="e.g. Development Team">
                    @error('name') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Team Code *</label>
                    <input type="text" name="code" value="{{ old('code') }}" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" placeholder="e.g. DEV">
                    @error('code') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Description</label>
                <textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" placeholder="Team responsibilities and scope...">{{ old('description') }}</textarea>
                @error('description') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Department</label>
                    <select name="department_id" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                        <option value="">Select Department (Optional)</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    @error('department_id') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Team Leader</label>
                    <select name="team_leader_id" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                        <option value="">Select Team Leader (Optional)</option>
                        @foreach($leaders as $leader)
                        <option value="{{ $leader->id }}" {{ old('team_leader_id') == $leader->id ? 'selected' : '' }}>{{ $leader->full_name }} ({{ $leader->employee_code }})</option>
                        @endforeach
                    </select>
                    @error('team_leader_id') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Status *</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                    <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('status') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('teams.index') }}" class="px-4 py-2 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-100">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-[#2D2D2D] text-white rounded text-xs font-semibold hover:bg-[#1a1a1a]">Create Team</button>
            </div>
        </form>
    </div>
</div>
@endsection
