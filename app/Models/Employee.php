<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        // 1. Direct email match
        $user = User::where('email', $this->email)->first();
        if ($user)
            return $user;

        $emailLower = strtolower($this->email);

        // 2. Superadmin / Keval accounts
        if (in_array($emailLower, ['keval@uesthrms.com', 'keval192837@gmail.com']) || $this->employee_code === 'EMP-001') {
            $super = User::whereIn('email', ['keval192837@gmail.com', 'admin@uest.com', 'admin@uesthrms.com', 'keval@uesthrms.com'])->first();
            if ($super)
                return $super;
            $anySuper = User::where('role_title', 'Super Administrator')->first();
            if ($anySuper)
                return $anySuper;
        }

        // 3. Known role accounts
        if (in_array($emailLower, ['vikram.singh@uesthrms.com', 'staff@uesthrms.com'])) {
            return User::where('email', 'vikram.singh@uesthrms.com')->orWhere('email', 'staff@uesthrms.com')->first();
        }
        if (in_array($emailLower, ['priya.patel@uesthrms.com', 'hr@uesthrms.com'])) {
            return User::where('email', 'priya.patel@uesthrms.com')->orWhere('email', 'hr@uesthrms.com')->first();
        }
        if (in_array($emailLower, ['amit.kumar@uesthrms.com', 'manager@uesthrms.com'])) {
            return User::where('email', 'amit.kumar@uesthrms.com')->orWhere('email', 'manager@uesthrms.com')->first();
        }

        // 4. Fuzzy match by name
        if ($this->first_name) {
            $query = User::where('name', 'like', $this->first_name . '%');
            if ($this->last_name) {
                $prefix = substr($this->last_name, 0, min(4, strlen($this->last_name)));
                $query->where('name', 'like', '%' . $prefix . '%');
            }
            $match = $query->first();
            if ($match)
                return $match;
        }

        return null;
    }

    public function getAvatarUrlAttribute(): ?string
    {
        // 1. Direct employee avatar column
        if ($this->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->avatar)) {
            return asset('storage/' . $this->avatar);
        }

        // 2. Avatar from linked user
        $linkedUser = $this->linked_user;
        if ($linkedUser && $linkedUser->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($linkedUser->avatar)) {
            return asset('storage/' . $linkedUser->avatar);
        }

        // 3. Fallback for Super Admin (EMP-001 or Keval)
        if ($this->employee_code === 'EMP-001' || in_array(strtolower($this->email), ['keval@uesthrms.com', 'keval192837@gmail.com'])) {
            $adminWithAvatar = User::where('role_title', 'Super Administrator')->whereNotNull('avatar')->first();
            if ($adminWithAvatar && $adminWithAvatar->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($adminWithAvatar->avatar)) {
                return asset('storage/' . $adminWithAvatar->avatar);
            }
        }

        return null;
    }
}
