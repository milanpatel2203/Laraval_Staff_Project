<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_code',
        'first_name',
        'last_name',
        'email',
        'phone',
        'department_id',
        'team_id',
        'role_id',
        'designation',
        'joining_date',
        'salary',
        'status',
        'address',
        'avatar',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'salary' => 'decimal:2',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function ledTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'team_leader_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function user()
    {
        return $this->hasOne(User::class, 'email', 'email');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getInitialsAttribute(): string
    {
        $first = strtoupper(substr($this->first_name ?? '', 0, 1));
        $last = strtoupper(substr($this->last_name ?? '', 0, 1));
        return ($first || $last) ? ($first . $last) : 'EM';
    }

    public function getLinkedUserAttribute(): ?User
    {
        // 1. Direct email match (case-insensitive)
        if (!empty($this->email)) {
            $user = User::whereRaw('LOWER(email) = ?', [strtolower(trim($this->email))])->first();
            if ($user) {
                return $user;
            }
        }

        // 2. Direct phone / mobile match
        if (!empty($this->phone)) {
            $user = User::where('mobile', trim($this->phone))->orWhere('phone', trim($this->phone))->first();
            if ($user) {
                return $user;
            }
        }

        // 3. Exact full name match
        $fullName = trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
        if (!empty($fullName)) {
            $user = User::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($fullName)])->first();
            if ($user) {
                return $user;
            }
        }

        return null;
    }

    public function getAvatarUrlAttribute(): ?string
    {
        // 1. Direct employee avatar column
        if ($this->avatar && Storage::disk('public')->exists($this->avatar)) {
            return asset('storage/' . $this->avatar);
        }

        // 2. Avatar from linked user
        $linkedUser = $this->linked_user;
        if ($linkedUser && $linkedUser->avatar && Storage::disk('public')->exists($linkedUser->avatar)) {
            return asset('storage/' . $linkedUser->avatar);
        }

        return null;
    }
}
