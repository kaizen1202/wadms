<?php

namespace App\Models\ADMIN;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
  protected $fillable = [
    'program_name',
    'program_description',
    'specialization'
  ];

  public function areas()
  {
      return $this->belongsToMany(
          Area::class,
          'program_area_mappings'
      );
  }

  public function infoLevelProgramMappings()
  {
      return $this->hasMany(InfoLevelProgramMapping::class, 'program_id');
  }
}
