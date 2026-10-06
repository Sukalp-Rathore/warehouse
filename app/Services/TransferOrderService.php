<?php

namespace App\Services;

use App\Models\Client\TransferOrder;
use App\Models\Client\Inventory;
use App\Models\Client\Bin;
use App\Models\Client\Item;
use App\Services\InventoryMovementService;
use App\Services\PutawayService;
use Illuminate\Support\Facades\DB;

class TransferOrderService
{
    protected $movementService;
    protected $putawayService;

    public function __construct(
        InventoryMovementService $movementService,
        PutawayService $putawayService
    ) {
        $this->movementService = $movementService;
        $this->putawayService = $putawayService;
    }

    /**
     * Create a new transfer order
     */
    public function create($data)
    {
        // Validate bins and item
        $fromBin = Bin::find($data['from_bin_id']);
        $toBin = Bin::find($data['to_bin_id']);
        $item = Item::find($data['item_id']);

        if (!$fromBin || !$toBin || !$item) {
            return [
                'success' => false,
                'message' => 'Invalid bin or item'
            ];
        }
        
        // Log the actual IDs for debugging
        \Log::info('TransferOrderService: Bin and Item validation', [
            'from_bin_id_request' => $data['from_bin_id'],
            'from_bin_id_found' => $fromBin ? (string) $fromBin->_id : 'not found',
            'from_bin_code' => $fromBin ? $fromBin->bin_code : 'N/A',
            'item_id_request' => $data['item_id'],
            'item_id_found' => $item ? (string) $item->_id : 'not found',
            'item_code' => $item ? $item->item_code : 'N/A',
        ]);

        // Check if source bin has sufficient inventory
        // Use the actual bin and item objects' IDs to ensure correct ObjectId matching
        $fromBinId = $fromBin->_id; // Use the found bin's _id
        $itemId = $item->_id; // Use the found item's _id
        
        // First, let's check what inventory actually exists in this bin (for debugging)
        $allBinInventory = Inventory::where('bin_id', $fromBinId)->get();
        $allItemInventory = Inventory::where('item_id', $itemId)->get();
        
        \Log::info('TransferOrderService: Diagnostic queries', [
            'from_bin_id' => $fromBinId,
            'item_id' => $itemId,
            'inventory_in_bin_count' => $allBinInventory->count(),
            'inventory_for_item_count' => $allItemInventory->count(),
            'bin_inventory_items' => $allBinInventory->map(function($inv) {
                return [
                    'item_id' => (string) $inv->item_id,
                    'item_code' => $inv->item_code,
                    'status' => $inv->status,
                    'available_qty' => $inv->available_qty,
                ];
            })->toArray(),
            'item_inventory_bins' => $allItemInventory->map(function($inv) {
                return [
                    'bin_id' => (string) $inv->bin_id,
                    'bin_code' => $inv->bin ? $inv->bin->bin_code : 'N/A',
                    'status' => $inv->status,
                    'available_qty' => $inv->available_qty,
                ];
            })->toArray(),
        ]);
        
        // Get all inventory records first (without status filter to see what exists)
        // MongoDB Laravel should handle ObjectId conversion automatically, but let's ensure it works
        $allInventoryRecords = Inventory::where('bin_id', $fromBinId)
            ->where('item_id', $itemId)
            ->get();
        
        // If still empty, the inventory might not exist in this bin for this item
        // The diagnostic queries above will show what's actually in the bin and what bins have this item

        // Debug: Log the query parameters and results
        \Log::info('TransferOrderService: Checking inventory', [
            'from_bin_id' => $fromBinId,
            'item_id' => $itemId,
            'quantity_required' => $data['quantity'],
            'total_records_found' => $allInventoryRecords->count(),
            'records_detail' => $allInventoryRecords->map(function($inv) {
                return [
                    'id' => (string) $inv->_id,
                    'bin_id' => (string) $inv->bin_id,
                    'item_id' => (string) $inv->item_id,
                    'status' => $inv->status,
                    'available_qty' => $inv->available_qty,
                    'quantity' => $inv->quantity,
                    'batch_id' => $inv->batch_id ? (string) $inv->batch_id : null,
                ];
            })->toArray(),
        ]);

        // Filter by status and batch (if specified) using collection methods
        $filteredInventory = $allInventoryRecords->where('status', 'available');
        
        if (!empty($data['batch_id'])) {
            $filteredInventory = $filteredInventory->where('batch_id', $data['batch_id']);
        }
        
        // Sum available quantities manually (more reliable than MongoDB sum())
        $totalAvailableQty = $filteredInventory->sum('available_qty');
        
        \Log::info('TransferOrderService: Filtered inventory', [
            'batch_id' => $data['batch_id'] ?? 'not specified',
            'available_status_count' => $allInventoryRecords->where('status', 'available')->count(),
            'filtered_count' => $filteredInventory->count(),
            'total_available_qty' => $totalAvailableQty,
        ]);
        
        if ($totalAvailableQty < $data['quantity']) {
            $batchMsg = !empty($data['batch_id']) ? " for specified batch" : "";
            return [
                'success' => false,
                'message' => "Insufficient inventory in source bin{$batchMsg}. Available: {$totalAvailableQty}, Required: {$data['quantity']}. Found {$allInventoryRecords->count()} inventory record(s) in bin, {$filteredInventory->count()} with available status."
            ];
        }
        
        // Get the first inventory record with available quantity (for batch_id reference)
        $sourceInventory = $filteredInventory
            ->where('available_qty', '>', 0)
            ->sortByDesc('available_qty')
            ->first();

        if (!$sourceInventory) {
            return [
                'success' => false,
                'message' => 'No inventory found in source bin'
            ];
        }

        // Validate destination bin capacity
        $validation = $this->putawayService->validatePutaway(
            $data['to_bin_id'],
            $data['item_id'],
            $data['quantity']
        );

        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => $validation['message']
            ];
        }

        // Generate TO number
        $toNumber = $this->generateTONumber();

        // Create transfer order
        $transferOrder = TransferOrder::create([
            'to_number' => $toNumber,
            'from_bin_id' => $data['from_bin_id'],
            'to_bin_id' => $data['to_bin_id'],
            'item_id' => $data['item_id'],
            'item_code' => $item->item_code,
            'batch_id' => $data['batch_id'] ?? $sourceInventory->batch_id,
            'quantity' => $data['quantity'],
            'status' => 'created',
            'priority' => $data['priority'] ?? 'normal',
            'auto_generated' => $data['auto_generated'] ?? false,
            'reason' => $data['reason'] ?? 'manual',
            'assigned_to' => $data['assigned_to'] ?? null,
            'notes' => $data['notes'] ?? null,
            'created_by' => session('user._id'),
        ]);

        return [
            'success' => true,
            'transfer_order' => $transferOrder
        ];
    }

    /**
     * Approve transfer order
     */
    public function approve($toId, $userId)
    {
        $transferOrder = TransferOrder::find($toId);

        if (!$transferOrder) {
            return [
                'success' => false,
                'message' => 'Transfer order not found'
            ];
        }

        if (!$transferOrder->canBeApproved()) {
            return [
                'success' => false,
                'message' => 'Transfer order cannot be approved in current status'
            ];
        }

        $transferOrder->approve($userId);

        return [
            'success' => true,
            'transfer_order' => $transferOrder
        ];
    }

    /**
     * Assign transfer order to operator
     */
    public function assign($toId, $userId)
    {
        $transferOrder = TransferOrder::find($toId);

        if (!$transferOrder) {
            return [
                'success' => false,
                'message' => 'Transfer order not found'
            ];
        }

        $transferOrder->assign($userId);

        return [
            'success' => true,
            'transfer_order' => $transferOrder
        ];
    }

    /**
     * Execute transfer order
     */
    public function execute($toId, $userId = null)
    {
        try {
            $transferOrder = TransferOrder::find($toId);

            if (!$transferOrder) {
                throw new \Exception('Transfer order not found');
            }

            if (!$transferOrder->canBeExecuted()) {
                throw new \Exception('Transfer order cannot be executed in current status');
            }

            // Start execution if not already started
            if ($transferOrder->status === 'approved') {
                $transferOrder->start();
            }

            // Execute transfer using InventoryMovementService
            $result = $this->movementService->transfer(
                $transferOrder->from_bin_id,
                $transferOrder->to_bin_id,
                $transferOrder->item_id,
                $transferOrder->quantity,
                $transferOrder->batch_id,
                $transferOrder->to_number,
                "Transfer Order: {$transferOrder->to_number}"
            );

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            // Complete transfer order
            $transferOrder->complete();

            return [
                'success' => true,
                'transfer_order' => $transferOrder,
                'inventory' => $result['inventory']
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Cancel transfer order
     */
    public function cancel($toId)
    {
        $transferOrder = TransferOrder::find($toId);

        if (!$transferOrder) {
            return [
                'success' => false,
                'message' => 'Transfer order not found'
            ];
        }

        if (in_array($transferOrder->status, ['completed', 'cancelled'])) {
            return [
                'success' => false,
                'message' => 'Transfer order cannot be cancelled in current status'
            ];
        }

        $transferOrder->cancel();

        return [
            'success' => true,
            'transfer_order' => $transferOrder
        ];
    }

    /**
     * Generate unique TO number
     */
    private function generateTONumber()
    {
        $prefix = 'TO-';
        $lastTO = TransferOrder::orderBy('created_at', 'desc')->first();
        
        if ($lastTO && preg_match('/' . $prefix . '(\d+)/', $lastTO->to_number, $matches)) {
            $nextNumber = (int)$matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
}

