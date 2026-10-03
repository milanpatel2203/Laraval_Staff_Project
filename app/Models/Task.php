<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_code',
        'title',
        'description',
        'created_by',
        'assigned_to',
        'team_id',
        'priority',
        'status',
        'start_date',
        'due_date',
        'completed_at',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(TaskHistory::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class);
    }

    public function isOverdue(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && ! in_array($this->status, ['completed', 'cancelled']);
    }

    public function scopeForUser($query, User $user)
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->role?->slug === 'hr-manager') {
            return $query->whereHas('assignedEmployee', function ($q) use ($user) {
                $q->where('team_id', $user->employee?->team_id);
            });
        }

        if ($user->role?->slug === 'manager') {
            return $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhereHas('assignedEmployee', function ($subQ) use ($user) {
                        $subQ->where('team_id', $user->employee?->team_id);
                    });
            });
        }

        if ($user->role?->slug === 'team-leader') {
            return $query->where('team_id', $user->employee?->team_id);
        }

        return $query->where('assigned_to', $user->employee?->id);
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['status'] ?? null, function ($query, $status) {
            $query->where('status', $status);
        })->when($filters['priority'] ?? null, function ($query, $priority) {
            $query->where('priority', $priority);
        })->when($filters['employee'] ?? null, function ($query, $employee) {
            $query->where('assigned_to', $employee);
        })->when($filters['team'] ?? null, function ($query, $team) {
            $query->where('team_id', $team);
        })->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('task_code', 'like', "%{$search}%")
                    ->orWhereHas('assignedEmployee', function ($subQ) use ($search) {
                        $subQ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        });
    }
}
