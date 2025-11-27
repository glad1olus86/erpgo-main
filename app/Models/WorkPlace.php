<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkPlace extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'address', 'phone', 'email', 'created_by'];

    /**
     * Get all work assignments for this work place
     */
    public function workAssignments()
    {
        return $this->hasMany(WorkAssignment::class);
    }

    /**
     * Get current (active) work assignments
     */
    public function currentAssignments()
    {
        return $this->hasMany(WorkAssignment::class)->whereNull('ended_at');
    }

    /**
     * Get all workers through assignments
     */
    public function workers()
    {
        return $this->hasManyThrough(Worker::class, WorkAssignment::class, 'work_place_id', 'id', 'id', 'worker_id');
    }

    /**
     * Get count of current workers
     */
    public function getCurrentWorkerCountAttribute()
    {
        return $this->currentAssignments()->count();
    }
}
