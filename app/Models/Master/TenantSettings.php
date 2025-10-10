<?php

namespace App\Models\Master;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TenantSettings extends Model
{
    use HasFactory;

    protected $connection = 'master';
    protected $collection = 'tenant_settings';

    protected $fillable = [
        'client_id',
        'tenant_name',
        'tenant_email',
        'timezone',
        'plant_details',
        'warehouse_config',
        'bin_config',
        'item_vendor_rules',
        'barcode_settings',
        'roles_permissions',
        'reorder_alert_rules',
        'integration_settings',
        'status',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'plant_details' => 'array',
        'warehouse_config' => 'array',
        'bin_config' => 'array',
        'item_vendor_rules' => 'array',
        'barcode_settings' => 'array',
        'roles_permissions' => 'array',
        'reorder_alert_rules' => 'array',
        'integration_settings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'client_id');
    }

    public function isActive()
    {
        return $this->status === 'active';
    }
}
