<?php

namespace App\Models\ADMIN;

use Illuminate\Database\Eloquent\Model;

class Parameter extends Model
{
    protected $fillable = ['parameter_name', 'area_id'];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function areaParameterMappings()
    {
        return $this->hasMany(AreaParameterMapping::class);
    }
    public function programAreas()
{
    return $this->belongsToMany(
        ProgramAreaMapping::class,
        'area_parameter_mappings',
        'parameter_id',
        'program_area_mapping_id'
    )->withTimestamps();
}

 public function sub_parameters()
{
    return $this->hasMany(SubParameter::class);
}


}
