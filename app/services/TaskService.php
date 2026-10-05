<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Task;
use App\Models\TaskHistory;
use App\Models\TaskNotification;
use App\Models\User;

class TaskService
{
    public function generateTaskCode(): string
    {
        $lastTask = Task::orderBy('id', 'desc')->first();
        $lastNumber = $lastTask ? (int) str_replace('TASK-', '', $lastTask->task_code) : 0;
        $newNumber = $lastNumber + 1;

        return 'TASK-'.str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Log a task history entry.
     */
    public function logHistory(
        Task $task,
        User $user,
        string $action,
        ?string $oldValue = null,
        ?string $newValue = null,
        ?string $remarks = null,
        ?int $oldAssignedTo = null,
        ?int $newAssignedTo = null
    ): void {
        TaskHistory::create([
            'task_id'         => $task->id,
            'performed_by'    => $user->id,
            'action'          => $action,
            'old_value'       => $oldValue,
            'new_value'       => $newValue,
            'remarks'         => $remarks,
            'old_assigned_to' => $oldAssignedTo,
            'new_assigned_to' => $newAssignedTo,
        ]);
    }

    /**
     * Handle initial task assignment when creating a task.
     * Note: assigned_to and assigned_date are already set via Task::create(),
     * so we only need to log history and notify.
     */
    public function assignTask(Task $task, Employee $employee, User $assignedBy): void
    {
        // Log history
        $this->logHistory(
            $task,
            $assignedBy,
            'Task Assigned',
            null,
            $employee->full_name,
            null,
            null,
            $employee->id
        );

        // Notify the assigned employee (via their user account)
        $this->notifyEmployee(
            $employee,
            $task,
            'assigned',
            "{$task->task_code} has been assigned to you by {$assignedBy->name}."
        );
    }

    /**
     * Handle task reassignment: save old/new employee, notify both, log history.
     */
    public function reassign(Task $task, int $newEmployeeId, int $newTeamId, User $reassignedBy, ?string $remarks = null): Task
    {
        $oldEmployee = $task->assignedEmployee;
        $oldEmployeeId = $task->assigned_to;

        $newEmployee = Employee::findOrFail($newEmployeeId);

        // Update the task
        $task->update([
            'assigned_to'          => $newEmployeeId,
            'team_id'              => $newTeamId,
            'last_reassigned_by'   => $reassignedBy->id,
            'last_reassigned_at'   => now(),
            'assigned_date'        => now()->toDateString(),
        ]);

        $task->load('assignedEmployee', 'team');

        // Log history with old/new employee IDs
        $this->logHistory(
            $task,
            $reassignedBy,
            'Task Reassigned',
            $oldEmployee ? $oldEmployee->full_name : 'Unassigned',
            $newEmployee->full_name,
            $remarks,
            $oldEmployeeId,
            $newEmployeeId
        );

        // Notify old employee: task removed from them
        if ($oldEmployee && $oldEmployeeId !== $newEmployeeId) {
            $this->notifyEmployee(
                $oldEmployee,
                $task,
                'reassigned_from',
                "{$task->task_code} has been reassigned from you to {$newEmployee->full_name} by {$reassignedBy->name}."
            );
        }

        // Notify new employee: task assigned to them
        $this->notifyEmployee(
            $newEmployee,
            $task,
            'reassigned_to',
            "{$task->task_code} has been assigned to you by {$reassignedBy->name}."
        );

        return $task;
    }

    /**
     * Change task status and log history.
     */
    public function changeStatus(Task $task, string $status, User $user, ?string $remarks = null): Task
    {
        $oldStatus = $task->status;

        $updates = ['status' => $status];
        if ($status === 'completed') {
            $updates['completed_at'] = now();
        }

        $task->update($updates);

        $this->logHistory($task, $user, 'Status Changed', $oldStatus, $status, $remarks);

        return $task;
    }

    /**
     * Get task statistics for a user.
     */
    public function getStatistics(User $user): array
    {
        $query = Task::forUser($user);

        return [
            'total'       => (clone $query)->count(),
            'to_do'       => (clone $query)->where('status', 'to_do')->count(),
            'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
            'on_hold'     => (clone $query)->where('status', 'on_hold')->count(),
            'completed'   => (clone $query)->where('status', 'completed')->count(),
            'cancelled'   => (clone $query)->where('status', 'cancelled')->count(),
            'overdue'     => (clone $query)->where('due_date', '<', now())
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->count(),
        ];
    }

    /**
     * Send an in-app notification to an employee (via their linked user account).
     */
    private function notifyEmployee(Employee $employee, Task $task, string $type, string $message): void
    {
        $user = $employee->user;

        if ($user) {
            TaskNotification::create([
                'user_id' => $user->id,
                'task_id' => $task->id,
                'type'    => $type,
                'message' => $message,
            ]);
        }
    }
}
