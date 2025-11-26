<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'worker_id',
        'room_id',
        'hotel_id',
        'check_in_date',
        'check_out_date',
        'created_by',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
    ];

    /**
     * Get the worker for this assignment.
     */
    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }

    /**
     * Get the room for this assignment.
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get the hotel for this assignment.
     */
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Scope to get only active assignments (not checked out).
     */
    public function scopeActive($query)
    {
        return $query->whereNull('check_out_date');
    }

    /**
     * Scope to get assignments for a specific worker.
     */
    public function scopeForWorker($query, $workerId)
    {
        return $query->where('worker_id', $workerId);
    }

    /**
     * Check if this assignment is currently active.
     */
    public function isActive()
    {
        return $this->check_out_date === null;
    }
}
