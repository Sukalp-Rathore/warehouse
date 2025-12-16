<?php

namespace App\Services;

use App\Models\Client\TransferOrder;
use App\Models\Client\Inventory;
use App\Models\Client\Bin;
use App\Models\Client\Item;
use App\Services\TransferOrderService;
use Illuminate\Support\Facades\DB;

class ReplenishmentService
{
    protected $transferOrderService;

    public function __construct(TransferOrderService $transferOrderService)
    {
        $this->transferOrderService = $transferOrderService;
    }

    /**
     * Auto-generate transfer orders for bins that need replenishment
     * Checks if inventory.available_qty < bin.reorder_level
     */
    public function generateReplenishmentTOs($warehouseId = null, $binId = null)
    {
        $generated = [];
        $errors = [];

        // Build query for bins that need replenishment
        $binQuery = Bin::query()
            ->where('is_active', true)
            ->whereNotNull('reorder_level')
            ->where('reorder_level', '>', 0);

        if ($warehouseId) {
            $binQuery->where('warehouse_id', $warehouseId);
        }

        if ($binId) {
            $binQuery->where('_id', $binId);
        }

        $bins = $binQuery->get();

        foreach ($bins as $bin) {
            // Get all inventory items in this bin
            $inventoryItems = Inventory::where('bin_id', $bin->_id)
                ->where('status', 'available')
                ->get();

            foreach ($inventoryItems as $inventory) {
                // Check if available quantity is below reorder level
                if ($inventory->available_qty < $bin->reorder_level) {
                    // Find a source bin with sufficient stock
                    $sourceBin = $this->findSourceBin(
                        $inventory->item_id,
                        $bin->warehouse_id,
                        $bin->_id,
                        $bin->reorder_quantity ?? ($bin->reorder_level * 2)
                    );

                    if ($sourceBin) {
                        // Check if TO already exists for this replenishment
                        $existingTO = TransferOrder::where('to_bin_id', $bin->_id)
                            ->where('item_id', $inventory->item_id)
                            ->where('status', '!=', 'completed')
                            ->where('status', '!=', 'cancelled')
                            ->where('auto_generated', true)
                            ->first();

                        if (!$existingTO) {
                            // Create transfer order
                            $result = $this->transferOrderService->create([
                                'from_bin_id' => $sourceBin['bin_id'],
                                'to_bin_id' => $bin->_id,
                                'item_id' => $inventory->item_id,
                                'batch_id' => $inventory->batch_id,
                                'quantity' => $bin->reorder_quantity ?? ($bin->reorder_level * 2),
                                'priority' => 'normal',
                                'auto_generated' => true,
                                'reason' => 'replenishment',
                                'notes' => "Auto-generated: Available qty ({$inventory->available_qty}) below reorder level ({$bin->reorder_level})"
                            ]);

                            if ($result['success']) {
                                $generated[] = $result['transfer_order'];
                            } else {
                                $errors[] = [
                                    'bin_id' => (string) $bin->_id,
                                    'item_id' => (string) $inventory->item_id,
                                    'error' => $result['message']
                                ];
                            }
                        }
                    } else {
                        $errors[] = [
                            'bin_id' => (string) $bin->_id,
                            'item_id' => (string) $inventory->item_id,
                            'error' => 'No source bin found with sufficient stock'
                        ];
                    }
                }
            }
        }

        return [
            'success' => true,
            'generated' => $generated,
            'count' => count($generated),
            'errors' => $errors
        ];
    }

    /**
     * Find a source bin with sufficient stock for replenishment
     */
    private function findSourceBin($itemId, $warehouseId, $excludeBinId, $requiredQuantity)
    {
        // Find bins in the same warehouse with available stock
        $sourceInventory = Inventory::where('item_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->where('bin_id', '!=', $excludeBinId)
            ->where('status', 'available')
            ->where('available_qty', '>=', $requiredQuantity)
            ->with('bin')
            ->get()
            ->filter(function($inv) {
                return $inv->bin && $inv->bin->is_active && $inv->bin->status === 'available';
            })
            ->sortByDesc('available_qty')
            ->first();

        if ($sourceInventory) {
            return [
                'bin_id' => $sourceInventory->bin_id,
                'bin_code' => $sourceInventory->bin->bin_code ?? null,
                'available_qty' => $sourceInventory->available_qty
            ];
        }

        return null;
    }

    /**
     * Check if a bin needs replenishment
     */
    public function needsReplenishment($binId, $itemId)
    {
        $bin = Bin::find($binId);
        
        if (!$bin || !$bin->reorder_level || $bin->reorder_level <= 0) {
            return false;
        }

        $inventory = Inventory::where('bin_id', $binId)
            ->where('item_id', $itemId)
            ->where('status', 'available')
            ->first();

        if (!$inventory) {
            return true; // No inventory means it needs replenishment
        }

        return $inventory->available_qty < $bin->reorder_level;
    }
}

