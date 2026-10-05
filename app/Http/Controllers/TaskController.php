<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskNotification;
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

    /*
    |--------------------------------------------------------------------------
    | Task List (Index)
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = Task::with(['creator', 'assignedEmployee', 'team'])
            ->forUser(Auth::user())
            ->filter($request->only(['status', 'priority', 'employee', 'team', 'search']))
            ->latest();

        $tasks = $query->paginate(15);

        $employees = Employee::with('team')->where('status', 'active')->orderBy('first_name')->get();
        $teams     = Team::where('status', 'active')->orderBy('name')->get();

        $statistics = $this->taskService->getStatistics(Auth::user());

        // Unread notifications for current user
        $unreadNotifications = TaskNotification::with('task')
            ->forUser(Auth::id())
            ->unread()
            ->latest()
            ->take(10)
            ->get();

        return view('tasks.index', compact('tasks', 'employees', 'teams', 'statistics', 'unreadNotifications'));
    }

    /*
    |--------------------------------------------------------------------------
    | Create Task
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $this->authorize('create', Task::class);

        $user      = Auth::user();
        $employees = $this->getAvailableEmployees($user);
        $teams     = Team::where('status', 'active')->orderBy('name')->get();

        return view('tasks.create', compact('employees', 'teams'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Task::class);

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'team_id'     => 'required|exists:teams,id',
            'assigned_to' => 'required|exists:employees,id',
            'priority'    => 'required|in:low,medium,high,urgent',
            'start_date'  => 'nullable|date',
            'due_date'    => 'nullable|date|after_or_equal:start_date',
            'remarks'     => 'nullable|string',
        ]);

        // Validate the employee belongs to the selected team
        $employee = Employee::findOrFail($validated['assigned_to']);
        if ($employee->team_id != $validated['team_id']) {
            return back()
                ->withErrors(['assigned_to' => 'The selected employee does not belong to the selected team.'])
                ->withInput();
        }

        $validated['task_code']    = $this->taskService->generateTaskCode();
        $validated['created_by']   = Auth::id();
        $validated['assigned_date'] = now()->toDateString();

        $task = Task::create($validated);

        // Log task creation history
        $this->taskService->logHistory(
            $task,
            Auth::user(),
            'Task Created',
            null,
            null,
            $validated['remarks'] ?? null,
            null,
            $employee->id
        );

        // Assign the task and notify employee
        $this->taskService->assignTask($task, $employee, Auth::user());

        return redirect()->route('tasks.index')->with('success', 'Task created and assigned successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Show Task
    |--------------------------------------------------------------------------
    */

    public function show(Task $task)
    {
        $this->authorize('view', $task);

        $task->load([
            'creator',
            'assignedEmployee',
            'team',
            'histories.performer',
            'histories.oldAssignedEmployee',
            'histories.newAssignedEmployee',
            'attachments.uploader',
            'lastReassignedBy',
        ]);

        // Mark notifications as read for this task
        TaskNotification::where('user_id', Auth::id())
            ->where('task_id', $task->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // Get assignment-specific histories for the Assignment History section
        $assignmentHistories = $task->histories
            ->whereIn('action', ['Task Assigned', 'Task Reassigned'])
            ->sortByDesc('created_at');

        $latestHistory = $task->histories()
            ->where('action', '!=', 'Task Created')
            ->latest()
            ->first();

        // Available employees for reassignment (from the task's team)
        $teamEmployees = collect();
        if ($task->team) {
            $teamEmployees = $task->team->employees()->where('status', 'active')->get();
        }

        return view('tasks.show', compact('task', 'latestHistory', 'assignmentHistories', 'teamEmployees'));
    }

    /*
    |--------------------------------------------------------------------------
    | Edit Task
    |--------------------------------------------------------------------------
    */

    public function edit(Task $task)
    {
        $this->authorize('update', $task);

        $user = Auth::user();

        // Staff can only update status/remarks from the show page
        if ($user->role?->slug === 'staff') {
            return redirect()->route('tasks.show', $task)
                ->with('info', 'You can update your task status directly from the task details page.');
        }

        $employees = $this->getAvailableEmployees($user);
        $teams     = Team::where('status', 'active')->orderBy('name')->get();

        return view('tasks.edit', compact('task', 'employees', 'teams'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $user    = Auth::user();
        $isStaff = $user->role?->slug === 'staff';

        if ($isStaff) {
            // Staff can only update status and remarks
            $validated = $request->validate([
                'status'  => 'required|in:to_do,in_progress,on_hold,completed,cancelled',
                'remarks' => 'nullable|string',
            ]);

            $oldStatus = $task->status;

            $updates = [];
            if ($oldStatus !== $validated['status']) {
                $updates['status'] = $validated['status'];
                if ($validated['status'] === 'completed') {
                    $updates['completed_at'] = now();
                }
            }

            if (isset($validated['remarks'])) {
                $updates['remarks'] = $validated['remarks'];
            }

            if (! empty($updates)) {
                $task->update($updates);

                if ($oldStatus !== $validated['status']) {
                    $this->taskService->logHistory(
                        $task,
                        $user,
                        'Status Changed',
                        $oldStatus,
                        $validated['status']
                    );
                }

                return redirect()->back()->with('success', 'Task updated successfully.');
            }

            return redirect()->back();
        }

        // Admin / HR / Manager / Team Leader
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'team_id'     => 'nullable|exists:teams,id',
            'assigned_to' => 'nullable|exists:employees,id',
            'priority'    => 'required|in:low,medium,high,urgent',
            'status'      => 'required|in:to_do,in_progress,on_hold,completed,cancelled',
            'start_date'  => 'nullable|date',
            'due_date'    => 'nullable|date|after_or_equal:start_date',
            'remarks'     => 'nullable|string',
        ]);

        $oldValues = $task->only(['title', 'description', 'priority', 'status', 'start_date', 'due_date', 'remarks']);

        // If assigned_to changed → treat as reassignment
        if (isset($validated['assigned_to']) && $validated['assigned_to'] != $task->assigned_to) {
            $newEmployee = Employee::findOrFail($validated['assigned_to']);
            $validated['team_id'] = $newEmployee->team_id;

            $this->taskService->reassign(
                $task,
                $newEmployee->id,
                $newEmployee->team_id,
                Auth::user(),
                $validated['remarks'] ?? null
            );

            // Remove assigned_to from the validated array so we don't double-update
            unset($validated['assigned_to']);
            unset($validated['team_id']);
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

    /*
    |--------------------------------------------------------------------------
    | Delete Task
    |--------------------------------------------------------------------------
    */

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Status Actions
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Reassign (from show page form)
    |--------------------------------------------------------------------------
    */

    public function reassign(Request $request, Task $task)
    {
        $this->authorize('reassign', $task);

        $validated = $request->validate([
            'assigned_to' => 'required|exists:employees,id',
            'remarks'     => 'nullable|string',
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

    /*
    |--------------------------------------------------------------------------
    | AJAX: Get Employees by Team
    |--------------------------------------------------------------------------
    */

    public function getEmployeesByTeam(Request $request)
    {
        $teamId = $request->input('team_id');

        if (! $teamId) {
            return response()->json([]);
        }

        $employees = Employee::where('team_id', $teamId)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name']);

        return response()->json($employees->map(fn ($e) => [
            'id'        => $e->id,
            'full_name' => $e->first_name . ' ' . $e->last_name,
        ]));
    }

    /*
    |--------------------------------------------------------------------------
    | Notifications: Mark All as Read
    |--------------------------------------------------------------------------
    */

    public function markNotificationsRead()
    {
        TaskNotification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return redirect()->back()->with('success', 'Notifications marked as read.');
    }

    /*
    |--------------------------------------------------------------------------
    | Attachments
    |--------------------------------------------------------------------------
    */

    public function uploadAttachment(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
        ]);

        $file     = $validated['file'];
        $fileName = time().'_'.$file->getClientOriginalName();
        $filePath = $file->storeAs('task_attachments', $fileName, 'public');

        $task->attachments()->create([
            'uploaded_by' => Auth::id(),
            'file_name'   => $file->getClientOriginalName(),
            'file_path'   => $filePath,
            'file_type'   => $file->getClientMimeType(),
            'file_size'   => $file->getSize(),
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

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function getAvailableEmployees(User $user)
    {
        if ($user->isSuperAdmin() || in_array($user->role?->slug, ['hr-manager', 'manager'])) {
            return Employee::with('team')->where('status', 'active')->orderBy('first_name')->get();
        }

        if ($user->role?->slug === 'team-leader') {
            return Employee::with('team')
                ->where('team_id', $user->employee?->team_id)
                ->where('status', 'active')
                ->orderBy('first_name')
                ->get();
        }

        return collect([]);
    }
}
