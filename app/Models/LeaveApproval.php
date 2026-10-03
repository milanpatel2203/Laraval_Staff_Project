<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveApproval extends Model
{
    protected $fillable = [
        'leave_id',
        'approver_id',
        'approver_employee_id',
        'level',
        'status',
        'remarks',
        'actioned_at',
    ];

    protected $casts = [
        'actioned_at' => 'datetime',
        'level' => 'integer',
    ];

    public function leave(): BelongsTo
    {
        return $this->belongsTo(Leave::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function approverEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approver_employee_id');
    }
}
