<?php

namespace App\Models\ADMIN;

use App\Enums\TaskForceRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AccreditationAssignment extends Model
{
     protected $fillable = [
        'user_id',
        'role_id',
        'role',
        'accred_info_id',
        'level_id',
        'program_id',
        'area_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function accreditationInfo()
    {
        return $this->belongsTo(AccreditationInfo::class, 'accred_info_id');
    }

    public function level()
    {
        return $this->belongsTo(AccreditationLevel::class, 'level_id');
    }

    protected function casts(): array
    {
        return [
            'role' => TaskForceRole::class,
        ];
    }
}
