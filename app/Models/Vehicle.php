<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'license_plate',
        'brand',
        'color',
        'vin_code',
        'fuel_consumption',
        'photo',
        'assigned_type',
        'assigned_id',
        'created_by',
    ];

    protected $casts = [
        'fuel_consumption' => 'decimal:2',
    ];

    /**
     * Scope for current user's vehicles (multi-tenancy)
     */
    public function scopeForCurrentUser($query)
    {
        return $query->where('created_by', Auth::user()->creatorId());
    }

    /**
     * Get the assigned person (Worker or User)
     */
    public function assignedPerson()
    {
        return $this->morphTo(__FUNCTION__, 'assigned_type', 'assigned_id');
    }

    /**
     * Get all technical inspections
     */
    public function inspections()
    {
        return $this->hasMany(TechnicalInspection::class)->orderBy('inspection_date', 'desc');
    }

    /**
     * Get the latest inspection
     */
    public function latestInspection()
    {
        return $this->hasOne(TechnicalInspection::class)->latestOfMany('inspection_date');
    }

    /**
     * Get inspection status: ok, soon, overdue
     */
    public function getInspectionStatusAttribute(): string
    {
        $latest = $this->latestInspection;
        
        if (!$latest) {
            return 'none';
        }

        $nextDate = Carbon::parse($latest->next_inspection_date);
        $today = Carbon::today();

        if ($nextDate->isPast()) {
            return 'overdue';
        }

        if ($nextDate->diffInDays($today) <= 30) {
            return 'soon';
        }

        return 'ok';
    }

    /**
     * Get inspection status label
     */
    public function getInspectionStatusLabelAttribute(): string
    {
        return match ($this->inspection_status) {
            'overdue' => __('Просрочено'),
            'soon' => __('Скоро ТО'),
            'ok' => __('В норме'),
            'none' => __('Нет данных'),
        };
    }

    /**
     * Get inspection status badge class
     */
    public function getInspectionStatusBadgeAttribute(): string
    {
        return match ($this->inspection_status) {
            'overdue' => 'bg-danger',
            'soon' => 'bg-warning',
            'ok' => 'bg-success',
            'none' => 'bg-secondary',
        };
    }

    /**
     * Get assigned person name
     */
    public function getAssignedNameAttribute(): ?string
    {
        if (!$this->assigned_id || !$this->assigned_type) {
            return null;
        }

        // Load relation if not loaded
        if (!$this->relationLoaded('assignedPerson')) {
            $this->load('assignedPerson');
        }

        $person = $this->assignedPerson;
        
        if (!$person) {
            return null;
        }

        // Check if it's a Worker (has first_name/last_name)
        if (str_contains($this->assigned_type, 'Worker')) {
            return trim(($person->first_name ?? '') . ' ' . ($person->last_name ?? ''));
        }

        // Otherwise it's a User
        return $person->name ?? null;
    }
}
