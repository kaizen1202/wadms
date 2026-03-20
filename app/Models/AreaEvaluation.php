<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AreaEvaluation extends Model
{
    protected $fillable = [
        'program_area_mapping_id',
        'internal_accessor_id',
        'status',
        'completed_at',
    ];

    public function files()
    {
        return $this->hasMany(AreaEvaluationFile::class);
    }
}
