<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventory extends Model
{
    //
    use HasFactory;
    protected $collection = 'inventory';
    protected $fillable = [
        'product_name',
        'selling_price',
        'product_image',
        'quantity',
        'total_stock_amount'
    ];
}