<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = ['hotel_id', 'room_number', 'capacity', 'monthly_price', 'payment_type', 'partial_amount', 'created_by'];

    // Payment types
    const PAYMENT_WORKER = 'worker';      // Платит сам
    const PAYMENT_AGENCY = 'agency';      // Платит агенство
    const PAYMENT_PARTIAL = 'partial';    // Платит частично

    public static function getPaymentTypes(): array
    {
        return [
            self::PAYMENT_WORKER => __('Платит сам'),
            self::PAYMENT_AGENCY => __('Платит агенство'),
            self::PAYMENT_PARTIAL => __('Платит частично'),
        ];
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get all assignments for this room.
     */
    public function assignments()
    {
        return $this->hasMany(RoomAssignment::class);
    }

    /**
     * Get only current (active) assignments for this room.
     */
    public function currentAssignments()
    {
        return $this->hasMany(RoomAssignment::class)
            ->whereNull('check_out_date');
    }

    /**
     * Get the number of available spots in this room.
     */
    public function availableSpots()
    {
        $occupied = $this->currentAssignments()->count();
        return $this->capacity - $occupied;
    }

    /**
     * Check if this room is full.
     */
    public function isFull()
    {
        return $this->availableSpots() <= 0;
    }

    /**
     * Get occupancy info as a string (e.g., "2/3").
     */
    public function occupancyStatus()
    {
        $occupied = $this->currentAssignments()->count();
        return $occupied . '/' . $this->capacity;
    }
}
