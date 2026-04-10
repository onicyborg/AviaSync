<?php

namespace App\Models;

use App\Traits\LogsSystemChanges;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Crew extends Model
{
    use HasFactory, HasUuids, SoftDeletes, LogsSystemChanges;

    protected $fillable = [
        'user_id',
        'employee_id',
        'profile_picture',
        'position',
        'base_location',
        'total_flight_hours',
        'status',
        'created_by',
        'updated_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function certifications()
    {
        return $this->hasMany(Certification::class);
    }

    public function healthRecords()
    {
        return $this->hasMany(HealthRecord::class);
    }

    public function flightSchedules()
    {
        return $this->belongsToMany(FlightSchedule::class, 'crew_flight_schedules')
                    ->using(CrewFlightSchedule::class)
                    ->withPivot('id', 'role_in_flight', 'assigned_at', 'created_by', 'updated_by', 'deleted_at')
                    ->wherePivotNull('deleted_at')
                    ->withTimestamps();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}