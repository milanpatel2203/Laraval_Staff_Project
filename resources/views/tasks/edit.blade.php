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

    {{-- Error Alert --}}
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Edit Form --}}
    <div class="bg-white border border-gray-200 rounded p-6">
        <form action="{{ route('tasks.update', $task->id) }}" method="POST" id="editTaskForm">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Title --}}
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Task Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $task->title) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]"
                        placeholder="Enter task title" required>
                    @error('title')
                    <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Description</label>
                    <textarea name="description" rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]"
                        placeholder="Enter task description">{{ old('description', $task->description) }}</textarea>
                    @error('description')
                    <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Team + Assigned To (only for authorized roles) --}}
                @can('reassign', $task)
                {{-- Team --}}
                <div>
                    <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Team</label>
                    <select name="team_id" id="team_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                        <option value="">Keep current team ({{ $task->team?->name ?? 'Unassigned' }})</option>
                        @foreach($teams as $team)
                        <option value="{{ $team->id }}" {{ old('team_id', $task->team_id) == $team->id ? 'selected' : '' }}>
                            {{ $team->name }}
                        </option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-gray-400 mt-1">Change team to load different employees below.</p>
                </div>

                {{-- Reassign To --}}
                <div>
                    <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">
                        Reassign To
                        <span class="text-[10px] font-normal text-gray-400 ml-1">(leave empty to keep current assignment)</span>
                    </label>

                    {{-- Current Assignment Info --}}
                    <div class="mb-2 p-2 bg-blue-50 border border-blue-200 rounded text-[11px] text-blue-700">
                        <i class="fas fa-user-check mr-1"></i>
                        Currently assigned to: <strong>{{ $task->assignedEmployee?->full_name ?? 'Unassigned' }}</strong>
                        @if($task->team)
                        ({{ $task->team->name }})
                        @endif
                    </div>

                    <select name="assigned_to" id="assigned_to"
                        class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                        <option value="">Keep current assignee</option>
                        {{-- Will be populated by JS or server-side if team_id is set --}}
                        @if($task->team)
                            @foreach($task->team->employees()->where('status', 'active')->get() as $emp)
                            @if($emp->id != $task->assigned_to)
                            <option value="{{ $emp->id }}" {{ old('assigned_to') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->full_name }}
                            </option>
                            @endif
                            @endforeach
                        @endif
                    </select>
                    <div id="employeeLoading" class="hidden text-[10px] text-blue-500 mt-1">
                        <i class="fas fa-spinner fa-spin mr-1"></i> Loading employees...
                    </div>
                    <p class="text-[10px] text-amber-600 mt-1">
                        <i class="fas fa-info-circle mr-1"></i>
                        Changing the assignee will trigger a reassignment notification to both employees.
                    </p>
                    @error('assigned_to')
                    <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                @endcan

                {{-- Priority --}}
                <div>
                    <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Priority <span class="text-red-500">*</span></label>
                    <select name="priority"
                        class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]"
                        required>
                        <option value="low"    {{ old('priority', $task->priority) == 'low'    ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ old('priority', $task->priority) == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high"   {{ old('priority', $task->priority) == 'high'   ? 'selected' : '' }}>High</option>
                        <option value="urgent" {{ old('priority', $task->priority) == 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                    @error('priority')
                    <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Status <span class="text-red-500">*</span></label>
                    <select name="status"
                        class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]"
                        required>
                        <option value="to_do"       {{ old('status', $task->status) == 'to_do'       ? 'selected' : '' }}>To Do</option>
                        <option value="in_progress" {{ old('status', $task->status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="on_hold"     {{ old('status', $task->status) == 'on_hold'     ? 'selected' : '' }}>On Hold</option>
                        <option value="completed"   {{ old('status', $task->status) == 'completed'   ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled"   {{ old('status', $task->status) == 'cancelled'   ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    @error('status')
                    <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Start Date --}}
                <div>
                    <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Start Date</label>
                    <input type="date" name="start_date" value="{{ old('start_date', $task->start_date?->format('Y-m-d')) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                    @error('start_date')
                    <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Due Date --}}
                <div>
                    <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Due Date</label>
                    <input type="date" name="due_date" value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]">
                    @error('due_date')
                    <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remarks --}}
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-[#2D2D2D] mb-1.5">Remarks</label>
                    <textarea name="remarks" rows="2"
                        class="w-full px-3 py-2 border border-gray-300 rounded text-xs text-[#2D2D2D] focus:outline-none focus:border-[#2D2D2D]"
                        placeholder="Enter any additional remarks">{{ old('remarks', $task->remarks) }}</textarea>
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

<script>
const teamSelect     = document.getElementById('team_id');
const employeeSelect = document.getElementById('assigned_to');
const loadingDiv     = document.getElementById('employeeLoading');
const currentTaskAssignedTo = {{ $task->assigned_to ?? 'null' }};

if (teamSelect && employeeSelect) {
    teamSelect.addEventListener('change', function () {
        const teamId = this.value;

        if (!teamId) {
            // Reset to server-rendered options
            location.reload();
            return;
        }

        loadingDiv.classList.remove('hidden');
        employeeSelect.disabled = true;
        employeeSelect.innerHTML = '<option value="">Loading...</option>';

        fetch(`{{ route('tasks.employees-by-team') }}?team_id=${teamId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(employees => {
            loadingDiv.classList.add('hidden');
            employeeSelect.innerHTML = '<option value="">Keep current assignee</option>';

            if (employees.length === 0) {
                employeeSelect.innerHTML = '<option value="">No active employees in this team</option>';
                employeeSelect.disabled = true;
                return;
            }

            employees.forEach(emp => {
                const opt = document.createElement('option');
                opt.value = emp.id;
                opt.textContent = emp.full_name;
                // Don't pre-select current assignee; this is for reassignment
                employeeSelect.appendChild(opt);
            });

            employeeSelect.disabled = false;
        })
        .catch(() => {
            loadingDiv.classList.add('hidden');
            employeeSelect.innerHTML = '<option value="">Failed to load employees</option>';
        });
    });
}
</script>
@endsection
