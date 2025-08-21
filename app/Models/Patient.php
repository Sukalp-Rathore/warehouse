<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Patient extends Model
{
    //
    use HasFactory;
    protected $collection = 'patients';
    protected $fillable = [
        'name',
        'phone',
        'gender',
        'age',
        'address',
        'history',
        'created_by',
    ];
}
