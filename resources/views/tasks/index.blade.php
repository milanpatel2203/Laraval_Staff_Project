@extends('layouts.app')

@section('title', 'Tasks')
@section('page-title', 'Tasks')

@section('content')
<div class="space-y-6">
    {{-- Alerts --}}
    @if(session('success'))
    <div class="bg-white border border-[#2D2D2D] text-[#2D2D2D] px-4 py-3 rounded flex items-center gap-2.5 text-sm font-medium">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white border border-gray-200 rounded p-4 flex items-start gap-3.5">
            <div class="w-10 h-10 rounded bg-[#2D2D2D] text-[#F5F5F5] flex items-center justify-center text-base shrink-0">
                <i class="fas fa-tasks"></i>
            </div>
            <div class="flex flex-col">
                <span class="text-xl font-bold leading-tight text-[#2D2D2D]">{{ $statistics['total'] }}</span>
                <span class="text-[11px] text-gray-500 font-medium mt-0.5">Total Tasks</span>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded p-4 flex items-start gap-3.5">
            <div class="w-10 h-10 rounded bg-gray-300 text-[#2D2D2D] flex items-center justify-center text-base shrink-0">
                <i class="fas fa-clock"></i>
            </div>
            <div class="flex flex-col">
                <span class="text-xl font-bold leading-tight text-[#2D2D2D]">{{ $statistics['to_do'] }}</span>
                <span class="text-[11px] text-gray-500 font-medium mt-0.5">To Do</span>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded p-4 flex items-start gap-3.5">
            <div class="w-10 h-10 rounded bg-blue-500 text-white flex items-center justify-center text-base shrink-0">
                <i class="fas fa-spinner"></i>
            </div>
            <div class="flex flex-col">
                <span class="text-xl font-bold leading-tight text-[#2D2D2D]">{{ $statistics['in_progress'] }}</span>
                <span class="text-[11px] text-gray-500 font-medium mt-0.5">In Progress</span>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded p-4 flex items-start gap-3.5">
            <div class="w-10 h-10 rounded bg-yellow-500 text-white flex items-center justify-center text-base shrink-0">
                <i class="fas fa-pause"></i>
            </div>
            <div class="flex flex-col">
                <span class="text-xl font-bold leading-tight text-[#2D2D2D]">{{ $statistics['on_hold'] }}</span>
                <span class="text-[11px] text-gray-500 font-medium mt-0.5">On Hold</span>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded p-4 flex items-start gap-3.5">
            <div class="w-10 h-10 rounded bg-green-500 text-white flex items-center justify-center text-base shrink-0">
                <i class="fas fa-check"></i>
            </div>
            <div class="flex flex-col">
                <span class="text-xl font-bold leading-tight text-[#2D2D2D]">{{ $statistics['completed'] }}</span>
                <span class="text-[11px] text-gray-500 font-medium mt-0.5">Completed</span>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded p-4 flex items-start gap-3.5">
            <div class="w-10 h-10 rounded bg-red-500 text-white flex items-center justify-center text-base shrink-0">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="flex flex-col">
                <span class="text-xl font-bold leading-tight text-[#2D2D2D]">{{ $statistics['overdue'] }}</span>
                <span class="text-[11px] text-gray-500 font-medium mt-0.5">Overdue</span>
            </div>
        </div>
    </div>

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-[#2D2D2D]">Task List ({{ $tasks->total() }})</h2>
            <p class="text-xs text-gray-500 mt-0.5">Manage and track all tasks.</p>
        </div>
        @can('create', App\Models\Task::class)
        <a href="{{ route('tasks.create') }}" class="bg-[#2D2D2D] hover:bg-[#1a1a1a] text-white text-xs font-semibold px-4 py-2 rounded inline-flex items-center gap-2">
            <i class="fas fa-plus"></i> Create Task
        </a>
        @endcan
    </div>

    {{-- Filter Form --}}
    <div class="bg-white border border-gray-200 rounded p-4">
        <form action="{{ route('tasks.index') }}" method="GET" class="flex flex-col lg:flex-row items-center gap-3">
            <div class="w-full lg:flex-1">
                <input type="text" name="search" value="{{ request('search') }}" class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]" placeholder="Search by title, code, or employee...">
            </div>
            <div class="w-full lg:w-36">
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                    <option value="">All Status</option>
                    <option value="to_do" {{ request('status') == 'to_do' ? 'selected' : '' }}>To Do</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="on_hold" {{ request('status') == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="w-full lg:w-36">
                <select name="priority" class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                    <option value="">All Priority</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                    <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
            </div>
            <div class="w-full lg:w-48">
                <select name="employee" class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                    <option value="">All Employees</option>
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ request('employee') == $emp->id ? 'selected' : '' }}>{{ $emp->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full lg:w-48">
                <select name="team" class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                    <option value="">All Teams</option>
                    @foreach($teams as $team)
                    <option value="{{ $team->id }}" {{ request('team') == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2 w-full lg:w-auto">
                <button type="submit" class="px-4 py-2 bg-[#2D2D2D] text-white rounded text-xs font-semibold hover:bg-[#1a1a1a] flex items-center gap-1.5">
                    <i class="fas fa-search"></i> Filter
                </button>
                @if(request()->hasAny(['search', 'status', 'priority', 'employee', 'team']))
                <a href="{{ route('tasks.index') }}" class="px-3 py-2 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-100">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tasks Table --}}
    <div class="bg-white border border-gray-200 rounded overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50/50 text-gray-500 uppercase tracking-wider">
                        <th class="py-3 px-4 font-semibold">Task</th>
                        <th class="py-3 px-4 font-semibold">Code</th>
                        <th class="py-3 px-4 font-semibold">Assigned To</th>
                        <th class="py-3 px-4 font-semibold">Team</th>
                        <th class="py-3 px-4 font-semibold">Priority</th>
                        <th class="py-3 px-4 font-semibold">Due Date</th>
                        <th class="py-3 px-4 font-semibold">Status</th>
                        <th class="py-3 px-4 font-semibold">Latest Remarks</th>
                        <th class="py-3 px-4 font-semibold">Created By</th>
                        <th class="py-3 px-4 font-semibold w-24 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-[13px]">
                    @forelse($tasks as $task)
                    <tr class="hover:bg-gray-50/50 {{ $task->isOverdue() ? 'bg-red-50/30' : '' }}">
                        <td class="py-3 px-4">
                            <span class="font-semibold text-[#2D2D2D]">{{ $task->title }}</span>
                        </td>
                        <td class="py-3 px-4 font-bold text-[#2D2D2D]">{{ $task->task_code }}</td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-[#2D2D2D] text-[#F5F5F5] flex items-center justify-center text-xs shrink-0">
                                    <i class="fas fa-user"></i>
                                </div>
                                <span class="font-medium text-[#2D2D2D]">{{ $task->assignedEmployee->full_name ?? 'Unassigned' }}</span>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-[#2D2D2D]">{{ $task->team?->name ?? 'Unassigned' }}</td>
                        <td class="py-3 px-4">
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
                        </td>
                        <td class="py-3 px-4 text-gray-600 {{ $task->isOverdue() ? 'text-red-600 font-bold' : '' }}">
                            {{ $task->due_date ? $task->due_date->format('d M Y') : '—' }}
                            @if($task->isOverdue())
                            <span class="text-[10px] text-red-600 block">Overdue</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
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
                        </td>
                        <td class="py-3 px-4">
                            @if($task->remarks)
                            <div class="max-w-xs truncate text-gray-600 text-[11px]" title="{{ $task->remarks }}">
                                {{ $task->remarks }}
                            </div>
                            @else
                            <span class="text-gray-400 text-[11px]">—</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-gray-600">{{ $task->creator?->name ?? '—' }}</td>
                        <td class="py-3 px-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('tasks.show', $task->id) }}" class="w-7 h-7 rounded border border-gray-300 bg-white text-[#2D2D2D] hover:bg-[#2D2D2D] hover:text-white flex items-center justify-center text-xs" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>

                                @if(auth()->user()->role?->slug === 'staff' && auth()->user()->can('update', $task))
                                <div class="relative">
                                    <button onclick="document.getElementById('statusDropdown-{{ $task->id }}').classList.toggle('hidden')" class="w-7 h-7 rounded border border-gray-300 bg-white text-[#2D2D2D] hover:bg-[#2D2D2D] hover:text-white flex items-center justify-center text-xs" title="Update Status">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <div id="statusDropdown-{{ $task->id }}" class="hidden absolute right-0 top-full mt-1 z-10 bg-white border border-gray-200 rounded shadow-lg p-2 min-w-[140px]">
                                        <div class="text-[10px] text-gray-500 mb-1 px-2">Update Status:</div>
                                        <div class="space-y-1">
                                            <form action="{{ route('tasks.update', $task->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="to_do">
                                                <button type="submit" class="w-full text-left px-2 py-1.5 text-xs text-[#2D2D2D] hover:bg-gray-100 rounded {{ $task->status === 'to_do' ? 'bg-blue-100 font-semibold' : '' }}">
                                                    To Do
                                                </button>
                                            </form>
                                            <form action="{{ route('tasks.update', $task->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="in_progress">
                                                <button type="submit" class="w-full text-left px-2 py-1.5 text-xs text-[#2D2D2D] hover:bg-gray-100 rounded {{ $task->status === 'in_progress' ? 'bg-blue-100 font-semibold' : '' }}">
                                                    In Progress
                                                </button>
                                            </form>
                                            <form action="{{ route('tasks.update', $task->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="on_hold">
                                                <button type="submit" class="w-full text-left px-2 py-1.5 text-xs text-[#2D2D2D] hover:bg-gray-100 rounded {{ $task->status === 'on_hold' ? 'bg-blue-100 font-semibold' : '' }}">
                                                    On Hold
                                                </button>
                                            </form>
                                            <form action="{{ route('tasks.update', $task->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="completed">
                                                <button type="submit" class="w-full text-left px-2 py-1.5 text-xs text-[#2D2D2D] hover:bg-gray-100 rounded {{ $task->status === 'completed' ? 'bg-blue-100 font-semibold' : '' }}">
                                                    Completed
                                                </button>
                                            </form>
                                            <form action="{{ route('tasks.update', $task->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" class="w-full text-left px-2 py-1.5 text-xs text-[#2D2D2D] hover:bg-gray-100 rounded {{ $task->status === 'cancelled' ? 'bg-blue-100 font-semibold' : '' }}">
                                                    Cancelled
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @elseif(auth()->user()->can('update', $task))
                                <a href="{{ route('tasks.edit', $task->id) }}" class="w-7 h-7 rounded border border-gray-300 bg-white text-[#2D2D2D] hover:bg-[#2D2D2D] hover:text-white flex items-center justify-center text-xs" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-8 text-center text-gray-400">No tasks found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tasks->hasPages())
        <div class="p-4 border-t border-gray-200">
            {{ $tasks->links() }}
        </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('click', function(event) {
        const dropdowns = document.querySelectorAll('[id^="statusDropdown-"]');
        dropdowns.forEach(function(dropdown) {
            if (!dropdown.classList.contains('hidden')) {
                const button = dropdown.previousElementSibling;
                if (!dropdown.contains(event.target) && !button.contains(event.target)) {
                    dropdown.classList.add('hidden');
                }
            }
        });
    });
</script>
@endsection
