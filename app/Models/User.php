<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Auth\Authenticatable;

class User extends Model implements UserContract
{
    protected $collection = 'users';
    use Authenticatable;
    // Your model code
}