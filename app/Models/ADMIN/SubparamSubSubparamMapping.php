<?php

namespace App\Models\ADMIN;

use Illuminate\Database\Eloquent\Model;

class SubparamSubSubparamMapping extends Model
{
    protected $table = 'subparam_subsubparam_mappings';

    protected $fillable = [
        'parameter_subparameter_mapping_id',
        'sub_subparameter_id',
    ];

    public function paramSubparamMapping()
    {
        return $this->belongsTo(ParameterSubparameterMapping::class, 'parameter_subparameter_mapping_id');
    }

    public function subSubParameter()
    {
        return $this->belongsTo(SubSubParameter::class, 'sub_subparameter_id');
    }
}
