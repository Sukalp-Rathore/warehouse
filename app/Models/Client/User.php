<?php

namespace App\Models\Client;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Model implements UserContract
{
    use Authenticatable, HasFactory;

    protected $connection = 'tenant';
    protected $collection = 'users';

    protected $fillable = [
        'email',
        'password',
        'role',
        'permissions',
        'status',
        'client_id',
        'name',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'permissions' => 'array',
    ];
}
