<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'mobile', 'phone', 'role_title', 'role_id', 'theme', 'bio', 'avatar', 'password'])]
#[Hidden(['password', 'remember_token'])]

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'mobile',
        'phone',
        'role_title',
        'role_id',
        'theme',
        'bio',
        'avatar',
        'password',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role_title === 'Super Administrator' || ($this->role && $this->role->slug === 'super-admin');
    }

    public function isHRManager(): bool
    {
        return ($this->role && $this->role->slug === 'hr-manager') || str_contains(strtolower($this->role_title ?? ''), 'hr manager');
    }

    public function isDepartmentManager(): bool
    {
        return ($this->role && $this->role->slug === 'department-manager') || str_contains(strtolower($this->role_title ?? ''), 'department manager');
    }

    public function isStaff(): bool
    {
        return ($this->role && $this->role->slug === 'employee') || str_contains(strtolower($this->role_title ?? ''), 'staff') || str_contains(strtolower($this->role_title ?? ''), 'employee');
    }

    public function hasPermission(string $slug): bool
    {
        if ($this->role) {
            // Safety fallback so Super Admin can always access roles & settings management
            if ($this->isSuperAdmin() && in_array($slug, ['roles.manage', 'settings.manage'])) {
                return true;
            }
            return $this->role->hasPermission($slug);
        }
        return $this->isSuperAdmin();
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'otp_expires_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Company Relationship
    |--------------------------------------------------------------------------
    */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Role Relationship
    |--------------------------------------------------------------------------
    */

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'email', 'email');
    }

    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role?->slug === 'super-admin';
    }

    /*
    |--------------------------------------------------------------------------
    | User Initials
    |--------------------------------------------------------------------------
    */

    public function getInitialsAttribute(): string
    {
        $words = array_values(
            array_filter(
                explode(' ', trim($this->name))
            )
        );

        if (empty($words)) {
            return 'AD';
        }

        if (count($words) === 1) {
            return strtoupper(
                substr(
                    $words[0],
                    0,
                    min(2, strlen($words[0]))
                )
            );
        }

        return strtoupper(
            substr($words[0], 0, 1) .
            substr($words[count($words) - 1], 0, 1)
        );
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->avatar)) {
            return asset('storage/' . $this->avatar);
        }

        if ($this->linked_employee && $this->linked_employee->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->linked_employee->avatar)) {
            return asset('storage/' . $this->linked_employee->avatar);
        }

        // If Super Admin, check if another Super Admin account has an avatar or if EMP-001 has an avatar
        if ($this->isSuperAdmin()) {
            $superWithAvatar = self::where('role_title', 'Super Administrator')
                ->whereNotNull('avatar')
                ->where('avatar', '!=', '')
                ->first();
            if ($superWithAvatar && $superWithAvatar->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($superWithAvatar->avatar)) {
                return asset('storage/' . $superWithAvatar->avatar);
            }

            $emp001 = Employee::where('employee_code', 'EMP-001')->orWhere('email', 'keval@uesthrms.com')->first();
            if ($emp001 && $emp001->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($emp001->avatar)) {
                return asset('storage/' . $emp001->avatar);
            }
        }

        return null;
    }

    public function getLinkedEmployeeAttribute(): ?Employee
    {
        // 1. Direct email match
        $emp = Employee::where('email', $this->email)->first();
        if ($emp) {
            return $emp;
        }

        $emailLower = strtolower($this->email);

        // 2. Super Administrator / Keval accounts
        if (in_array($emailLower, ['keval192837@gmail.com', 'admin@uest.com', 'admin@uesthrms.com', 'keval@uesthrms.com'])) {
            $kevalEmp = Employee::where('email', 'keval@uesthrms.com')
                ->orWhere('employee_code', 'EMP-001')
                ->first();
            if ($kevalEmp)
                return $kevalEmp;
        }

        // 3. Known role accounts
        if (in_array($emailLower, ['staff@uesthrms.com', 'vikram.singh@uesthrms.com'])) {
            return Employee::where('email', 'vikram.singh@uesthrms.com')->first();
        }
        if (in_array($emailLower, ['hr@uesthrms.com', 'priya.patel@uesthrms.com'])) {
            return Employee::where('email', 'priya.patel@uesthrms.com')->first();
        }
        if (in_array($emailLower, ['manager@uesthrms.com', 'amit.kumar@uesthrms.com'])) {
            return Employee::where('email', 'amit.kumar@uesthrms.com')->first();
        }

        // 4. Fuzzy match by name
        $nameTrim = trim($this->name);
        $parts = preg_split('/\s+/', $nameTrim);
        if (!empty($parts[0])) {
            $firstName = $parts[0];
            $query = Employee::where('first_name', 'like', $firstName . '%');
            if (count($parts) >= 2) {
                $lastName = $parts[count($parts) - 1];
                $prefix = substr($lastName, 0, min(4, strlen($lastName)));
                $query->where('last_name', 'like', $prefix . '%');
            }
            $match = $query->first();
            if ($match)
                return $match;
        }

        // 5. Fallback for Super Admin
        if ($this->isSuperAdmin()) {
            return Employee::where('employee_code', 'EMP-001')->first() ?? Employee::first();
        }

        return null;
    }
}