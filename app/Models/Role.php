<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Str;

class Role extends Model
{
    protected $fillable = [
        'name',
        'slug'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function users()
    {
        return $this->belongsToMany(User::class, 'role_user_pivot', 'role_id', 'user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Auto-generate slug
    |--------------------------------------------------------------------------
    */

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($role) {
            $role->slug = Str::slug($role->name, '_');
        });
    }
}
