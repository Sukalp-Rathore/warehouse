<?php

namespace App\Models\Master;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdminUser extends Model implements UserContract
{
    use Authenticatable, HasFactory;

    protected $connection = 'master';
    protected $collection = 'admin_users';

    protected $fillable = [
        'email',
        'password',
        'role',
        'name',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
