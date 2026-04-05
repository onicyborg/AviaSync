<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HealthRecord extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'crew_id',
        'checkup_date',
        'medical_examiner',
        'status',
        'notes',
        'next_checkup_date',
        'attachment_path',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'checkup_date' => 'date',
        'next_checkup_date' => 'date',
    ];

    public function crew()
    {
        return $this->belongsTo(Crew::class);
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
