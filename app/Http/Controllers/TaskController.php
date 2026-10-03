<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\Team;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function __construct(
        private TaskService $taskService
    ) {}

    public function index(Request $request)
    {
        $query = Task::with(['creator', 'assignedEmployee', 'team'])
            ->forUser(Auth::user())
            ->filter($request->only(['status', 'priority', 'employee', 'team', 'search']))
            ->latest();

        $tasks = $query->paginate(15);

        $employees = Employee::with('team')->where('status', 'active')->orderBy('first_name')->get();
        $teams = Team::where('status', 'active')->orderBy('name')->get();

        $statistics = $this->taskService->getStatistics(Auth::user());

        return view('tasks.index', compact('tasks', 'employees', 'teams', 'statistics'));
    }

    public function create()
    {
        $this->authorize('create', Task::class);

        $user = Auth::user();
        $employees = $this->getAvailableEmployees($user);
        $teams = Team::where('status', 'active')->orderBy('name')->get();

        return view('tasks.create', compact('employees', 'teams'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Task::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:employees,id',
            'team_id' => 'nullable|exists:teams,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'remarks' => 'nullable|string',
        ]);

        $employee = Employee::findOrFail($validated['assigned_to']);
        $validated['team_id'] = $employee->team_id;
        $validated['task_code'] = $this->taskService->generateTaskCode();
        $validated['created_by'] = Auth::id();

        $task = Task::create($validated);

        $this->taskService->logHistory(
            $task,
            Auth::user(),
            'Task Created',
            null,
            null,
            $validated['remarks']
        );

        $this->taskService->logHistory(
            $task,
            Auth::user(),
            'Task Assigned',
            null,
            $employee->full_name
        );

        return redirect()->route('tasks.index')->with('success', 'Task created successfully.');
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);

        $task->load(['creator', 'assignedEmployee', 'team', 'histories.performer', 'attachments.uploader']);

        $latestHistory = $task->histories()->where('action', '!=', 'Task Created')->latest()->first();

        return view('tasks.show', compact('task', 'latestHistory'));
    }

    public function edit(Task $task)
    {
        $this->authorize('update', $task);

        $user = Auth::user();

        if ($user->role?->slug === 'staff') {
            return redirect()->route('tasks.show', $task)->with('info', 'You can update your task status directly from the task details page.');
        }

        $employees = $this->getAvailableEmployees($user);
        $teams = Team::where('status', 'active')->orderBy('name')->get();

        return view('tasks.edit', compact('task', 'employees', 'teams'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $user = Auth::user();
        $isStaff = $user->role?->slug === 'staff';

        if ($isStaff) {
            $validated = $request->validate([
                'status' => 'required|in:to_do,in_progress,on_hold,completed,cancelled',
            ]);

            $oldStatus = $task->status;

            if ($oldStatus !== $validated['status']) {
                $task->update($validated);

                $this->taskService->logHistory(
                    $task,
                    $user,
                    'Status Changed',
                    $oldStatus,
                    $validated['status']
                );

                return redirect()->back()->with('success', 'Task status updated successfully.');
            }

            return redirect()->back();
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:employees,id',
            'team_id' => 'nullable|exists:teams,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:to_do,in_progress,on_hold,completed,cancelled',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'remarks' => 'nullable|string',
        ]);

        $oldValues = $task->only(['title', 'description', 'priority', 'status', 'start_date', 'due_date', 'remarks']);

        if (isset($validated['assigned_to'])) {
            $employee = Employee::findOrFail($validated['assigned_to']);
            $validated['team_id'] = $employee->team_id;
        }

        $task->update($validated);

        $this->taskService->logHistory(
            $task,
            Auth::user(),
            'Task Updated',
            json_encode($oldValues),
            json_encode($validated)
        );

        return redirect()->route('tasks.show', $task)->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
    }

    public function start(Task $task)
    {
        $this->authorize('changeStatus', $task);

        $this->taskService->changeStatus($task, 'in_progress', Auth::user(), 'Task started by employee');

        return redirect()->route('tasks.show', $task)->with('success', 'Task started successfully.');
    }

    public function complete(Task $task)
    {
        $this->authorize('complete', $task);

        $this->taskService->changeStatus($task, 'completed', Auth::user(), 'Task marked as completed');

        return redirect()->route('tasks.show', $task)->with('success', 'Task completed successfully.');
    }

    public function cancel(Request $request, Task $task)
    {
        $this->authorize('cancel', $task);

        $validated = $request->validate([
            'remarks' => 'nullable|string',
        ]);

        $this->taskService->changeStatus($task, 'cancelled', Auth::user(), $validated['remarks']);

        return redirect()->route('tasks.show', $task)->with('success', 'Task cancelled successfully.');
    }

    public function reassign(Request $request, Task $task)
    {
        $this->authorize('reassign', $task);

        $validated = $request->validate([
            'assigned_to' => 'required|exists:employees,id',
            'remarks' => 'nullable|string',
        ]);

        $employee = Employee::findOrFail($validated['assigned_to']);

        $this->taskService->reassign(
            $task,
            $employee->id,
            $employee->team_id,
            Auth::user(),
            $validated['remarks']
        );

        return redirect()->route('tasks.show', $task)->with('success', 'Task reassigned successfully.');
    }

    public function uploadAttachment(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
        ]);

        $file = $validated['file'];
        $fileName = time().'_'.$file->getClientOriginalName();
        $filePath = $file->storeAs('task_attachments', $fileName, 'public');

        $task->attachments()->create([
            'uploaded_by' => Auth::id(),
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ]);

        $this->taskService->logHistory(
            $task,
            Auth::user(),
            'Attachment Added',
            null,
            $file->getClientOriginalName()
        );

        return redirect()->route('tasks.show', $task)->with('success', 'Attachment uploaded successfully.');
    }

    public function downloadAttachment(TaskAttachment $attachment)
    {
        $this->authorize('view', $attachment->task);

        return response()->download(storage_path('app/public/'.$attachment->file_path), $attachment->file_name);
    }

    private function getAvailableEmployees(User $user)
    {
        if ($user->isSuperAdmin() || $user->role?->slug === 'hr-manager' || $user->role?->slug === 'manager') {
            return Employee::where('status', 'active')->orderBy('first_name')->get();
        }

        if ($user->role?->slug === 'team-leader') {
            return Employee::where('team_id', $user->employee?->team_id)
                ->where('status', 'active')
                ->orderBy('first_name')
                ->get();
        }

        return collect([]);
    }
}
