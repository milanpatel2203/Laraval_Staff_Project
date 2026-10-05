<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function role()
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

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
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
        if ($this->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->avatar)) {
            return asset('storage/' . $this->avatar);
        }

        // 2. Avatar from linked user
        $linkedUser = $this->linked_user;
        if ($linkedUser && $linkedUser->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($linkedUser->avatar)) {
            return asset('storage/' . $linkedUser->avatar);
        }

        return null;
    }
}
