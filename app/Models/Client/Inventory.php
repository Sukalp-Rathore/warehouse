<?php

namespace App\Models\Client;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventory extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $collection = 'inventory';

    protected $fillable = [
        'warehouse_id',
        'sku',
        'name',
        'quantity',
        'location',
        'description',
        'unit_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
