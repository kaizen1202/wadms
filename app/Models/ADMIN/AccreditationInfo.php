<?php

namespace App\Models\ADMIN;

use App\Models\ProgramFinalVerdict;
use App\Enums\AccreditationStatus;
use Illuminate\Database\Eloquent\Model;

class AccreditationInfo extends Model
{
    protected $fillable = [
        'title',
        'year',
        'status',
        'visit_type',
        'accreditation_date',
        'accreditation_body_id',
    ];
    
    protected $casts = [
        'status' => AccreditationStatus::class,
        'accreditation_date' => 'date'
    ];

    public function finalVerdicts()
    {
        return $this->hasMany(ProgramFinalVerdict::class, 'accred_info_id');
    }

    public function accreditationBody()
    {
        return $this->belongsTo(AccreditationBody::class, 'accreditation_body_id');
    }

    public function levels()
    {
        return $this->belongsToMany(
            AccreditationLevel::class,
            'accreditation_info_level'
        );
    }

    public function infoLevelProgramMappings()
    {
        return $this->hasMany(InfoLevelProgramMapping::class, 'accreditation_info_id');
    }

    /* ===================== Helpers ===================== */

    public function isOngoing(): bool
    {
        return $this->status === AccreditationStatus::ONGOING;
    }

    public function isCompleted(): bool
    {
        return $this->status === AccreditationStatus::COMPLETED;
    }
}
