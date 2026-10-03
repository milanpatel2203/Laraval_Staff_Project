<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Role;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_tasks_index(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('tasks.index'));

        $response->assertStatus(403);
    }

    public function test_user_with_permission_can_view_tasks_index(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['slug' => 'super-admin']);
        $user->role_id = $role->id;
        $user->save();

        $this->actingAs($user);

        $response = $this->get(route('tasks.index'));

        $response->assertStatus(200);
    }

    public function test_task_code_is_generated_automatically(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['slug' => 'super-admin']);
        $user->role_id = $role->id;
        $user->save();

        $employee = Employee::factory()->create();
        $team = Team::factory()->create();
        $employee->team_id = $team->id;
        $employee->save();

        $this->actingAs($user);

        $response = $this->post(route('tasks.store'), [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'assigned_to' => $employee->id,
            'priority' => 'medium',
        ]);

        $this->assertDatabaseHas('tasks', [
            'task_code' => 'TASK-001',
            'title' => 'Test Task',
        ]);
    }

    public function test_task_history_is_logged_on_creation(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['slug' => 'super-admin']);
        $user->role_id = $role->id;
        $user->save();

        $employee = Employee::factory()->create();
        $team = Team::factory()->create();
        $employee->team_id = $team->id;
        $employee->save();

        $this->actingAs($user);

        $this->post(route('tasks.store'), [
            'title' => 'Test Task',
            'assigned_to' => $employee->id,
            'priority' => 'medium',
        ]);

        $task = Task::first();

        $this->assertDatabaseHas('task_histories', [
            'task_id' => $task->id,
            'action' => 'Task Created',
        ]);

        $this->assertDatabaseHas('task_histories', [
            'task_id' => $task->id,
            'action' => 'Task Assigned',
        ]);
    }
}
