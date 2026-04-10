<?php

namespace App\Models;

use App\Traits\LogsSystemChanges;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Certification extends Model
{
    use HasFactory, HasUuids, SoftDeletes, LogsSystemChanges;

    protected $fillable = [
        'crew_id',
        'certificate_name',
        'certificate_number',
        'issue_date',
        'expiry_date',
        'status',
        'attachment_path',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
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
