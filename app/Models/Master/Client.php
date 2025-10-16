<?php

namespace App\Models\Master;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;

    protected $connection = 'master';
    protected $collection = 'clients';

    protected $fillable = [
        'client_id',
        'name',
        'email',
        'hashed_email',
        'database_name',
        'status',
        'subscription_plan',
        'max_warehouses',
        'max_users',
    ];

    protected $casts = [
        'max_warehouses' => 'integer',
        'max_users' => 'integer',
    ];

    public function isActive()
    {
        return $this->status === 'active';
    }
}
