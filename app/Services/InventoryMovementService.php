<?php

namespace App\Services;

use App\Models\Client\Inventory;
use App\Models\Client\InventoryTransaction;
use App\Models\Client\Bin;
use App\Models\Client\Batch;
use App\Models\Client\Item;
use App\Models\Client\GRN;
use Illuminate\Support\Facades\DB;

class InventoryMovementService
{
    /**
     * Transfer items from one bin to another
     */
    public function transfer($fromBinId, $toBinId, $itemId, $quantity, $batchId = null, $referenceDoc = null, $notes = null)
    {
        DB::connection('tenant')->beginTransaction();
        
        try {
            // Find source inventory
            $sourceInventory = Inventory::where('bin_id', $fromBinId)
                ->where('item_id', $itemId)
                ->where('status', 'available')
                ->where('available_qty', '>=', $quantity)
                ->first();

            if (!$sourceInventory) {
                throw new \Exception('Insufficient inventory in source bin');
            }

            // Validate destination bin capacity
            $item = Item::find($itemId);
            $bin = Bin::find($toBinId);
            
            $volumePerUnit = $item->volume_per_unit ?? $item->volume ?? 0.01;
            $weightPerUnit = $item->weight_per_unit ?? $item->weight ?? 0.01;
            
            $requiredVolume = $quantity * $volumePerUnit;
            $requiredWeight = $quantity * $weightPerUnit;

            $availableVolume = ($bin->max_volume ?? 0) - ($bin->current_volume ?? 0);
            $availableWeight = ($bin->max_weight ?? 0) - ($bin->current_weight ?? 0);

            if ($availableVolume < $requiredVolume || $availableWeight < $requiredWeight) {
                throw new \Exception('Destination bin does not have sufficient capacity');
            }

            // Deduct from source
            $sourceInventory->available_qty -= $quantity;
            $sourceInventory->quantity -= $quantity;
            $sourceInventory->last_movement_at = now();
            $sourceInventory->save();

            // Update source bin capacity
            $fromBin = Bin::find($fromBinId);
            $fromBin->current_volume = max(0, ($fromBin->current_volume ?? 0) - $requiredVolume);
            $fromBin->current_weight = max(0, ($fromBin->current_weight ?? 0) - $requiredWeight);
            $fromBin->save();

            // Find or create destination inventory
            $destinationInventory = Inventory::where('bin_id', $toBinId)
                ->where('item_id', $itemId)
                ->where('batch_id', $batchId ?? $sourceInventory->batch_id)
                ->first();

            if ($destinationInventory) {
                // Update existing inventory
                $destinationInventory->quantity += $quantity;
                $destinationInventory->available_qty += $quantity;
                $destinationInventory->last_movement_at = now();
                $destinationInventory->save();
            } else {
                // Create new inventory record
                $destinationInventory = Inventory::create([
                    'item_id' => $itemId,
                    'item_code' => $item->item_code,
                    'warehouse_id' => $bin->warehouse_id,
                    'storage_type_id' => $bin->storage_type_id,
                    'section_id' => $bin->section_id,
                    'bin_id' => $toBinId,
                    'batch_id' => $batchId ?? $sourceInventory->batch_id,
                    'quantity' => $quantity,
                    'available_qty' => $quantity,
                    'blocked_qty' => 0,
                    'reserved_qty' => 0,
                    'status' => 'available',
                    'last_movement_at' => now(),
                    'created_by' => session('user._id'),
                ]);
            }

            // Update destination bin capacity
            $bin->current_volume = ($bin->current_volume ?? 0) + $requiredVolume;
            $bin->current_weight = ($bin->current_weight ?? 0) + $requiredWeight;
            $bin->save();

            // Create transaction record
            InventoryTransaction::createTransaction([
                'transaction_type' => 'TRANSFER',
                'item_id' => $itemId,
                'item_code' => $item->item_code,
                'batch_id' => $batchId ?? $sourceInventory->batch_id,
                'from_bin_id' => $fromBinId,
                'to_bin_id' => $toBinId,
                'quantity' => $quantity,
                'uom' => $item->uom,
                'reference_doc' => $referenceDoc,
                'notes' => $notes,
            ]);

            DB::connection('tenant')->commit();
            
            return [
                'success' => true,
                'inventory' => $destinationInventory
            ];
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Pick/issue items (deduct from inventory)
     */
    public function pick($binId, $itemId, $quantity, $batchId = null, $referenceDoc = null, $notes = null)
    {
        DB::connection('tenant')->beginTransaction();
        
        try {
            $query = Inventory::where('bin_id', $binId)
                ->where('item_id', $itemId)
                ->where('status', 'available')
                ->where('available_qty', '>=', $quantity);

            if ($batchId) {
                $query->where('batch_id', $batchId);
            }

            $inventory = $query->first();

            if (!$inventory) {
                throw new \Exception('Insufficient inventory in bin');
            }

            $item = Item::find($itemId);
            $bin = Bin::find($binId);

            // Deduct quantity
            $inventory->available_qty -= $quantity;
            $inventory->quantity -= $quantity;
            $inventory->last_movement_at = now();
            $inventory->save();

            // Update bin capacity
            $volumePerUnit = $item->volume_per_unit ?? $item->volume ?? 0.01;
            $weightPerUnit = $item->weight_per_unit ?? $item->weight ?? 0.01;
            
            $bin->current_volume = max(0, ($bin->current_volume ?? 0) - ($quantity * $volumePerUnit));
            $bin->current_weight = max(0, ($bin->current_weight ?? 0) - ($quantity * $weightPerUnit));
            $bin->save();

            // Create transaction
            InventoryTransaction::createTransaction([
                'transaction_type' => 'PICK',
                'item_id' => $itemId,
                'item_code' => $item->item_code,
                'batch_id' => $batchId ?? $inventory->batch_id,
                'from_bin_id' => $binId,
                'to_bin_id' => null,
                'quantity' => $quantity,
                'uom' => $item->uom,
                'reference_doc' => $referenceDoc,
                'notes' => $notes,
            ]);

            DB::connection('tenant')->commit();
            
            return [
                'success' => true,
                'inventory' => $inventory
            ];
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Putaway items (from GRN or manual placement)
     */
    public function putaway($warehouseId, $binId, $itemId, $quantity, $batchId = null, $grnId = null, $referenceDoc = null, $codes = null)
    {
        DB::connection('tenant')->beginTransaction();
        
        try {
            $item = Item::find($itemId);
            $bin = Bin::find($binId);

            if (!$item || !$bin) {
                throw new \Exception('Item or bin not found');
            }

            // Validate capacity
            $volumePerUnit = $item->volume_per_unit ?? $item->volume ?? 0.01;
            $weightPerUnit = $item->weight_per_unit ?? $item->weight ?? 0.01;
            
            $requiredVolume = $quantity * $volumePerUnit;
            $requiredWeight = $quantity * $weightPerUnit;

            $availableVolume = ($bin->max_volume ?? 0) - ($bin->current_volume ?? 0);
            $availableWeight = ($bin->max_weight ?? 0) - ($bin->current_weight ?? 0);

            if ($availableVolume < $requiredVolume || $availableWeight < $requiredWeight) {
                throw new \Exception('Bin does not have sufficient capacity');
            }

            // Find or create inventory record
            $inventory = Inventory::where('bin_id', $binId)
                ->where('item_id', $itemId)
                ->where('batch_id', $batchId)
                ->first();

            if ($inventory) {
                // Update existing
                $inventory->quantity += $quantity;
                $inventory->available_qty += $quantity;
                $inventory->last_movement_at = now();
                $inventory->save();
            } else {
                // Create new inventory record
                $inventoryData = [
                    'item_id' => $itemId,
                    'item_code' => $item->item_code,
                    'warehouse_id' => $warehouseId,
                    'storage_type_id' => $bin->storage_type_id,
                    'section_id' => $bin->section_id,
                    'bin_id' => $binId,
                    'batch_id' => $batchId,
                    'quantity' => $quantity,
                    'available_qty' => $quantity,
                    'blocked_qty' => 0,
                    'reserved_qty' => 0,
                    'status' => 'available',
                    'last_movement_at' => now(),
                    'created_by' => session('user._id'),
                ];

                // Add barcode/QR/RFID codes if provided
                if ($codes) {
                    $inventoryData['barcode'] = $codes['barcode'] ?? null;
                    $inventoryData['qr_code'] = $codes['qr_code'] ?? null;
                    $inventoryData['rfid_tag'] = $codes['rfid_tag'] ?? null;
                }

                $inventory = Inventory::create($inventoryData);
            }

            // Update bin capacity
            $bin->current_volume = ($bin->current_volume ?? 0) + $requiredVolume;
            $bin->current_weight = ($bin->current_weight ?? 0) + $requiredWeight;
            
            // Update bin status if needed
            if ($bin->current_volume >= ($bin->max_volume * 0.9)) {
                $bin->status = 'occupied';
            }
            $bin->save();

            // Create transaction
            InventoryTransaction::createTransaction([
                'transaction_type' => 'PUTAWAY',
                'item_id' => $itemId,
                'item_code' => $item->item_code,
                'batch_id' => $batchId,
                'from_bin_id' => null,
                'to_bin_id' => $binId,
                'quantity' => $quantity,
                'uom' => $item->uom,
                'reference_doc' => $referenceDoc,
                'metadata' => $grnId ? ['grn_id' => $grnId] : null,
            ]);

            DB::connection('tenant')->commit();
            
            return [
                'success' => true,
                'inventory' => $inventory
            ];
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Stock adjustment (increase or decrease quantity)
     */
    public function adjustment($binId, $itemId, $quantity, $batchId = null, $reason = null, $adjustmentType = 'adjust')
    {
        DB::connection('tenant')->beginTransaction();
        
        try {
            $item = Item::find($itemId);
            $bin = Bin::find($binId);

            if (!$item || !$bin) {
                throw new \Exception('Item or bin not found');
            }

            $inventory = Inventory::where('bin_id', $binId)
                ->where('item_id', $itemId)
                ->where('batch_id', $batchId)
                ->first();

            $volumePerUnit = $item->volume_per_unit ?? $item->volume ?? 0.01;
            $weightPerUnit = $item->weight_per_unit ?? $item->weight ?? 0.01;

            if ($adjustmentType === 'increase') {
                // Increase quantity
                if (!$inventory) {
                    // Create new inventory record
                    $inventory = Inventory::create([
                        'item_id' => $itemId,
                        'item_code' => $item->item_code,
                        'warehouse_id' => $bin->warehouse_id,
                        'storage_type_id' => $bin->storage_type_id,
                        'section_id' => $bin->section_id,
                        'bin_id' => $binId,
                        'batch_id' => $batchId,
                        'quantity' => $quantity,
                        'available_qty' => $quantity,
                        'status' => 'available',
                        'created_by' => session('user._id'),
                    ]);
                } else {
                    $inventory->quantity += $quantity;
                    $inventory->available_qty += $quantity;
                    $inventory->save();
                }

                // Update bin capacity
                $bin->current_volume = ($bin->current_volume ?? 0) + ($quantity * $volumePerUnit);
                $bin->current_weight = ($bin->current_weight ?? 0) + ($quantity * $weightPerUnit);
            } else {
                // Decrease quantity
                if (!$inventory || $inventory->available_qty < $quantity) {
                    throw new \Exception('Insufficient inventory for adjustment');
                }

                $inventory->quantity -= $quantity;
                $inventory->available_qty -= $quantity;
                $inventory->save();

                // Update bin capacity
                $bin->current_volume = max(0, ($bin->current_volume ?? 0) - ($quantity * $volumePerUnit));
                $bin->current_weight = max(0, ($bin->current_weight ?? 0) - ($quantity * $weightPerUnit));
            }

            $bin->save();

            // Create transaction
            InventoryTransaction::createTransaction([
                'transaction_type' => 'ADJUSTMENT',
                'item_id' => $itemId,
                'item_code' => $item->item_code,
                'batch_id' => $batchId ?? $inventory->batch_id,
                'from_bin_id' => $binId,
                'to_bin_id' => $binId,
                'quantity' => abs($quantity),
                'uom' => $item->uom,
                'notes' => $reason ? "Adjustment: {$reason}" : 'Stock adjustment',
                'metadata' => [
                    'adjustment_type' => $adjustmentType,
                    'reason' => $reason
                ],
            ]);

            DB::connection('tenant')->commit();
            
            return [
                'success' => true,
                'inventory' => $inventory
            ];
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Move item to QC hold
     */
    public function qcHold($inventoryId, $holdBinId = null, $notes = null)
    {
        DB::connection('tenant')->beginTransaction();
        
        try {
            $inventory = Inventory::find($inventoryId);
            
            if (!$inventory) {
                throw new \Exception('Inventory record not found');
            }

            // If hold bin is specified, transfer to that bin
            if ($holdBinId && (string) $holdBinId !== (string) $inventory->bin_id) {
                // Transfer to QC hold bin
                $result = $this->transfer(
                    $inventory->bin_id,
                    $holdBinId,
                    $inventory->item_id,
                    $inventory->available_qty,
                    $inventory->batch_id,
                    null,
                    'QC Hold: ' . $notes
                );

                if (!$result['success']) {
                    throw new \Exception($result['message']);
                }

                $inventory = Inventory::find($inventoryId);
            }

            // Update status
            $inventory->status = 'qc_hold';
            $inventory->blocked_qty += $inventory->available_qty;
            $inventory->available_qty = 0;
            $inventory->last_movement_at = now();
            $inventory->save();

            // Create transaction
            InventoryTransaction::createTransaction([
                'transaction_type' => 'QC_HOLD',
                'item_id' => $inventory->item_id,
                'item_code' => $inventory->item_code,
                'batch_id' => $inventory->batch_id,
                'from_bin_id' => $inventory->bin_id,
                'to_bin_id' => $holdBinId ?? $inventory->bin_id,
                'quantity' => $inventory->quantity,
                'uom' => $inventory->item->uom ?? 'PCS',
                'notes' => $notes,
            ]);

            DB::connection('tenant')->commit();
            
            return [
                'success' => true,
                'inventory' => $inventory
            ];
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Release item from QC hold
     */
    public function qcRelease($inventoryId, $destinationBinId, $notes = null)
    {
        // Note: MongoDB transactions may not work the same as SQL, so we'll handle without explicit transaction
        try {
            $inventory = Inventory::find($inventoryId);
            
            if (!$inventory) {
                throw new \Exception('Inventory record not found');
            }

            if ($inventory->status !== 'qc_hold') {
                throw new \Exception('Inventory is not in QC hold status');
            }

            $releaseQuantity = (float) ($inventory->blocked_qty ?? 0);
            
            // Log initial state
            \Log::info('QC Release: Starting', [
                'inventory_id' => (string) $inventory->_id,
                'current_status' => $inventory->status,
                'blocked_qty' => $releaseQuantity,
                'quantity' => $inventory->quantity,
                'available_qty' => $inventory->available_qty,
                'reserved_qty' => $inventory->reserved_qty ?? 0,
            ]);
            
            if ($releaseQuantity <= 0) {
                throw new \Exception('No blocked quantity to release');
            }
            
            $item = Item::find($inventory->item_id);
            $itemId = $inventory->item_id;
            $batchId = $inventory->batch_id;
            $sourceBinId = $inventory->bin_id;
            $sourceBin = Bin::find($sourceBinId);

            // Normalize bin IDs for comparison (handle MongoDB ObjectId vs string)
            $normalizedSourceBinId = (string) $sourceBinId;
            $normalizedDestBinId = (string) $destinationBinId;
            
            \Log::info('QC Release: Bin comparison', [
                'source_bin_id' => $normalizedSourceBinId,
                'destination_bin_id' => $normalizedDestBinId,
                'are_same' => $normalizedSourceBinId === $normalizedDestBinId,
            ]);

            // If destination bin is different, we need to manually transfer since status is qc_hold
            if ($normalizedSourceBinId !== $normalizedDestBinId) {
                $destBin = Bin::find($destinationBinId);
                
                if (!$destBin) {
                    throw new \Exception('Destination bin not found');
                }

                // Validate destination bin capacity
                // Note: When releasing from QC, we're moving existing inventory, so we need to check
                // if the destination bin can accommodate the quantity being released
                $volumePerUnit = $item->volume_per_unit ?? $item->volume ?? 0.01;
                $weightPerUnit = $item->weight_per_unit ?? $item->weight ?? 0.01;
                
                $requiredVolume = $releaseQuantity * $volumePerUnit;
                $requiredWeight = $releaseQuantity * $weightPerUnit;

                $availableVolume = ($destBin->max_volume ?? 0) - ($destBin->current_volume ?? 0);
                $availableWeight = ($destBin->max_weight ?? 0) - ($destBin->current_weight ?? 0);

                // Allow a small tolerance (1%) for rounding errors, but still check capacity
                $volumeTolerance = $requiredVolume * 0.01;
                $weightTolerance = $requiredWeight * 0.01;

                if (($availableVolume + $volumeTolerance) < $requiredVolume || ($availableWeight + $weightTolerance) < $requiredWeight) {
                    \Log::warning('QC Release: Capacity check failed', [
                        'destination_bin_id' => (string) $destBin->_id,
                        'required_volume' => $requiredVolume,
                        'available_volume' => $availableVolume,
                        'required_weight' => $requiredWeight,
                        'available_weight' => $availableWeight,
                    ]);
                    throw new \Exception('Destination bin does not have sufficient capacity. Required: ' . 
                        number_format($requiredVolume, 2) . ' volume, ' . number_format($requiredWeight, 2) . ' weight. ' .
                        'Available: ' . number_format($availableVolume, 2) . ' volume, ' . number_format($availableWeight, 2) . ' weight.');
                }

                // Deduct from source inventory using MongoDB native update
                $totalQuantity = (float) ($inventory->quantity ?? 0);
                $releaseQty = (float) $releaseQuantity;
                $newQuantity = max(0, $totalQuantity - $releaseQty);
                // When releasing from QC, all blocked quantity should be released
                $newBlockedQty = (float) 0;
                $reservedQty = (float) ($inventory->reserved_qty ?? 0);
                
                // If there's remaining quantity after release, it should be available
                if ($newQuantity > 0) {
                    // Available = total quantity - reserved (blocked is already 0)
                    $newAvailableQty = max(0, $newQuantity - $reservedQty);
                } else {
                    // No quantity left - set to 0
                    $newAvailableQty = (float) 0;
                    $newBlockedQty = (float) 0;
                }
                
                $collection = DB::connection('tenant')->getCollection('inventory');
                $updateResult = $collection->updateOne(
                    ['_id' => $inventory->_id],
                    [
                        '$set' => [
                            'quantity' => (float) $newQuantity,
                            'available_qty' => (float) $newAvailableQty,
                            'blocked_qty' => (float) $newBlockedQty,
                            'status' => 'available',
                            'last_movement_at' => now(),
                        ]
                    ]
                );
                
                \Log::info('QC Release: Different bin source update', [
                    'inventory_id' => (string) $inventory->_id,
                    'modified_count' => $updateResult->getModifiedCount(),
                    'new_quantity' => $newQuantity,
                    'new_blocked_qty' => $newBlockedQty,
                    'new_available_qty' => $newAvailableQty,
                ]);

                // Update source bin capacity
                if ($sourceBin) {
                    $sourceBin->current_volume = max(0, ($sourceBin->current_volume ?? 0) - $requiredVolume);
                    $sourceBin->current_weight = max(0, ($sourceBin->current_weight ?? 0) - $requiredWeight);
                    $sourceBin->save();
                }

                // Find or create destination inventory
                $destinationInventory = Inventory::where('bin_id', $destinationBinId)
                    ->where('item_id', $itemId)
                    ->where('batch_id', $batchId)
                    ->first();

                if ($destinationInventory) {
                    // Update existing inventory using MongoDB native update
                    $destQuantity = (float) ($destinationInventory->quantity ?? 0);
                    $destAvailableQty = (float) ($destinationInventory->available_qty ?? 0);
                    $releaseQty = (float) $releaseQuantity;
                    
                    $collection = DB::connection('tenant')->getCollection('inventory');
                    $updateResult = $collection->updateOne(
                        ['_id' => $destinationInventory->_id],
                        [
                            '$set' => [
                                'quantity' => (float) ($destQuantity + $releaseQty),
                                'available_qty' => (float) ($destAvailableQty + $releaseQty),
                                'status' => 'available',
                                'blocked_qty' => (float) 0, // Ensure it's not blocked
                                'last_movement_at' => now(),
                            ]
                        ]
                    );
                    
                    \Log::info('QC Release: Different bin destination update', [
                        'inventory_id' => (string) $destinationInventory->_id,
                        'modified_count' => $updateResult->getModifiedCount(),
                        'new_quantity' => $destQuantity + $releaseQty,
                        'new_available_qty' => $destAvailableQty + $releaseQty,
                    ]);
                    
                    // Reload and refresh
                    $inventory = Inventory::find($destinationInventory->_id);
                    if ($inventory) {
                        $inventory->refresh();
                    }
                } else {
                    // Create new inventory record in destination bin
                    $inventory = Inventory::create([
                        'item_id' => $itemId,
                        'item_code' => $item->item_code,
                        'warehouse_id' => $destBin->warehouse_id,
                        'storage_type_id' => $destBin->storage_type_id,
                        'section_id' => $destBin->section_id,
                        'bin_id' => $destinationBinId,
                        'batch_id' => $batchId,
                        'quantity' => $releaseQuantity,
                        'available_qty' => $releaseQuantity,
                        'blocked_qty' => 0,
                        'reserved_qty' => 0,
                        'status' => 'available',
                        'last_movement_at' => now(),
                        'created_by' => session('user._id'),
                    ]);
                }

                // Update destination bin capacity
                $destBin->current_volume = ($destBin->current_volume ?? 0) + $requiredVolume;
                $destBin->current_weight = ($destBin->current_weight ?? 0) + $requiredWeight;
                $destBin->save();
            } else {
                // Same bin - just release from QC hold (no transfer needed)
                // Calculate available quantity: total quantity minus reserved quantity
                // (blocked quantity will be set to 0)
                $reservedQty = (float) ($inventory->reserved_qty ?? 0);
                $totalQuantity = (float) ($inventory->quantity ?? 0);
                $newAvailableQty = max(0, $totalQuantity - $reservedQty);
                
                \Log::info('QC Release: Same bin - before update', [
                    'inventory_id' => (string) $inventory->_id,
                    'total_quantity' => $totalQuantity,
                    'reserved_qty' => $reservedQty,
                    'current_blocked_qty' => $inventory->blocked_qty,
                    'current_available_qty' => $inventory->available_qty,
                    'new_available_qty' => $newAvailableQty,
                ]);
                
                // Use Eloquent update which handles MongoDB ObjectIds correctly
                $inventory->status = 'available';
                $inventory->available_qty = (float) $newAvailableQty;
                $inventory->blocked_qty = (float) 0;
                $inventory->last_movement_at = now();
                $updateResult = $inventory->save();
                
                // Log the update result for debugging
                \Log::info('QC Release: Same bin update result', [
                    'inventory_id' => (string) $inventory->_id,
                    'update_success' => $updateResult,
                    'new_available_qty' => $newAvailableQty,
                ]);
                
                // Force reload to get updated record - bypass cache completely
                $inventory = Inventory::find($inventory->_id);
                if ($inventory) {
                    $inventory->refresh();
                    
                    // Verify immediately after reload
                    \Log::info('QC Release: Same bin - after reload', [
                        'inventory_id' => (string) $inventory->_id,
                        'status' => $inventory->status,
                        'blocked_qty' => $inventory->blocked_qty,
                        'available_qty' => $inventory->available_qty,
                        'quantity' => $inventory->quantity,
                    ]);
                }
            }

            // Create transaction record
            $fromBinId = $normalizedSourceBinId !== $normalizedDestBinId ? 
                ($sourceBin ? $sourceBin->_id : null) : $sourceBinId;
            
            InventoryTransaction::createTransaction([
                'transaction_type' => 'QC_RELEASE',
                'item_id' => $itemId,
                'item_code' => $inventory->item_code,
                'batch_id' => $batchId,
                'from_bin_id' => $fromBinId,
                'to_bin_id' => $destinationBinId,
                'quantity' => $releaseQuantity,
                'uom' => $item->uom ?? 'PCS',
                'notes' => $notes,
            ]);

            // Final verification - ALWAYS force update to ensure blocked_qty is 0
            // Use fresh() to bypass any Eloquent caching
            $finalInventory = Inventory::find($inventory->_id);
            
            if (!$finalInventory) {
                throw new \Exception('Failed to retrieve inventory after release');
            }
            
            // Always force update to ensure blocked_qty is 0 and status is available
            // Use Eloquent model update which handles MongoDB ObjectIds correctly
            $finalQuantity = (float) ($finalInventory->quantity ?? 0);
            $finalReservedQty = (float) ($finalInventory->reserved_qty ?? 0);
            $finalAvailableQty = max(0, $finalQuantity - $finalReservedQty);
            $finalBlockedQty = (float) ($finalInventory->blocked_qty ?? 0);
            
            $finalInventory->status = 'available';
            $finalInventory->blocked_qty = (float) 0;
            $finalInventory->available_qty = (float) $finalAvailableQty;
            $finalInventory->last_movement_at = now();
            $updateResult = $finalInventory->save();
            
            // Log the final update for debugging
            \Log::info('QC Release: Final force update', [
                'inventory_id' => (string) $finalInventory->_id,
                'status_before' => $finalInventory->getOriginal('status'),
                'blocked_qty_before' => $finalBlockedQty,
                'available_qty_before' => $finalInventory->getOriginal('available_qty'),
                'quantity' => $finalQuantity,
                'reserved_qty' => $finalReservedQty,
                'update_success' => $updateResult,
            ]);
            
            // Force fresh reload to bypass any caching
            $inventory = Inventory::find($inventory->_id);
            if ($inventory) {
                $inventory->refresh(); // Force refresh from database
                
                // Final verification - log if still blocked
                if (($inventory->blocked_qty ?? 0) > 0) {
                    \Log::error('QC Release: Blocked quantity still present after update!', [
                        'inventory_id' => (string) $inventory->_id,
                        'blocked_qty' => $inventory->blocked_qty,
                        'status' => $inventory->status,
                    ]);
                }
            }
            
            return [
                'success' => true,
                'inventory' => $inventory
            ];
        } catch (\Exception $e) {
            \Log::error('QC Release Error', [
                'inventory_id' => $inventoryId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}

