<?php

namespace App\Models\ADMIN;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InfoLevelProgramMapping extends Model
{
    use SoftDeletes;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'accreditation_info_id',
        'program_id',
        'level_id',
        'deleted_by'
    ];
    // InfoLevelProgramMapping.php
    public function accreditationInfo()
    {
        return $this->belongsTo(AccreditationInfo::class, 'accreditation_info_id');
    }

    public function level()
    {
        return $this->belongsTo(AccreditationLevel::class, 'level_id');
    }

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function programAreas()
    {
        return $this->hasMany(ProgramAreaMapping::class, 'info_level_program_mapping_id');
    }

    public function areas()
    {
        return $this->belongsToMany(
            Area::class,
            'program_area_mappings',
            'info_level_program_mapping_id',
            'area_id'
        );
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    protected static function booted()
    {
        static::deleting(function ($model) {
            if (! $model->isForceDeleting()) {
                $model->deleted_by = auth()->id();
                $model->saveQuietly();
            }
        });
    }
}
