<?php

namespace App\Models\ADMIN;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubParameter extends Model
{
     use HasFactory;

    protected $fillable = ['sub_parameter_name', 'parameter_id'];

    public function parameter()
    {
        return $this->belongsTo(Parameter::class, 'parameter_id');
    }

    public function subSubParameters()
    {
        return $this->hasMany(SubSubParameter::class);
    }

    public function areaMappings()
    {
        return $this->belongsToMany(
            AreaParameterMapping::class,
            'parameter_subparameter_mappings',
            'subparameter_id',
            'area_parameter_mapping_id'
        )->withTimestamps();
    }

    public function parameterSubparameterMapping()
    {
        return $this->hasOne(ParameterSubparameterMapping::class, 'subparameter_id');
    }

    // This goes through the paramSubparamMapping
    public function subparamSubSubparamMapping()
    {
        return $this->hasOneThrough(
            SubparamSubSubparamMapping::class,  
            ParameterSubparameterMapping::class,         
            'sub_parameter_id',                  
            'parameter_subparameter_mapping_id', 
            'id',                                
            'id'                                
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
