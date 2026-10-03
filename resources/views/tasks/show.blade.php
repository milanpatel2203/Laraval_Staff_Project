@extends('layouts.app')

@section('title', 'Task Details')
@section('page-title', 'Task Details')

@section('content')
<div class="space-y-6">
    {{-- Alerts --}}
    @if(session('success'))
    <div class="bg-white border border-[#2D2D2D] text-[#2D2D2D] px-4 py-3 rounded flex items-center gap-2.5 text-sm font-medium">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('info'))
    <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded flex items-center gap-2.5 text-sm font-medium">
        <i class="fas fa-info-circle"></i>
        <span>{{ session('info') }}</span>
    </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-[#2D2D2D]">{{ $task->task_code }}</h2>
            <p class="text-xs text-gray-500 mt-0.5">{{ $task->title }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if(auth()->user()->role?->slug !== 'staff' && auth()->user()->can('update', $task))
            <a href="{{ route('tasks.edit', $task->id) }}" class="px-4 py-2 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-100">
                <i class="fas fa-edit mr-1"></i> Edit
            </a>
            @endif
            <a href="{{ route('tasks.index') }}" class="px-4 py-2 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-100">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>

    {{-- Task Details --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Details --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Task Information --}}
            <div class="bg-white border border-gray-200 rounded p-6">
                <h3 class="text-sm font-bold text-[#2D2D2D] mb-4 pb-2 border-b border-gray-200">Task Information</h3>

                {{-- Latest Activity Alert --}}
                @if($latestHistory)
                <div class="bg-blue-50 border border-blue-200 px-4 py-3 rounded mb-4">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-info-circle text-blue-600 mt-0.5"></i>
                        <div>
                            <span class="text-xs font-semibold text-blue-800">{{ $latestHistory->action }}</span>
                            @if($latestHistory->new_value)
                            <span class="text-xs text-blue-700 block mt-0.5">{{ $latestHistory->new_value }}</span>
                            @endif
                            @if($latestHistory->remarks)
                            <span class="text-[11px] text-blue-600 block mt-0.5 italic">"{{ $latestHistory->remarks }}"</span>
                            @endif
                            <span class="text-[10px] text-blue-500 mt-0.5 block">{{ $latestHistory->created_at->diffForHumans() }} by {{ $latestHistory->performer?->name ?? 'System' }}</span>
                        </div>
                    </div>
                </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-gray-500 block mb-1">Task Code</span>
                        <span class="font-semibold text-[#2D2D2D]">{{ $task->task_code }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block mb-1">Title</span>
                        <span class="font-semibold text-[#2D2D2D]">{{ $task->title }}</span>
                    </div>
                    <div class="md:col-span-2">
                        <span class="text-gray-500 block mb-1">Description</span>
                        <span class="text-[#2D2D2D]">{{ $task->description ?: '—' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block mb-1">Created By</span>
                        <span class="font-semibold text-[#2D2D2D]">{{ $task->creator?->name ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block mb-1">Assigned To</span>
                        <span class="font-semibold text-[#2D2D2D]">{{ $task->assignedEmployee->full_name ?? 'Unassigned' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block mb-1">Team</span>
                        <span class="font-semibold text-[#2D2D2D]">{{ $task->team?->name ?? 'Unassigned' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block mb-1">Priority</span>
                        @php
                            $priorityColors = [
                                'low' => 'bg-gray-200 text-gray-700',
                                'medium' => 'bg-blue-100 text-blue-700',
                                'high' => 'bg-orange-100 text-orange-700',
                                'urgent' => 'bg-red-100 text-red-700',
                            ];
                            $color = $priorityColors[$task->priority] ?? 'bg-gray-200 text-gray-700';
                        @endphp
                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $color }}">
                            {{ $task->priority }}
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-500 block mb-1">Status</span>
                        @php
                            $statusColors = [
                                'to_do' => 'bg-gray-200 text-gray-700',
                                'in_progress' => 'bg-blue-100 text-blue-700',
                                'on_hold' => 'bg-yellow-100 text-yellow-700',
                                'completed' => 'bg-green-100 text-green-700',
                                'cancelled' => 'bg-red-100 text-red-700',
                            ];
                            $statusColor = $statusColors[$task->status] ?? 'bg-gray-200 text-gray-700';
                        @endphp
                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $statusColor }}">
                            {{ str_replace('_', ' ', $task->status) }}
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-500 block mb-1">Start Date</span>
                        <span class="font-semibold text-[#2D2D2D]">{{ $task->start_date ? $task->start_date->format('d M Y') : '—' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block mb-1">Due Date</span>
                        <span class="font-semibold text-[#2D2D2D] {{ $task->isOverdue() ? 'text-red-600' : '' }}">
                            {{ $task->due_date ? $task->due_date->format('d M Y') : '—' }}
                            @if($task->isOverdue())
                            <span class="text-[10px] text-red-600 block">Overdue</span>
                            @endif
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-500 block mb-1">Completed At</span>
                        <span class="font-semibold text-[#2D2D2D]">{{ $task->completed_at ? $task->completed_at->format('d M Y H:i') : '—' }}</span>
                    </div>
                    <div class="md:col-span-2">
                        <span class="text-gray-500 block mb-1">Remarks</span>
                        <div class="bg-gray-50 p-3 rounded border border-gray-200">
                            <span class="text-[#2D2D2D] text-xs">{{ $task->remarks ?: 'No remarks added yet.' }}</span>
                            @if($task->remarks && $task->updated_at)
                            <div class="text-[10px] text-gray-400 mt-1">Last updated: {{ $task->updated_at->diffForHumans() }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-wrap items-center gap-2 mt-6 pt-4 border-t border-gray-200">
                    @if(auth()->user()->employee?->id === $task->assigned_to)
                    <div class="bg-blue-50 border border-blue-200 px-3 py-2 rounded text-xs text-blue-700 mb-2 w-full">
                        <i class="fas fa-user-check mr-1"></i> This task is assigned to you
                    </div>
                    @endif

                    @if(auth()->user()->role?->slug === 'staff' && auth()->user()->can('update', $task))
                    <form action="{{ route('tasks.update', $task->id) }}" method="POST" class="inline-flex items-center gap-2">
                        @csrf
                        @method('PUT')
                        <select name="status" class="px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" onchange="this.form.submit()">
                            <option value="to_do" {{ $task->status === 'to_do' ? 'selected' : '' }}>To Do</option>
                            <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="on_hold" {{ $task->status === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                            <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ $task->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </form>
                    @endif

                    @can('reassign', $task)
                    <button onclick="document.getElementById('reassignForm').classList.toggle('hidden')" class="px-4 py-2 bg-[#2D2D2D] text-white rounded text-xs font-semibold hover:bg-[#1a1a1a]">
                        <i class="fas fa-exchange-alt mr-1"></i> Reassign
                    </button>
                    @endcan
                </div>

                {{-- Reassign Form --}}
                <form id="reassignForm" action="{{ route('tasks.reassign', $task->id) }}" method="POST" class="hidden mt-4 p-4 bg-gray-50 rounded">
                    @csrf
                    <div class="mb-3">
                        <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Reassign To</label>
                        <select name="assigned_to" class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                            <option value="">Select Employee</option>
                            @if($task->team)
                                @foreach($task->team->employees as $emp)
                                @if($emp->id != $task->assigned_to)
                                <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                                @endif
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Remarks</label>
                        <textarea name="remarks" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" placeholder="Enter reassignment remarks"></textarea>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-[#2D2D2D] text-white rounded text-xs font-semibold hover:bg-[#1a1a1a]">Reassign</button>
                        <button type="button" onclick="document.getElementById('reassignForm').classList.add('hidden')" class="px-4 py-2 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-100">Cancel</button>
                    </div>
                </form>
            </div>

            {{-- Attachments --}}
            <div class="bg-white border border-gray-200 rounded p-6">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-200">
                    <h3 class="text-sm font-bold text-[#2D2D2D]">Attachments</h3>
                    @can('update', $task)
                    <button onclick="document.getElementById('uploadForm').classList.toggle('hidden')" class="px-3 py-1.5 bg-[#2D2D2D] text-white rounded text-xs font-semibold hover:bg-[#1a1a1a]">
                        <i class="fas fa-upload mr-1"></i> Upload
                    </button>
                    @endcan
                </div>

                {{-- Upload Form --}}
                <form id="uploadForm" action="{{ route('tasks.upload', $task->id) }}" method="POST" enctype="multipart/form-data" class="hidden mb-4 p-4 bg-gray-50 rounded">
                    @csrf
                    <div class="mb-3">
                        <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Select File</label>
                        <input type="file" name="file" class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" required>
                        <p class="text-[10px] text-gray-500 mt-1">Max size: 10MB. Allowed: PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG</p>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-[#2D2D2D] text-white rounded text-xs font-semibold hover:bg-[#1a1a1a]">Upload</button>
                        <button type="button" onclick="document.getElementById('uploadForm').classList.add('hidden')" class="px-4 py-2 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-100">Cancel</button>
                    </div>
                </form>

                @forelse($task->attachments as $attachment)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded mb-2">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-file text-gray-500"></i>
                        <div>
                            <span class="text-xs font-semibold text-[#2D2D2D]">{{ $attachment->file_name }}</span>
                            <span class="text-[10px] text-gray-500 block">{{ $attachment->file_size ? number_format($attachment->file_size / 1024, 2) . ' KB' : '' }}</span>
                        </div>
                    </div>
                    <a href="{{ route('tasks.download', $attachment->id) }}" class="px-3 py-1.5 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-100">
                        <i class="fas fa-download mr-1"></i> Download
                    </a>
                </div>
                @empty
                <p class="text-xs text-gray-400 text-center py-4">No attachments uploaded.</p>
                @endforelse
            </div>
        </div>

        {{-- Sidebar - Task History --}}
        <div class="space-y-6">
            <div class="bg-white border border-gray-200 rounded p-6">
                <h3 class="text-sm font-bold text-[#2D2D2D] mb-4 pb-2 border-b border-gray-200">Task History</h3>

                <div class="space-y-4 max-h-96 overflow-y-auto">
                    @forelse($task->histories->sortByDesc('created_at') as $history)
                    <div class="border-l-2 border-gray-200 pl-3">
                        <div class="text-[10px] text-gray-500 mb-0.5">{{ $history->created_at->format('d M Y H:i') }}</div>
                        <div class="text-xs font-semibold text-[#2D2D2D]">{{ $history->action }}</div>
                        @if($history->old_value || $history->new_value)
                        <div class="text-[11px] text-gray-600 mt-0.5">
                            @if($history->old_value)<span class="block text-gray-400">From: {{ $history->old_value }}</span>@endif
                            @if($history->new_value)<span class="block">To: {{ $history->new_value }}</span>@endif
                        </div>
                        @endif
                        @if($history->remarks)
                        <div class="text-[11px] text-gray-500 mt-0.5 italic">{{ $history->remarks }}</div>
                        @endif
                        <div class="text-[10px] text-gray-400 mt-0.5">By: {{ $history->performer?->name ?? 'System' }}</div>
                    </div>
                    @empty
                    <p class="text-xs text-gray-400 text-center py-4">No history recorded.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
