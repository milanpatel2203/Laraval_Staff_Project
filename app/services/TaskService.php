<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskHistory;
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

    public function logHistory(Task $task, User $user, string $action, ?string $oldValue = null, ?string $newValue = null, ?string $remarks = null): void
    {
        TaskHistory::create([
            'task_id' => $task->id,
            'performed_by' => $user->id,
            'action' => $action,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'remarks' => $remarks,
        ]);
    }

    public function changeStatus(Task $task, string $status, User $user, ?string $remarks = null): Task
    {
        $oldStatus = $task->status;

        $task->update(['status' => $status]);

        if ($status === 'completed') {
            $task->update(['completed_at' => now()]);
        }

        $this->logHistory($task, $user, 'Status Changed', $oldStatus, $status, $remarks);

        return $task;
    }

    public function reassign(Task $task, int $newEmployeeId, int $newTeamId, User $user, ?string $remarks = null): Task
    {
        $oldEmployee = $task->assignedEmployee->full_name ?? 'Unknown';
        $oldTeam = $task->team?->name ?? 'Unknown';

        $task->update([
            'assigned_to' => $newEmployeeId,
            'team_id' => $newTeamId,
        ]);

        $task->load('assignedEmployee', 'team');

        $newEmployee = $task->assignedEmployee->full_name;
        $newTeam = $task->team?->name;

        $this->logHistory(
            $task,
            $user,
            'Task Reassigned',
            "From: {$oldEmployee} ({$oldTeam})",
            "To: {$newEmployee} ({$newTeam})",
            $remarks
        );

        return $task;
    }

    public function getStatistics(User $user): array
    {
        $query = Task::forUser($user);

        return [
            'total' => (clone $query)->count(),
            'to_do' => (clone $query)->where('status', 'to_do')->count(),
            'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
            'on_hold' => (clone $query)->where('status', 'on_hold')->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
            'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
            'overdue' => (clone $query)->where('due_date', '<', now())
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->count(),
        ];
    }
}
