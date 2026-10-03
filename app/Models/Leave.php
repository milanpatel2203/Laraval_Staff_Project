<?php

namespace App\Models;

use App\Services\LeaveApprovalService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Leave extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'type',
        'leave_type_id',
        'from_date',
        'to_date',
        'days',
        'reason',
        'status',
        'current_approver_id',
        'approval_stage',
        'escalation_reason',
        'is_escalated',
        'admin_remarks',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'is_escalated' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function currentApprover(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'current_approver_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(LeaveApproval::class)->orderBy('id');
    }

    public function canBeActedOnBy(User $user): bool
    {
        $service = app(LeaveApprovalService::class);

        return $service->canApprove($user, $this) || $service->canReject($user, $this);
    }

    public function stageLabel(): string
    {
        return match ($this->approval_stage) {
            LeaveApprovalService::STAGE_TEAM_LEADER => 'Pending Team Leader',
            LeaveApprovalService::STAGE_SUPER_ADMIN => $this->is_escalated
                ? 'Escalated to Super Admin'
                : 'Pending Super Admin',
            LeaveApprovalService::STAGE_COMPLETED => 'Completed',
            default => ucfirst((string) $this->status),
        };
    }
}
