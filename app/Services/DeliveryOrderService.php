<?php

namespace App\Services;

use App\Models\Client\DeliveryOrder;
use App\Models\Client\Item;
use App\Models\Client\Warehouse;
use Illuminate\Support\Facades\DB;

class DeliveryOrderService
{
    /**
     * Create a new delivery order
     */
    public function create($data)
    {
        $warehouse = Warehouse::find($data['warehouse_id']);
        
        if (!$warehouse) {
            return [
                'success' => false,
                'message' => 'Warehouse not found'
            ];
        }

        // Generate DO number
        $doNumber = $this->generateDONumber();

        // Create delivery order
        $deliveryOrder = DeliveryOrder::create([
            'do_number' => $doNumber,
            'customer_name' => $data['customer_name'],
            'customer_code' => $data['customer_code'] ?? null,
            'customer_address' => $data['customer_address'] ?? [],
            'warehouse_id' => $data['warehouse_id'],
            'status' => 'created',
            'priority' => $data['priority'] ?? 'normal',
            'delivery_date' => $data['delivery_date'] ?? now()->addDays(1),
            'items' => $data['items'],
            'picking_strategy' => $data['picking_strategy'] ?? 'fifo',
            'sap_reference' => $data['sap_reference'] ?? null,
            'created_by' => session('user._id'),
        ]);

        return [
            'success' => true,
            'delivery_order' => $deliveryOrder
        ];
    }

    /**
     * Generate unique DO number
     */
    private function generateDONumber()
    {
        $prefix = 'DO-';
        $lastDO = DeliveryOrder::orderBy('created_at', 'desc')->first();
        
        if ($lastDO && preg_match('/' . $prefix . '(\d+)/', $lastDO->do_number, $matches)) {
            $nextNumber = (int)$matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Update DO status
     */
    public function updateStatus($doId, $status)
    {
        $do = DeliveryOrder::find($doId);
        
        if (!$do) {
            return [
                'success' => false,
                'message' => 'Delivery order not found'
            ];
        }

        $do->updateStatus($status);

        return [
            'success' => true,
            'delivery_order' => $do
        ];
    }
}

