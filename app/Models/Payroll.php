<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'month',
        'basic_salary',
        'allowances',
        'deductions',
        'net_salary',
        'paid_amount',
        'status',
        'payment_date',
        'payment_method',
        'remarks',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function getRemainingAmountAttribute(): float
    {
        $paid = (float) ($this->paid_amount ?? 0);
        $net = (float) $this->net_salary;
        return max(0, round($net - $paid, 2));
    }

    public function getIsFullyPaidAttribute(): bool
    {
        return $this->status === 'paid' || ((float) ($this->paid_amount ?? 0) >= (float) $this->net_salary && (float) $this->net_salary > 0);
    }

    public function getIsPartiallyPaidAttribute(): bool
    {
        $paid = (float) ($this->paid_amount ?? 0);
        $net = (float) $this->net_salary;
        return ($this->status === 'partial' || $this->status === 'partially_paid') || ($paid > 0 && $paid < $net);
    }
}
