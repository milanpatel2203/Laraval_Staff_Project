@extends('layouts.app')

@section('title', 'Edit Task')
@section('page-title', 'Edit Task')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-[#2D2D2D]">Edit Task: {{ $task->task_code }}</h2>
            <p class="text-xs text-gray-500 mt-0.5">Update task details.</p>
        </div>
        <a href="{{ route('tasks.show', $task->id) }}" class="px-4 py-2 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-100">
            <i class="fas fa-arrow-left mr-1"></i> Back to Task
        </a>
    </div>

    {{-- Edit Form --}}
    <div class="bg-white border border-gray-200 rounded p-6">
        <form action="{{ route('tasks.update', $task->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Title --}}
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Task Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $task->title) }}" class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" placeholder="Enter task title" required>
                    @error('title')
                    <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Description</label>
                    <textarea name="description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" placeholder="Enter task description">{{ old('description', $task->description) }}</textarea>
                    @error('description')
                    <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                @can('reassign', $task)
                {{-- Assign To --}}
                <div>
                    <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Reassign To</label>
                    <select name="assigned_to" class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                        <option value="">Current: {{ $task->assignedEmployee->full_name ?? 'Unassigned' }}</option>
                        @foreach($employees as $emp)
                        @if($emp->id != $task->assigned_to)
                        <option value="{{ $emp->id }}" {{ old('assigned_to') == $emp->id ? 'selected' : '' }} data-team="{{ $emp->team_id }}">
                            {{ $emp->full_name }} {{ $emp->team ? '(' . $emp->team->name . ')' : '' }}
                        </option>
                        @endif
                        @endforeach
                    </select>
                    @error('assigned_to')
                    <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                @endcan

                {{-- Priority --}}
                <div>
                    <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Priority <span class="text-red-500">*</span></label>
                    <select name="priority" class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" required>
                        <option value="low" {{ old('priority', $task->priority) == 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ old('priority', $task->priority) == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ old('priority', $task->priority) == 'high' ? 'selected' : '' }}>High</option>
                        <option value="urgent" {{ old('priority', $task->priority) == 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                    @error('priority')
                    <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Status <span class="text-red-500">*</span></label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" required>
                        <option value="to_do" {{ old('status', $task->status) == 'to_do' ? 'selected' : '' }}>To Do</option>
                        <option value="in_progress" {{ old('status', $task->status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="on_hold" {{ old('status', $task->status) == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                        <option value="completed" {{ old('status', $task->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ old('status', $task->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    @error('status')
                    <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Start Date --}}
                <div>
                    <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Start Date</label>
                    <input type="date" name="start_date" value="{{ old('start_date', $task->start_date?->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                    @error('start_date')
                    <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Due Date --}}
                <div>
                    <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Due Date</label>
                    <input type="date" name="due_date" value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                    @error('due_date')
                    <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remarks --}}
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Remarks</label>
                    <textarea name="remarks" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" placeholder="Enter any additional remarks">{{ old('remarks', $task->remarks) }}</textarea>
                    @error('remarks')
                    <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200">
                <a href="{{ route('tasks.show', $task->id) }}" class="px-4 py-2 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-100">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-[#2D2D2D] text-white rounded text-xs font-semibold hover:bg-[#1a1a1a]">
                    <i class="fas fa-save mr-1"></i> Update Task
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
