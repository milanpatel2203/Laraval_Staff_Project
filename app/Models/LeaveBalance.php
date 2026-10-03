<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'allocated',
        'used',
        'remaining',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function deduct(int $days): void
    {
        $this->used += $days;
        $this->remaining = max(0, $this->allocated - $this->used);
        $this->save();
    }

    public function restore(int $days): void
    {
        $this->used = max(0, $this->used - $days);
        $this->remaining = $this->allocated - $this->used;
        $this->save();
    }
}
