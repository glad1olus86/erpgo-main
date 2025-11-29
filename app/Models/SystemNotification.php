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

    /**
     * Scope for current user's notifications (multi-tenancy)
     */
    public function scopeForCurrentUser($query)
    {
        return $query->where('created_by', Auth::user()->creatorId());
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
     * Get icon based on type
     */
    public function getIconAttribute()
    {
        return match($this->type) {
            self::TYPE_HOTEL_OCCUPANCY => 'ti ti-building',
            self::TYPE_WORKER_UNEMPLOYED => 'ti ti-user-off',
            default => 'ti ti-bell',
        };
    }

    /**
     * Get color based on type
     */
    public function getColorAttribute()
    {
        return match($this->type) {
            self::TYPE_HOTEL_OCCUPANCY => 'warning',
            self::TYPE_WORKER_UNEMPLOYED => 'danger',
            default => 'info',
        };
    }
}
