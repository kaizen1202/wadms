<?php

namespace App\Models\ADMIN;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AccreditationDocuments extends Model
{
   protected $fillable = [
        'subparameter_id',
        'file_name',
        'file_path',
        'file_type',
        'upload_by',
        'role_id',
        'accred_info_id',
        'level_id',
        'program_id',
        'area_id',
        'parameter_id',
    ];
    public function subParameter()
    {
        return $this->belongsTo(SubParameter::class, 'subparameter_id');
    }
    public function uploader()
    {
        return $this->belongsTo(User::class, 'upload_by');
    }

    public function uploaderRole()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function accredInfo()
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

    public function parameter()
    {
        return $this->belongsTo(Parameter::class, 'parameter_id');
    }
}

