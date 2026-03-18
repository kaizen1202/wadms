<?php

namespace App\Models;

use App\Enums\EvaluationStatus;
use App\Models\ADMIN\Area;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ADMIN\AccreditationInfo;
use App\Models\ADMIN\AccreditationLevel;
use App\Models\ADMIN\Program;
use App\Models\User;
use App\Models\SubparameterRating;

class AccreditationEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'accred_info_id',
        'level_id',
        'program_id',
        'area_id',
        'evaluated_by',
        'role_id',
        'status'
    ];

    public function accreditationInfo()
    {
        return $this->belongsTo(AccreditationInfo::class, 'accred_info_id');
    }

    public function level()
    {
        return $this->belongsTo(AccreditationLevel::class, 'level_id');
    }

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }

    public function subparameterRatings()
    {
        return $this->hasMany(
            SubparameterRating::class,
            'evaluation_id'
        );
    }

    public function areaRecommendations()
    {
        return $this->hasMany(
            AreaRecommendation::class,
            'evaluation_id'
        );
    }

    protected $appends = ['is_final', 'is_updated'];

    public function getIsUpdatedAttribute(): bool
    {
        return $this->updated_at->gt($this->created_at);
    }

    public function getIsFinalAttribute(): bool
    {
        return $this->status === EvaluationStatus::FINALIZED;
    }

    protected $casts = [
        'status' => EvaluationStatus::class,
    ];

    protected static function booted()
    {
        static::updating(function ($evaluation) {
            if ($evaluation->status !== EvaluationStatus::FINALIZED &&
                $evaluation->updated_at->gt($evaluation->created_at)) {
                $evaluation->status = EvaluationStatus::UPDATED;
            }
        });
    }
}
