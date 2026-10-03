<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('tasks.view');
    }

    public function view(User $user, Task $task): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->role?->slug === 'staff') {
            return $task->assigned_to === $user->employee?->id;
        }

        if ($user->role?->slug === 'team-leader') {
            return $task->team_id === $user->employee?->team_id;
        }

        if ($user->role?->slug === 'manager') {
            return $task->created_by === $user->id
                || $task->team_id === $user->employee?->team_id;
        }

        return $user->hasPermission('tasks.view');
    }

    public function create(User $user): bool
    {
        if ($user->role?->slug === 'staff') {
            return false;
        }

        return $user->hasPermission('tasks.create');
    }

    public function update(User $user, Task $task): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->role?->slug === 'staff') {
            return $task->assigned_to === $user->employee?->id;
        }

        if ($user->role?->slug === 'team-leader') {
            return $task->team_id === $user->employee?->team_id;
        }

        if ($user->role?->slug === 'manager') {
            return $task->created_by === $user->id
                || $task->team_id === $user->employee?->team_id;
        }

        return $user->hasPermission('tasks.edit');
    }

    public function delete(User $user, Task $task): bool
    {
        if ($user->role?->slug === 'staff') {
            return false;
        }

        return $user->hasPermission('tasks.delete');
    }

    public function assign(User $user, Task $task): bool
    {
        if ($user->role?->slug === 'staff') {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->role?->slug === 'team-leader') {
            return $task->team_id === $user->employee?->team_id;
        }

        return $user->hasPermission('tasks.assign');
    }

    public function reassign(User $user, Task $task): bool
    {
        if ($user->role?->slug === 'staff') {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->role?->slug === 'team-leader') {
            return $task->team_id === $user->employee?->team_id;
        }

        return $user->hasPermission('tasks.reassign');
    }

    public function changeStatus(User $user, Task $task): bool
    {
        return $this->update($user, $task);
    }

    public function complete(User $user, Task $task): bool
    {
        return $this->update($user, $task);
    }

    public function cancel(User $user, Task $task): bool
    {
        if ($user->role?->slug === 'staff') {
            return false;
        }

        return $this->update($user, $task);
    }
}
