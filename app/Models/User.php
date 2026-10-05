<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

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
        ];
    }
    
    public function getInitialsAttribute(): string
    {
        $words = array_values(array_filter(explode(' ', trim($this->name))));
        if (empty($words)) {
            return 'AD';
        }
        if (count($words) === 1) {
            return strtoupper(substr($words[0], 0, min(2, strlen($words[0]))));
        }
        return strtoupper(substr($words[0], 0, 1) . substr($words[count($words) - 1], 0, 1));
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar && Storage::disk('public')->exists($this->avatar)) {
            return asset('storage/' . $this->avatar);
        }

        if ($this->linked_employee && $this->linked_employee->avatar && Storage::disk('public')->exists($this->linked_employee->avatar)) {
            return asset('storage/' . $this->linked_employee->avatar);
        }

        return null;
    }

    public function getLinkedEmployeeAttribute(): ?Employee
    {
        // 1. Direct email match (case-insensitive)
        if (!empty($this->email)) {
            $emp = Employee::whereRaw('LOWER(email) = ?', [strtolower(trim($this->email))])->first();
            if ($emp) {
                return $emp;
            }
        }

        // 2. Direct phone / mobile match
        $phone = $this->mobile ?: $this->phone;
        if (!empty($phone)) {
            $emp = Employee::where('phone', trim($phone))->first();
            if ($emp) {
                return $emp;
            }
        }

        // 3. Exact full name match
        if (!empty($this->name)) {
            $emp = Employee::whereRaw("LOWER(TRIM(CONCAT(first_name, ' ', last_name))) = ?", [strtolower(trim($this->name))])->first();
            if ($emp) {
                return $emp;
            }
        }

        return null;
    }
}