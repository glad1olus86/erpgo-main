<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SystemNotification extends Model
{
    protected $fillable = [
        'type',
        'title', 
        'message',
        'data',
        'link',
        'severity',
        'is_read',
        'created_by',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
    ];

    // Notification types
    const TYPE_HOTEL_OCCUPANCY = 'hotel_occupancy';
    const TYPE_WORKER_UNEMPLOYED = 'worker_unemployed';

    // Severity levels
    const SEVERITY_INFO = 'info';
    const SEVERITY_WARNING = 'warning';
    const SEVERITY_DANGER = 'danger';

    /**
     * Scope for current user's notifications (multi-tenancy)
     * For cashbox notifications, also check target_user_id in data
     */
    public function scopeForCurrentUser($query)
    {
        $userId = Auth::user()->id;
        $companyId = Auth::user()->creatorId();
        
        return $query->where(function ($q) use ($userId, $companyId) {
            // Standard company-wide notifications
            $q->where('created_by', $companyId)
              ->where(function ($subQ) use ($userId) {
                  // Either no target_user_id (broadcast to company)
                  // Or target_user_id matches current user
                  $subQ->whereNull('data->target_user_id')
                       ->orWhereJsonContains('data->target_user_id', $userId)
                       ->orWhereRaw("JSON_EXTRACT(data, '$.target_user_id') = ?", [$userId]);
              });
        });
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Mark as read
     */
    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Get icon based on type/severity
     */
    public function getIconAttribute()
    {
        // For custom rules, use severity-based icons
        if (str_starts_with($this->type, 'custom_rule_')) {
            return match($this->severity) {
                self::SEVERITY_WARNING => 'ti ti-alert-triangle',
                self::SEVERITY_DANGER => 'ti ti-alert-circle',
                default => 'ti ti-info-circle',
            };
        }

        return match($this->type) {
            self::TYPE_HOTEL_OCCUPANCY => 'ti ti-building',
            self::TYPE_WORKER_UNEMPLOYED => 'ti ti-user-off',
            default => 'ti ti-bell',
        };
    }

    /**
     * Get color based on type/severity
     */
    public function getColorAttribute()
    {
        // For custom rules, use severity
        if (str_starts_with($this->type, 'custom_rule_') && $this->severity) {
            return $this->severity;
        }

        return match($this->type) {
            self::TYPE_HOTEL_OCCUPANCY => 'warning',
            self::TYPE_WORKER_UNEMPLOYED => 'danger',
            default => 'info',
        };
    }
}
