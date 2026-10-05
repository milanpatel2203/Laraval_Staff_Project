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

    {{-- My Notifications for this task (for staff/assigned employee) --}}
    @php
        $myTaskNotifications = \App\Models\TaskNotification::where('user_id', auth()->id())
            ->where('task_id', $task->id)
            ->latest()
            ->take(3)
            ->get();
    @endphp

    @if($myTaskNotifications->count() > 0)
    <div class="space-y-2">
        @foreach($myTaskNotifications as $notif)
        @php
            $notifColors = [
                'assigned'        => 'bg-green-50 border-green-200 text-green-800',
                'reassigned_to'   => 'bg-blue-50 border-blue-200 text-blue-800',
                'reassigned_from' => 'bg-amber-50 border-amber-200 text-amber-800',
                'status_changed'  => 'bg-gray-50 border-gray-200 text-gray-800',
            ];
            $notifIcons = [
                'assigned'        => 'fa-user-plus',
                'reassigned_to'   => 'fa-exchange-alt',
                'reassigned_from' => 'fa-user-minus',
                'status_changed'  => 'fa-sync',
            ];
            $colorClass = $notifColors[$notif->type] ?? 'bg-gray-50 border-gray-200 text-gray-800';
            $iconClass  = $notifIcons[$notif->type] ?? 'fa-bell';
        @endphp
        <div class="border {{ $colorClass }} px-4 py-3 rounded flex items-start gap-2.5 text-sm">
            <i class="fas {{ $iconClass }} mt-0.5"></i>
            <div class="flex-1">
                <span>{{ $notif->message }}</span>
                <span class="block text-[11px] mt-0.5 opacity-70">{{ $notif->created_at->format('d M Y, h:i A') }}</span>
            </div>
        </div>
        @endforeach
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
            @can('delete', $task)
            <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this task?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 border border-red-200 rounded text-xs font-medium text-red-600 bg-white hover:bg-red-50">
                    <i class="fas fa-trash mr-1"></i> Delete
                </button>
            </form>
            @endcan
            <a href="{{ route('tasks.index') }}" class="px-4 py-2 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-100">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>

    {{-- Task Details + Sidebar --}}
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
                            <span class="text-[10px] text-blue-500 mt-0.5 block">
                                {{ $latestHistory->created_at->diffForHumans() }} by {{ $latestHistory->performer?->name ?? 'System' }}
                            </span>
                        </div>
                    </div>
                </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-gray-500 block mb-1">Task Code</span>
                        <span class="font-bold text-[#2D2D2D]">{{ $task->task_code }}</span>
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
                        <span class="text-gray-500 block mb-1">Team</span>
                        <span class="font-semibold text-[#2D2D2D]">{{ $task->team?->name ?? 'Unassigned' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block mb-1">Assigned To</span>
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-[#2D2D2D] text-white flex items-center justify-center text-[10px]">
                                <i class="fas fa-user"></i>
                            </div>
                            <span class="font-semibold text-[#2D2D2D]">{{ $task->assignedEmployee?->full_name ?? 'Unassigned' }}</span>
                        </div>
                    </div>
                    <div>
                        <span class="text-gray-500 block mb-1">Assigned By</span>
                        <span class="font-semibold text-[#2D2D2D]">{{ $task->creator?->name ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block mb-1">Assigned Date</span>
                        <span class="font-semibold text-[#2D2D2D]">
                            {{ $task->assigned_date ? $task->assigned_date->format('d M Y') : ($task->created_at?->format('d M Y') ?? '—') }}
                        </span>
                    </div>
                    @if($task->last_reassigned_by)
                    <div>
                        <span class="text-gray-500 block mb-1">Last Reassigned By</span>
                        <span class="font-semibold text-[#2D2D2D]">{{ $task->lastReassignedBy?->name ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block mb-1">Last Reassigned Date</span>
                        <span class="font-semibold text-[#2D2D2D]">
                            {{ $task->last_reassigned_at ? $task->last_reassigned_at->format('d M Y, h:i A') : '—' }}
                        </span>
                    </div>
                    @endif
                    <div>
                        <span class="text-gray-500 block mb-1">Priority</span>
                        @php
                            $priorityColors = [
                                'low'    => 'bg-gray-200 text-gray-700',
                                'medium' => 'bg-blue-100 text-blue-700',
                                'high'   => 'bg-orange-100 text-orange-700',
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
                                'to_do'       => 'bg-gray-200 text-gray-700',
                                'in_progress' => 'bg-blue-100 text-blue-700',
                                'on_hold'     => 'bg-yellow-100 text-yellow-700',
                                'completed'   => 'bg-green-100 text-green-700',
                                'cancelled'   => 'bg-red-100 text-red-700',
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
                            <span class="text-[10px] text-red-600 block font-normal"><i class="fas fa-exclamation-triangle mr-1"></i>Overdue</span>
                            @endif
                        </span>
                    </div>
                    @if($task->completed_at)
                    <div>
                        <span class="text-gray-500 block mb-1">Completed At</span>
                        <span class="font-semibold text-green-700">{{ $task->completed_at->format('d M Y, h:i A') }}</span>
                    </div>
                    @endif
                    <div class="md:col-span-2">
                        <span class="text-gray-500 block mb-1">Remarks</span>
                        <div class="bg-gray-50 p-3 rounded border border-gray-200">
                            <span class="text-[#2D2D2D] text-xs">{{ $task->remarks ?: 'No remarks added yet.' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-wrap items-center gap-2 mt-6 pt-4 border-t border-gray-200">
                    @if(auth()->user()->employee?->id === $task->assigned_to)
                    <div class="bg-green-50 border border-green-200 px-3 py-2 rounded text-xs text-green-700 w-full">
                        <i class="fas fa-user-check mr-1"></i> This task is currently assigned to you
                    </div>
                    @endif

                    {{-- Staff: Update Status + Remarks --}}
                    @if(auth()->user()->role?->slug === 'staff' && auth()->user()->can('update', $task))
                    <div class="w-full">
                        <form action="{{ route('tasks.update', $task->id) }}" method="POST" class="space-y-3 p-4 bg-gray-50 rounded border border-gray-200">
                            @csrf
                            @method('PUT')
                            <div class="flex items-center gap-3">
                                <div class="flex-1">
                                    <label class="block text-[10px] font-semibold text-gray-600 mb-1">Update Status</label>
                                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                                        <option value="to_do"       {{ $task->status === 'to_do'       ? 'selected' : '' }}>To Do</option>
                                        <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="on_hold"     {{ $task->status === 'on_hold'     ? 'selected' : '' }}>On Hold</option>
                                        <option value="completed"   {{ $task->status === 'completed'   ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled"   {{ $task->status === 'cancelled'   ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </div>
                                <div class="flex-1">
                                    <label class="block text-[10px] font-semibold text-gray-600 mb-1">Remarks</label>
                                    <input type="text" name="remarks" value="{{ $task->remarks }}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]"
                                        placeholder="Add remarks...">
                                </div>
                                <div class="pt-4">
                                    <button type="submit" class="px-4 py-2 bg-[#2D2D2D] text-white rounded text-xs font-semibold hover:bg-[#1a1a1a]">
                                        <i class="fas fa-save mr-1"></i> Save
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    @endif

                    {{-- Admin/HR: Reassign Button --}}
                    @can('reassign', $task)
                    <button onclick="document.getElementById('reassignForm').classList.toggle('hidden')"
                        class="px-4 py-2 bg-[#2D2D2D] text-white rounded text-xs font-semibold hover:bg-[#1a1a1a]">
                        <i class="fas fa-exchange-alt mr-1"></i> Reassign Task
                    </button>
                    @endcan
                </div>

                {{-- Reassign Form --}}
                @can('reassign', $task)
                <form id="reassignForm" action="{{ route('tasks.reassign', $task->id) }}" method="POST"
                    class="hidden mt-4 p-4 bg-amber-50 border border-amber-200 rounded">
                    @csrf
                    <h4 class="text-xs font-bold text-amber-800 mb-3">
                        <i class="fas fa-exchange-alt mr-1"></i> Reassign Task
                    </h4>

                    {{-- Current Assignment --}}
                    <div class="mb-3 p-2 bg-white border border-amber-200 rounded text-[11px] text-amber-700">
                        <i class="fas fa-user mr-1"></i>
                        Currently assigned to: <strong>{{ $task->assignedEmployee?->full_name ?? 'Unassigned' }}</strong>
                        @if($task->team) ({{ $task->team->name }}) @endif
                    </div>

                    <div class="mb-3">
                        <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Reassign To <span class="text-red-500">*</span></label>
                        <select name="assigned_to" class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" required>
                            <option value="">Select New Employee</option>
                            @foreach($teamEmployees as $emp)
                            @if($emp->id != $task->assigned_to)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                            @endif
                            @endforeach
                        </select>
                        @if($teamEmployees->count() === 0 || $teamEmployees->where('id', '!=', $task->assigned_to)->count() === 0)
                        <p class="text-[10px] text-amber-600 mt-1">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            No other employees available in this team. Edit the task to change the team first.
                        </p>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Remarks</label>
                        <textarea name="remarks" rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]"
                            placeholder="Enter reason for reassignment..."></textarea>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded text-xs font-semibold hover:bg-amber-700">
                            <i class="fas fa-exchange-alt mr-1"></i> Confirm Reassign
                        </button>
                        <button type="button" onclick="document.getElementById('reassignForm').classList.add('hidden')"
                            class="px-4 py-2 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-100">
                            Cancel
                        </button>
                    </div>
                </form>
                @endcan
            </div>

            {{-- Assignment History --}}
            @if($assignmentHistories->count() > 0)
            <div class="bg-white border border-gray-200 rounded p-6">
                <h3 class="text-sm font-bold text-[#2D2D2D] mb-4 pb-2 border-b border-gray-200">
                    <i class="fas fa-exchange-alt mr-2 text-amber-500"></i>
                    Assignment History
                </h3>

                <div class="space-y-4">
                    @foreach($assignmentHistories as $history)
                    <div class="border border-gray-100 rounded p-4 bg-gray-50">
                        {{-- Task Code Header --}}
                        <div class="flex items-center justify-between mb-3">
                            <span class="font-bold text-sm text-[#2D2D2D]">{{ $task->task_code }}</span>
                            <span class="text-[10px] text-gray-400">{{ $history->created_at->format('d M Y, h:i A') }}</span>
                        </div>

                        @if($history->action === 'Task Assigned')
                        {{-- Initial Assignment --}}
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs shrink-0">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div>
                                <div class="font-semibold text-sm text-[#2D2D2D]">
                                    {{ $history->newAssignedEmployee?->full_name ?? $history->new_value ?? 'Unknown' }}
                                </div>
                                <div class="text-[11px] text-gray-500 mt-0.5">
                                    Assigned by <span class="font-medium text-[#2D2D2D]">{{ $history->performer?->name ?? 'Unknown' }}</span>
                                </div>
                                <div class="text-[10px] text-gray-400 mt-0.5">{{ $history->created_at->format('d M Y, h:i A') }}</div>
                            </div>
                        </div>

                        @elseif($history->action === 'Task Reassigned')
                        {{-- Reassignment --}}
                        <div class="space-y-3">
                            {{-- New Assignee --}}
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs shrink-0">
                                    <i class="fas fa-user-check"></i>
                                </div>
                                <div>
                                    <div class="font-semibold text-sm text-[#2D2D2D]">
                                        {{ $history->newAssignedEmployee?->full_name ?? $history->new_value ?? 'Unknown' }}
                                    </div>
                                    <div class="text-[11px] text-gray-500 mt-0.5">
                                        Assigned by <span class="font-medium text-[#2D2D2D]">{{ $history->performer?->name ?? 'Unknown' }}</span>
                                    </div>
                                    <div class="text-[10px] text-gray-400 mt-0.5">{{ $history->created_at->format('d M Y, h:i A') }}</div>
                                </div>
                            </div>

                            {{-- Old Assignee --}}
                            <div class="flex items-start gap-3 pl-4 border-l-2 border-amber-200">
                                <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-xs shrink-0">
                                    <i class="fas fa-user-minus"></i>
                                </div>
                                <div>
                                    <div class="text-[11px] text-gray-500">Previously assigned to</div>
                                    <div class="font-semibold text-sm text-[#2D2D2D]">
                                        {{ $history->oldAssignedEmployee?->full_name ?? $history->old_value ?? 'Unknown' }}
                                    </div>
                                    <div class="text-[11px] text-gray-500 mt-0.5">
                                        Reassigned by <span class="font-medium text-[#2D2D2D]">{{ $history->performer?->name ?? 'Unknown' }}</span>
                                    </div>
                                </div>
                            </div>

                            @if($history->remarks)
                            <div class="text-[11px] text-gray-500 italic pl-4 border-l-2 border-gray-200">
                                "{{ $history->remarks }}"
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Attachments --}}
            <div class="bg-white border border-gray-200 rounded p-6">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-200">
                    <h3 class="text-sm font-bold text-[#2D2D2D]">Attachments</h3>
                    @can('update', $task)
                    <button onclick="document.getElementById('uploadForm').classList.toggle('hidden')"
                        class="px-3 py-1.5 bg-[#2D2D2D] text-white rounded text-xs font-semibold hover:bg-[#1a1a1a]">
                        <i class="fas fa-upload mr-1"></i> Upload
                    </button>
                    @endcan
                </div>

                {{-- Upload Form --}}
                <form id="uploadForm" action="{{ route('tasks.upload', $task->id) }}" method="POST" enctype="multipart/form-data" class="hidden mb-4 p-4 bg-gray-50 rounded">
                    @csrf
                    <div class="mb-3">
                        <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Select File</label>
                        <input type="file" name="file"
                            class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" required>
                        <p class="text-[10px] text-gray-500 mt-1">Max size: 10MB. Allowed: PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG</p>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-[#2D2D2D] text-white rounded text-xs font-semibold hover:bg-[#1a1a1a]">Upload</button>
                        <button type="button" onclick="document.getElementById('uploadForm').classList.add('hidden')"
                            class="px-4 py-2 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-100">Cancel</button>
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
                    <a href="{{ route('tasks.download', $attachment->id) }}"
                        class="px-3 py-1.5 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-100">
                        <i class="fas fa-download mr-1"></i> Download
                    </a>
                </div>
                @empty
                <p class="text-xs text-gray-400 text-center py-4">No attachments uploaded.</p>
                @endforelse
            </div>
        </div>

        {{-- Sidebar: Full Task History --}}
        <div class="space-y-6">
            <div class="bg-white border border-gray-200 rounded p-6">
                <h3 class="text-sm font-bold text-[#2D2D2D] mb-4 pb-2 border-b border-gray-200">
                    <i class="fas fa-history mr-2 text-gray-400"></i>Task History
                </h3>

                <div class="space-y-4 max-h-[600px] overflow-y-auto pr-1">
                    @forelse($task->histories->sortByDesc('created_at') as $history)
                    <div class="border-l-2 {{ in_array($history->action, ['Task Assigned', 'Task Reassigned']) ? 'border-amber-400' : 'border-gray-200' }} pl-3">
                        <div class="text-[10px] text-gray-400 mb-0.5">{{ $history->created_at->format('d M Y, h:i A') }}</div>
                        <div class="text-xs font-semibold text-[#2D2D2D]">
                            @if($history->action === 'Task Assigned')
                                <span class="text-green-600"><i class="fas fa-user-plus mr-1"></i>{{ $history->action }}</span>
                            @elseif($history->action === 'Task Reassigned')
                                <span class="text-amber-600"><i class="fas fa-exchange-alt mr-1"></i>{{ $history->action }}</span>
                            @elseif($history->action === 'Status Changed')
                                <span class="text-blue-600"><i class="fas fa-sync mr-1"></i>{{ $history->action }}</span>
                            @elseif($history->action === 'Task Created')
                                <span class="text-gray-700"><i class="fas fa-plus mr-1"></i>{{ $history->action }}</span>
                            @else
                                {{ $history->action }}
                            @endif
                        </div>

                        @if($history->action === 'Task Reassigned')
                        <div class="text-[11px] text-gray-600 mt-0.5">
                            <span class="block text-gray-400">From: {{ $history->oldAssignedEmployee?->full_name ?? $history->old_value }}</span>
                            <span class="block">To: {{ $history->newAssignedEmployee?->full_name ?? $history->new_value }}</span>
                        </div>
                        @elseif($history->action === 'Task Assigned')
                        <div class="text-[11px] text-gray-600 mt-0.5">
                            <span class="block">To: {{ $history->newAssignedEmployee?->full_name ?? $history->new_value }}</span>
                        </div>
                        @elseif($history->old_value || $history->new_value)
                        <div class="text-[11px] text-gray-600 mt-0.5">
                            @if($history->old_value)<span class="block text-gray-400">From: {{ Str::limit($history->old_value, 60) }}</span>@endif
                            @if($history->new_value)<span class="block">To: {{ Str::limit($history->new_value, 60) }}</span>@endif
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
