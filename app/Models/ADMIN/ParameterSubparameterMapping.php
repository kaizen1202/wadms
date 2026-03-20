<?php

namespace App\Models\ADMIN;

use Illuminate\Database\Eloquent\Model;

class ParameterSubparameterMapping extends Model
{
    protected $table = 'parameter_subparameter_mappings';

    protected $fillable = [
        'area_parameter_mapping_id',
        'subparameter_id',
    ];

    public function areaParameterMapping()
    {
        return $this->belongsTo(AreaParameterMapping::class, 'area_parameter_mapping_id');
    }

    public function subParameter()
    {
        return $this->belongsTo(SubParameter::class, 'subparameter_id');
    }

    public function subparamSubSubparamMappings()
    {
        return $this->hasMany(SubparamSubSubparamMapping::class, 'parameter_subparameter_mapping_id');
    }

    public function subSubParameters()
    {
        return $this->hasManyThrough(
            SubSubParameter::class,
            SubparamSubSubparamMapping::class,
            'parameter_subparameter_mapping_id',
            'id',
            'id',
            'sub_sub_parameter_id'
        );
    }

    public function uploads()
    {
        return $this->hasMany(
            AccreditationDocuments::class,
            'subparameter_id'
        );
    }
}