<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
        'year',
        'month',
        'total_deposited',
        'is_frozen',
    ];

    protected $casts = [
        'total_deposited' => 'decimal:2',
        'is_frozen' => 'boolean',
    ];

    /**
     * Get the company that owns this period.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all transactions for this period.
     */
    public function transactions()
    {
        return $this->hasMany(CashTransaction::class);
    }

    /**
     * Scope to filter by company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('created_by', $companyId);
    }

    /**
     * Check if period is frozen.
     */
    public function isFrozen(): bool
    {
        return $this->is_frozen;
    }

    /**
     * Get formatted period name (e.g., "Декабрь 2025").
     */
    public function getNameAttribute(): string
    {
        $months = [
            1 => 'Январь', 2 => 'Февраль', 3 => 'Март',
            4 => 'Апрель', 5 => 'Май', 6 => 'Июнь',
            7 => 'Июль', 8 => 'Август', 9 => 'Сентябрь',
            10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь',
        ];

        return ($months[$this->month] ?? $this->month) . ' ' . $this->year;
    }
}
