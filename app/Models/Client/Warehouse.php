<?php

namespace App\Models\Client;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Warehouse extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $collection = 'warehouses';

    protected $fillable = [
        'name',
        'location',
        'capacity',
        'status',
        'description',
    ];

    protected $casts = [
        'capacity' => 'integer',
    ];
}
