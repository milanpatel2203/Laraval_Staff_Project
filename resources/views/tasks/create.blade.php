@extends('layouts.app')

@section('title', 'Create Task')
@section('page-title', 'Create Task')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-[#2D2D2D]">Create New Task</h2>
            <p class="text-xs text-gray-500 mt-0.5">Assign a task to an employee.</p>
        </div>
        <a href="{{ route('tasks.index') }}" class="px-4 py-2 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-100">
            <i class="fas fa-arrow-left mr-1"></i> Back to Tasks
        </a>
    </div>

    {{-- Create Form --}}
    <div class="bg-white border border-gray-200 rounded p-6">
        <form action="{{ route('tasks.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Title --}}
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Task Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" placeholder="Enter task title" required>
                    @error('title')
                    <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Description</label>
                    <textarea name="description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" placeholder="Enter task description">{{ old('description') }}</textarea>
                    @error('description')
                    <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Assign To --}}
                <div>
                    <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Assign To <span class="text-red-500">*</span></label>
                    <select name="assigned_to" id="assigned_to" class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" required>
                        <option value="">Select Employee</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ old('assigned_to') == $emp->id ? 'selected' : '' }} data-team="{{ $emp->team_id }}">
                            {{ $emp->full_name }} {{ $emp->team ? '(' . $emp->team->name . ')' : '' }}
                        </option>
                        @endforeach
                    </select>
                    @error('assigned_to')
                    <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Priority --}}
                <div>
                    <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Priority <span class="text-red-500">*</span></label>
                    <select name="priority" class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" required>
                        <option value="">Select Priority</option>
                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                        <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                    @error('priority')
                    <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Start Date --}}
                <div>
                    <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Start Date</label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                    @error('start_date')
                    <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Due Date --}}
                <div>
                    <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Due Date</label>
                    <input type="date" name="due_date" value="{{ old('due_date') }}" class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                    @error('due_date')
                    <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remarks --}}
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Remarks</label>
                    <textarea name="remarks" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" placeholder="Enter any additional remarks">{{ old('remarks') }}</textarea>
                    @error('remarks')
                    <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200">
                <a href="{{ route('tasks.index') }}" class="px-4 py-2 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-100">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-[#2D2D2D] text-white rounded text-xs font-semibold hover:bg-[#1a1a1a]">
                    <i class="fas fa-plus mr-1"></i> Create Task
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('assigned_to').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const teamId = selectedOption.getAttribute('data-team');
    console.log('Selected employee team:', teamId);
});
</script>
@endsection
