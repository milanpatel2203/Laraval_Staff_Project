<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Leave;
use App\Models\LeaveApproval;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LeaveApprovalService
{
    public const STAGE_TEAM_LEADER = 'team_leader';

    public const STAGE_SUPER_ADMIN = 'super_admin';

    public const STAGE_COMPLETED = 'completed';

    public const LEVEL_TEAM_LEADER = 1;

    public const LEVEL_SUPER_ADMIN = 2;

    public const REASON_SELF_REQUEST = 'team_leader_self_request';

    public const REASON_UNAVAILABLE = 'team_leader_unavailable';

    /*
    |--------------------------------------------------------------------------
    | User → Employee
    |--------------------------------------------------------------------------
    */

    public function employeeFor(User $user): ?Employee
    {
        return $user->employee;
    }

    /*
    |--------------------------------------------------------------------------
    | Super Admin
    |--------------------------------------------------------------------------
    */

    public function isSuperAdmin(User $user): bool
    {
        return $user->role?->slug === 'super-admin';
    }

    /*
    |--------------------------------------------------------------------------
    | Determine Initial Approver
    |--------------------------------------------------------------------------
    */

    public function determineInitialApprover(Employee $employee): array
    {
        $employee->loadMissing([
            'role',
            'team.teamLeader',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Manager → Super Admin
        |--------------------------------------------------------------------------
        */

        if ($employee->role?->slug === 'manager') {
            return $this->routeToSuperAdmin(
                self::REASON_SELF_REQUEST,
                $employee->id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Team Leader → Super Admin
        |--------------------------------------------------------------------------
        */

        $team = $employee->team;

        if ($team && $team->isLedBy($employee)) {
            return $this->routeToSuperAdmin(
                self::REASON_SELF_REQUEST,
                $employee->id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Staff → Their Team Leader
        |--------------------------------------------------------------------------
        */

        if (! $team || $team->status !== 'active') {
            throw ValidationException::withMessages([
                'team' => 'You must be assigned to an active team before applying for leave.',
            ]);
        }

        $leader = $team->teamLeader;

        if (! $leader) {
            throw ValidationException::withMessages([
                'team' => 'Your team does not have a Team Leader assigned. Please contact Admin.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Team Leader Unavailable → Super Admin
        |--------------------------------------------------------------------------
        */

        if ($this->isApproverOnLeave($leader)) {
            return $this->routeToSuperAdmin(
                self::REASON_UNAVAILABLE,
                $leader->id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Normal Staff → Team Leader
        |--------------------------------------------------------------------------
        */

        return [
            'stage' => self::STAGE_TEAM_LEADER,
            'approver_employee_id' => $leader->id,
            'escalation_reason' => null,
            'is_escalated' => false,
            'level' => self::LEVEL_TEAM_LEADER,
            'forwarded_from_employee_id' => null,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Check Approver Leave
    |--------------------------------------------------------------------------
    */

    public function isApproverOnLeave(?Employee $approver): bool
    {
        if (! $approver) {
            return false;
        }

        $today = now()->toDateString();

        return Leave::query()
            ->where('employee_id', $approver->id)
            ->where('status', 'approved')
            ->whereDate('from_date', '<=', $today)
            ->whereDate('to_date', '>=', $today)
            ->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | Route To Super Admin
    |--------------------------------------------------------------------------
    */

    public function routeToSuperAdmin(
        string $reason,
        ?int $forwardedFromEmployeeId = null
    ): array {
        return [
            'stage' => self::STAGE_SUPER_ADMIN,
            'approver_employee_id' => null,
            'escalation_reason' => $reason,
            'is_escalated' => $reason === self::REASON_UNAVAILABLE,
            'level' => self::LEVEL_SUPER_ADMIN,
            'forwarded_from_employee_id' => $forwardedFromEmployeeId,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Submit Leave
    |--------------------------------------------------------------------------
    */

    public function submit(Employee $employee, array $data): Leave
    {
        $routing = $this->determineInitialApprover($employee);

        return DB::transaction(function () use (
            $employee,
            $data,
            $routing
        ) {

            $leave = Leave::create([
                'employee_id' => $employee->id,
                'type' => $data['type'],
                'from_date' => $data['from_date'],
                'to_date' => $data['to_date'],
                'days' => $data['days'],
                'reason' => $data['reason'],
                'status' => 'pending',

                'current_approver_id' => $routing['approver_employee_id'],

                'approval_stage' => $routing['stage'],

                'escalation_reason' => $routing['escalation_reason'],

                'is_escalated' => $routing['is_escalated'],
            ]);

            /*
            |--------------------------------------------------------------------------
            | Staff → Team Leader
            |--------------------------------------------------------------------------
            |
            | Only create an approval history row when there is
            | an actual employee assigned to approve the leave.
            |
            */

            if ($routing['approver_employee_id']) {

                LeaveApproval::create([
                    'leave_id' => $leave->id,

                    // approver_id uses employee ID
                    'approver_id' => $routing['approver_employee_id'],

                    'approver_employee_id' => $routing['approver_employee_id'],

                    'level' => $routing['level'],

                    'status' => 'pending',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Manager / Team Leader → Super Admin
            |--------------------------------------------------------------------------
            |
            | No fake "forwarded" approval row is created.
            |
            | The leave itself contains:
            |
            | approval_stage = super_admin
            | current_approver_id = NULL
            |
            */

            return $leave;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Can Approve
    |--------------------------------------------------------------------------
    */

    public function canApprove(User $user, Leave $leave): bool
    {
        if ($leave->status !== 'pending') {
            return false;
        }

        if (! $user->hasPermission('leaves.approve')) {
            return false;
        }

        $actorEmployee = $this->employeeFor($user);

        if (! $actorEmployee) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Applicant cannot approve own leave
        |--------------------------------------------------------------------------
        */

        if (
            (int) $actorEmployee->id ===
            (int) $leave->employee_id
        ) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Super Admin Approval
        |--------------------------------------------------------------------------
        */

        if ($leave->approval_stage === self::STAGE_SUPER_ADMIN) {
            return $this->isSuperAdmin($user);
        }

        /*
        |--------------------------------------------------------------------------
        | Team Leader Approval
        |--------------------------------------------------------------------------
        */

        if ($leave->approval_stage === self::STAGE_TEAM_LEADER) {
            return
                (int) $leave->current_approver_id ===
                (int) $actorEmployee->id;
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Can Reject
    |--------------------------------------------------------------------------
    */

    public function canReject(User $user, Leave $leave): bool
    {
        if ($leave->status !== 'pending') {
            return false;
        }

        if (! $user->hasPermission('leaves.reject')) {
            return false;
        }

        $actorEmployee = $this->employeeFor($user);

        if (! $actorEmployee) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Applicant cannot reject own leave
        |--------------------------------------------------------------------------
        */

        if (
            (int) $actorEmployee->id ===
            (int) $leave->employee_id
        ) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        */

        if ($leave->approval_stage === self::STAGE_SUPER_ADMIN) {
            return $this->isSuperAdmin($user);
        }

        /*
        |--------------------------------------------------------------------------
        | Team Leader
        |--------------------------------------------------------------------------
        */

        if ($leave->approval_stage === self::STAGE_TEAM_LEADER) {
            return
                (int) $leave->current_approver_id ===
                (int) $actorEmployee->id;
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Approve
    |--------------------------------------------------------------------------
    */

    public function approve(
        User $user,
        Leave $leave,
        ?string $remarks = null
    ): Leave {

        return DB::transaction(function () use (
            $user,
            $leave,
            $remarks
        ) {

            $locked = Leave::query()
                ->whereKey($leave->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== 'pending') {
                throw new HttpException(
                    409,
                    'This leave request has already been processed.'
                );
            }

            if (! $this->canApprove($user, $locked)) {
                throw new HttpException(
                    403,
                    'You are not authorized to approve this leave request.'
                );
            }

            $this->recordAction(
                $user,
                $locked,
                'approved',
                $remarks
            );

            $locked->update([
                'status' => 'approved',
                'approval_stage' => self::STAGE_COMPLETED,
                'admin_remarks' => $remarks,
                'current_approver_id' => null,
            ]);

            return $locked->refresh();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Reject
    |--------------------------------------------------------------------------
    */

    public function reject(
        User $user,
        Leave $leave,
        string $remarks
    ): Leave {

        return DB::transaction(function () use (
            $user,
            $leave,
            $remarks
        ) {

            $locked = Leave::query()
                ->whereKey($leave->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== 'pending') {
                throw new HttpException(
                    409,
                    'This leave request has already been processed.'
                );
            }

            if (! $this->canReject($user, $locked)) {
                throw new HttpException(
                    403,
                    'You are not authorized to reject this leave request.'
                );
            }

            $this->recordAction(
                $user,
                $locked,
                'rejected',
                $remarks
            );

            $locked->update([
                'status' => 'rejected',
                'approval_stage' => self::STAGE_COMPLETED,
                'admin_remarks' => $remarks,
                'current_approver_id' => null,
            ]);

            return $locked->refresh();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Visible Leaves
    |--------------------------------------------------------------------------
    */

    public function visibleLeavesQuery(User $user): Builder
    {
        $query = Leave::query();

        /*
        |--------------------------------------------------------------------------
        | Super Admin → All Leaves
        |--------------------------------------------------------------------------
        */

        if (
            $this->isSuperAdmin($user)
            && $user->hasPermission('leaves.view')
        ) {
            return $query;
        }

        $employee = $this->employeeFor($user);

        if (! $employee) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $inner) use (
            $employee
        ) {

            /*
            | Own leaves
            */

            $inner->where(
                'employee_id',
                $employee->id
            )

            /*
            | Currently assigned leaves
            */
                ->orWhere(
                    'current_approver_id',
                    $employee->id
                )

            /*
            | Leaves already processed by this employee
            */
                ->orWhereHas(
                    'approvals',
                    function (Builder $approvalQuery) use ($employee) {

                        $approvalQuery
                            ->where(
                                'approver_employee_id',
                                $employee->id
                            )
                            ->whereIn('status', [
                                'approved',
                                'rejected',
                            ]);
                    }
                );
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Pending Assigned Count
    |--------------------------------------------------------------------------
    */

    public function pendingAssignedCount(User $user): int
    {
        if (
            ! $user->hasPermission('leaves.view')
            && ! $user->hasPermission('leaves.approve')
        ) {
            return 0;
        }

        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        */

        if ($this->isSuperAdmin($user)) {
            return Leave::query()
                ->where('status', 'pending')
                ->where(
                    'approval_stage',
                    self::STAGE_SUPER_ADMIN
                )
                ->count();
        }

        $employee = $this->employeeFor($user);

        if (! $employee) {
            return 0;
        }

        return Leave::query()
            ->where('status', 'pending')
            ->where(
                'current_approver_id',
                $employee->id
            )
            ->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Pending Assigned Query
    |--------------------------------------------------------------------------
    */

    public function pendingAssignedQuery(User $user): Builder
    {
        $query = Leave::query()
            ->where('status', 'pending');

        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        */

        if ($this->isSuperAdmin($user)) {
            return $query->where(
                'approval_stage',
                self::STAGE_SUPER_ADMIN
            );
        }

        $employee = $this->employeeFor($user);

        if (! $employee) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(
            'current_approver_id',
            $employee->id
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Assert Visible
    |--------------------------------------------------------------------------
    */

    public function assertVisible(
        User $user,
        Leave $leave
    ): void {

        $visible = $this->visibleLeavesQuery($user)
            ->whereKey($leave->id)
            ->exists();

        if (! $visible) {
            throw new HttpException(
                403,
                'You are not authorized to view this leave request.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Record Approval Action
    |--------------------------------------------------------------------------
    */

    private function recordAction(
        User $user,
        Leave $leave,
        string $action,
        ?string $remarks
    ): void {

        /*
        |--------------------------------------------------------------------------
        | Existing Team Leader approval
        |--------------------------------------------------------------------------
        */

        $pending = $leave->approvals()
            ->where('status', 'pending')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        $employee = $this->employeeFor($user);

        if ($pending) {

            $pending->update([
                'approver_id' => $employee?->id
                    ?? $pending->approver_id,

                'approver_employee_id' => $employee?->id
                    ?? $pending->approver_employee_id,

                'status' => $action,

                'remarks' => $remarks,

                'actioned_at' => now(),
            ]);

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Super Admin approval
        |--------------------------------------------------------------------------
        |
        | If Super Admin has an employee record, save it.
        | Otherwise approver_id remains NULL.
        |
        */

        LeaveApproval::create([
            'leave_id' => $leave->id,

            'approver_id' => $employee?->id,

            'approver_employee_id' => $employee?->id,

            'level' => $leave->approval_stage === self::STAGE_SUPER_ADMIN
                    ? self::LEVEL_SUPER_ADMIN
                    : self::LEVEL_TEAM_LEADER,

            'status' => $action,

            'remarks' => $remarks,

            'actioned_at' => now(),
        ]);
    }
}
