<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'name',
        'email',
        'mobile',
        'logo',
        'address',
        'city',
        'state',
        'country',
        'status',
    ];

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function employeeTypes(): HasMany
    {
        return $this->hasMany(EmployeeType::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
