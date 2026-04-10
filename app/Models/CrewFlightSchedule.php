<?php

namespace App\Models;

use App\Traits\LogsSystemChanges;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrewFlightSchedule extends Pivot
{
    use HasUuids, SoftDeletes, LogsSystemChanges;

    public $incrementing = false;

    protected $table = 'crew_flight_schedules';

    protected $fillable = [
        'crew_id',
        'flight_schedule_id',
        'role_in_flight',
        'assigned_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
