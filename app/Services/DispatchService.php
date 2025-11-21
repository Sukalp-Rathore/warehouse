<?php

namespace App\Services;

use App\Models\Client\DeliveryOrder;
use App\Models\Client\Shipment;
use App\Models\Client\Item;
use Illuminate\Support\Facades\DB;

class DispatchService
{
    /**
     * Create shipment manifest
     */
    public function createManifest($doId, $data)
    {
        $do = DeliveryOrder::find($doId);
        
        if (!$do) {
            return [
                'success' => false,
                'message' => 'Delivery order not found'
            ];
        }

        if ($do->status !== 'picked') {
            return [
                'success' => false,
                'message' => 'Delivery order must be in picked status'
            ];
        }

        // Calculate total weight and volume
        $totalWeight = 0;
        $totalVolume = 0;
        $manifestItems = [];

        foreach ($do->items as $doItem) {
            $item = Item::find($doItem['item_id']);
            if ($item) {
                $weight = ($item->weight_per_unit ?? $item->weight ?? 0) * ($doItem['picked_qty'] ?? $doItem['quantity']);
                $volume = ($item->volume_per_unit ?? $item->volume ?? 0) * ($doItem['picked_qty'] ?? $doItem['quantity']);
                
                $totalWeight += $weight;
                $totalVolume += $volume;

                $manifestItems[] = [
                    'item_code' => $doItem['item_code'] ?? $item->item_code,
                    'description' => $item->description,
                    'quantity' => $doItem['picked_qty'] ?? $doItem['quantity'],
                    'uom' => $item->uom,
                    'weight' => $weight,
                    'volume' => $volume,
                ];
            }
        }

        $shipmentNumber = $this->generateShipmentNumber();

        $shipment = Shipment::create([
            'shipment_number' => $shipmentNumber,
            'do_id' => $doId,
            'vehicle_number' => $data['vehicle_number'],
            'driver_name' => $data['driver_name'],
            'driver_phone' => $data['driver_phone'] ?? null,
            'dispatch_date' => $data['dispatch_date'] ?? now(),
            'expected_delivery_date' => $data['expected_delivery_date'] ?? now()->addDays(1),
            'status' => 'dispatched',
            'manifest' => [
                'items' => $manifestItems,
                'total_weight' => $totalWeight,
                'total_volume' => $totalVolume,
            ],
        ]);

        // Update DO status
        $do->update([
            'status' => 'dispatched',
            'vehicle_number' => $data['vehicle_number'],
            'driver_name' => $data['driver_name'],
            'dispatch_date' => $data['dispatch_date'] ?? now(),
        ]);

        return [
            'success' => true,
            'shipment' => $shipment
        ];
    }

    /**
     * Generate unique shipment number
     */
    private function generateShipmentNumber()
    {
        $prefix = 'SHIP-';
        $lastShipment = Shipment::orderBy('created_at', 'desc')->first();
        
        if ($lastShipment && preg_match('/' . $prefix . '(\d+)/', $lastShipment->shipment_number, $matches)) {
            $nextNumber = (int)$matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Confirm dispatch
     */
    public function confirmDispatch($shipmentId)
    {
        $shipment = Shipment::find($shipmentId);
        
        if (!$shipment) {
            return [
                'success' => false,
                'message' => 'Shipment not found'
            ];
        }

        $shipment->updateStatus('in_transit');

        return [
            'success' => true,
            'shipment' => $shipment
        ];
    }

    /**
     * Mark as delivered
     */
    public function markDelivered($shipmentId)
    {
        $shipment = Shipment::find($shipmentId);
        
        if (!$shipment) {
            return [
                'success' => false,
                'message' => 'Shipment not found'
            ];
        }

        $shipment->markDelivered();

        // Update DO status
        $do = $shipment->deliveryOrder;
        if ($do) {
            $do->updateStatus('closed');
        }

        return [
            'success' => true,
            'shipment' => $shipment
        ];
    }
}

