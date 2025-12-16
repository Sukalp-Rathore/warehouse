<?php

namespace App\Services;

use App\Models\Client\Item;
use App\Models\Client\Inventory;

class ItemLocationService
{
    /**
     * Locate item by any code (item_code, barcode, QR code, RFID tag)
     */
    public function locateByCode($code)
    {
        $results = collect();

        // Search by item_code or sku
        $items = Item::where('item_code', 'like', "%{$code}%")
            ->orWhere('sku', 'like', "%{$code}%")
            ->orWhere('barcode', 'like', "%{$code}%")
            ->get();

        foreach ($items as $item) {
            $inventoryLocations = Inventory::where('item_id', $item->_id)
                ->with(['warehouse', 'storageType', 'section', 'bin', 'batch'])
                ->get();

            foreach ($inventoryLocations as $inventory) {
                $results->push([
                    'item' => $item,
                    'inventory' => $inventory,
                    'location_path' => $this->getLocationPath($inventory),
                    'match_type' => 'item_code'
                ]);
            }
        }

        // Search by barcode, QR code, or RFID in inventory collection
        $inventoryByCode = Inventory::where('barcode', $code)
            ->orWhere('qr_code', $code)
            ->orWhere('rfid_tag', $code)
            ->with(['item', 'warehouse', 'storageType', 'section', 'bin', 'batch'])
            ->get();

        foreach ($inventoryByCode as $inventory) {
            $matchType = 'unknown';
            if ($inventory->barcode === $code) {
                $matchType = 'barcode';
            } elseif ($inventory->qr_code === $code) {
                $matchType = 'qr_code';
            } elseif ($inventory->rfid_tag === $code) {
                $matchType = 'rfid_tag';
            }

            // Check if already added
            $exists = $results->first(function ($r) use ($inventory) {
                return (string) $r['inventory']->_id === (string) $inventory->_id;
            });

            if (!$exists) {
                $results->push([
                    'item' => $inventory->item,
                    'inventory' => $inventory,
                    'location_path' => $this->getLocationPath($inventory),
                    'match_type' => $matchType
                ]);
            }
        }

        return $results;
    }

    /**
     * Locate by barcode
     */
    public function locateByBarcode($barcode)
    {
        $inventory = Inventory::where('barcode', $barcode)
            ->with(['item', 'warehouse', 'storageType', 'section', 'bin', 'batch'])
            ->first();

        if (!$inventory) {
            return null;
        }

        return [
            'item' => $inventory->item,
            'inventory' => $inventory,
            'location_path' => $this->getLocationPath($inventory),
            'match_type' => 'barcode'
        ];
    }

    /**
     * Locate by QR code
     */
    public function locateByQR($qrCode)
    {
        $inventory = Inventory::where('qr_code', $qrCode)
            ->with(['item', 'warehouse', 'storageType', 'section', 'bin', 'batch'])
            ->first();

        if (!$inventory) {
            return null;
        }

        return [
            'item' => $inventory->item,
            'inventory' => $inventory,
            'location_path' => $this->getLocationPath($inventory),
            'match_type' => 'qr_code'
        ];
    }

    /**
     * Locate by RFID tag
     */
    public function locateByRFID($rfidTag)
    {
        $inventory = Inventory::where('rfid_tag', $rfidTag)
            ->with(['item', 'warehouse', 'storageType', 'section', 'bin', 'batch'])
            ->first();

        if (!$inventory) {
            return null;
        }

        return [
            'item' => $inventory->item,
            'inventory' => $inventory,
            'location_path' => $this->getLocationPath($inventory),
            'match_type' => 'rfid_tag'
        ];
    }

    /**
     * Get all locations where an item exists
     */
    public function getAllLocations($itemId)
    {
        $inventoryRecords = Inventory::where('item_id', $itemId)
            ->with(['warehouse', 'warehouse.plant', 'storageType', 'section', 'bin', 'batch'])
            ->get();

        $locations = collect();
        
        foreach ($inventoryRecords as $inventory) {
            $locations->push([
                'inventory' => $inventory,
                'location_path' => $this->getLocationPath($inventory),
                'quantity' => $inventory->available_qty,
                'total_quantity' => $inventory->quantity,
                'status' => $inventory->status,
                'batch' => $inventory->batch ? [
                    'batch_id' => $inventory->batch->batch_id,
                    'expiry_date' => $inventory->batch->expiry_date,
                ] : null,
            ]);
        }

        return $locations;
    }

    /**
     * Get full location details for an inventory record
     */
    public function getLocationDetails($inventoryId)
    {
        $inventory = Inventory::with([
            'item',
            'warehouse',
            'warehouse.plant',
            'storageType',
            'section',
            'bin',
            'batch'
        ])->find($inventoryId);

        if (!$inventory) {
            return null;
        }

        return [
            'inventory' => $inventory,
            'location_path' => $this->getLocationPath($inventory),
            'full_details' => [
                'plant' => $inventory->warehouse && $inventory->warehouse->plant 
                    ? [
                        'id' => (string) $inventory->warehouse->plant->_id,
                        'code' => $inventory->warehouse->plant->plant_code,
                        'name' => $inventory->warehouse->plant->plant_name,
                    ] 
                    : null,
                'warehouse' => $inventory->warehouse ? [
                    'id' => (string) $inventory->warehouse->_id,
                    'code' => $inventory->warehouse->warehouse_code,
                    'name' => $inventory->warehouse->warehouse_name,
                ] : null,
                'storage_type' => $inventory->storageType ? [
                    'id' => (string) $inventory->storageType->_id,
                    'code' => $inventory->storageType->code,
                    'name' => $inventory->storageType->name,
                ] : null,
                'section' => $inventory->section ? [
                    'id' => (string) $inventory->section->_id,
                    'code' => $inventory->section->section_code,
                    'name' => $inventory->section->section_name,
                ] : null,
                'bin' => $inventory->bin ? [
                    'id' => (string) $inventory->bin->_id,
                    'code' => $inventory->bin->bin_code,
                    'name' => $inventory->bin->bin_name,
                ] : null,
            ],
            'item' => $inventory->item ? [
                'id' => (string) $inventory->item->_id,
                'code' => $inventory->item->item_code,
                'sku' => $inventory->item->sku,
                'description' => $inventory->item->description,
            ] : null,
            'batch' => $inventory->batch ? [
                'id' => (string) $inventory->batch->_id,
                'batch_id' => $inventory->batch->batch_id,
                'expiry_date' => $inventory->batch->expiry_date,
                'manufacturing_date' => $inventory->batch->manufacturing_date,
            ] : null,
            'codes' => [
                'barcode' => $inventory->barcode,
                'qr_code' => $inventory->qr_code,
                'rfid_tag' => $inventory->rfid_tag,
            ],
        ];
    }

    /**
     * Get formatted location path string
     */
    private function getLocationPath($inventory)
    {
        $path = [];

        // Plant
        if ($inventory->warehouse && $inventory->warehouse->plant) {
            $path[] = $inventory->warehouse->plant->plant_name;
        }

        // Warehouse
        if ($inventory->warehouse) {
            $path[] = $inventory->warehouse->warehouse_name;
        }

        // Storage Type
        if ($inventory->storageType) {
            $path[] = $inventory->storageType->name;
        }

        // Section
        if ($inventory->section) {
            $path[] = $inventory->section->section_name;
        }

        // Bin
        if ($inventory->bin) {
            $path[] = $inventory->bin->bin_code;
        }

        return implode(' → ', $path);
    }
}

