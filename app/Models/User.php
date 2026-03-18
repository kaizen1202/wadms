<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\ADMIN\AccreditationAssignment;
use App\Models\ADMIN\ProgramAreaMapping;
use App\Enums\UserType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'user_type',
        'password',
        'status',
        'user_type',
        'current_role_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'user_type' => UserType::class,
        ];
    }
     public function areas()
    {
        return $this->belongsToMany(
            ProgramAreaMapping::class,
            'accreditation_assignments',
            'user_id',
            'area_id'
        );
    }

    public function roleRequests()
    {
        return $this->hasMany(RoleRequest::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user_pivot', 'user_id', 'role_id');
    }

    public function currentRole()
    {
        return $this->belongsTo(Role::class, 'current_role_id');
    }

    public function assignments()
    {
        return $this->hasMany(AccreditationAssignment::class);
    }
}
