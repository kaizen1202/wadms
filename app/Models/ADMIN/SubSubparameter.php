<?php

namespace App\Models\ADMIN;

use Illuminate\Database\Eloquent\Model;

class SubSubparameter extends Model
{
    protected $fillable = ['name', 'sub_parameter_id'];

    public function subParameter()
    {
        return $this->belongsTo(SubParameter::class);
    }

    public function subparamSubSubparamMapping()
    {
        return $this->hasOneThrough(
            SubparamSubSubparamMapping::class,
            ParameterSubparameterMapping::class,
            'subparameter_id',                   // FK on parameter_subparameter_mappings
            'parameter_subparameter_mapping_id', // FK on subparam_subsubparam_mappings
            'id',                                // local key on subparameters
            'id'                                 // local key on parameter_subparameter_mappings
        );
    }

    public function uploads()
    {
        return $this->hasMany(AccreditationDocuments::class, 'sub_sub_parameter_id');
    }
}
