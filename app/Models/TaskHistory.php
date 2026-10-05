<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'performed_by',
        'action',
        'old_value',
        'new_value',
        'remarks',
        'old_assigned_to',
        'new_assigned_to',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function oldAssignedEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'old_assigned_to');
    }

    public function newAssignedEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'new_assigned_to');
    }
}
