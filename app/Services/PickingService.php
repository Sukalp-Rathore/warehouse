<?php

namespace App\Services;

use App\Models\Client\DeliveryOrder;
use App\Models\Client\PickingTask;
use App\Models\Client\PickingWave;
use App\Models\Client\Inventory;
use App\Models\Client\Item;
use App\Models\Client\Batch;
use App\Models\Client\Bin;
use App\Services\InventoryMovementService;
use Illuminate\Support\Facades\DB;

class PickingService
{
    protected $movementService;

    public function __construct(InventoryMovementService $movementService)
    {
        $this->movementService = $movementService;
    }

    /**
     * Generate picking tasks for a delivery order
     */
    public function generatePickingTasks($doId, $strategy = 'fifo')
    {
        $do = DeliveryOrder::find($doId);
        
        if (!$do) {
            return [
                'success' => false,
                'message' => 'Delivery order not found'
            ];
        }

        $tasks = [];
        
        foreach ($do->items as $doItem) {
            $itemId = $doItem['item_id'];
            $requiredQty = $doItem['quantity'];
            
            // Allocate inventory based on strategy
            $allocations = $this->allocateInventory($itemId, $requiredQty, $do->warehouse_id, $strategy);
            
            foreach ($allocations as $allocation) {
                $taskNumber = $this->generateTaskNumber();
                
                $task = PickingTask::create([
                    'picking_task_number' => $taskNumber,
                    'do_id' => $doId,
                    'do_number' => $do->do_number,
                    'item_id' => $itemId,
                    'item_code' => $doItem['item_code'] ?? null,
                    'batch_id' => $allocation['batch_id'],
                    'from_bin_id' => $allocation['bin_id'],
                    'bin_code' => $allocation['bin_code'],
                    'quantity' => $allocation['quantity'],
                    'picked_qty' => 0,
                    'status' => 'pending',
                    'sequence' => count($tasks) + 1,
                ]);
                
                $tasks[] = $task;
            }
        }

        return [
            'success' => true,
            'tasks' => $tasks,
            'count' => count($tasks)
        ];
    }

    /**
     * Allocate inventory based on strategy (FIFO/FEFO)
     */
    private function allocateInventory($itemId, $requiredQty, $warehouseId, $strategy = 'fifo')
    {
        $allocations = [];
        $remainingQty = $requiredQty;

        // Get available inventory
        $query = Inventory::where('item_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->where('status', 'available')
            ->where('available_qty', '>', 0)
            ->with(['bin', 'batch']);

        // Apply strategy
        if ($strategy === 'fefo') {
            // First Expiry First Out - sort by expiry date
            $query->join('batches', 'inventory.batch_id', '=', 'batches._id')
                  ->orderBy('batches.expiry_date', 'asc')
                  ->orderBy('inventory.created_at', 'asc');
        } else {
            // FIFO - First In First Out
            $query->orderBy('created_at', 'asc');
        }

        $inventoryRecords = $query->get();

        foreach ($inventoryRecords as $inventory) {
            if ($remainingQty <= 0) {
                break;
            }

            $availableQty = $inventory->available_qty;
            $pickQty = min($availableQty, $remainingQty);

            $allocations[] = [
                'bin_id' => $inventory->bin_id,
                'bin_code' => $inventory->bin->bin_code ?? 'N/A',
                'batch_id' => $inventory->batch_id,
                'quantity' => $pickQty,
            ];

            $remainingQty -= $pickQty;
        }

        if ($remainingQty > 0) {
            throw new \Exception("Insufficient inventory. Required: {$requiredQty}, Available: " . ($requiredQty - $remainingQty));
        }

        return $allocations;
    }

    /**
     * Execute picking task
     */
    public function executePicking($taskId, $pickedQty, $userId)
    {
        DB::connection('tenant')->beginTransaction();

        try {
            $task = PickingTask::find($taskId);
            
            if (!$task) {
                throw new \Exception('Picking task not found');
            }

            if ($task->status === 'completed') {
                throw new \Exception('Task already completed');
            }

            // Validate picked quantity
            if ($pickedQty > $task->quantity) {
                throw new \Exception('Picked quantity cannot exceed required quantity');
            }

            // Execute pick using InventoryMovementService
            $result = $this->movementService->pick(
                $task->from_bin_id,
                $task->item_id,
                $pickedQty,
                $task->batch_id,
                $task->do_number,
                "Picking for DO: {$task->do_number}"
            );

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            // Update task
            $task->complete($userId, $pickedQty);

            // Update DO item picked quantity
            $do = $task->deliveryOrder;
            $items = $do->items;
            foreach ($items as &$item) {
                if ($item['item_id'] == $task->item_id) {
                    $item['picked_qty'] = ($item['picked_qty'] ?? 0) + $pickedQty;
                    break;
                }
            }
            $do->items = $items;
            
            // Update DO status if all items are picked
            if ($do->isFullyPicked()) {
                $do->updateStatus('picked');
            }
            
            $do->save();

            DB::connection('tenant')->commit();

            return [
                'success' => true,
                'task' => $task
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
     * Create picking wave
     */
    public function createWave($doIds, $assignedTo = null)
    {
        $waveNumber = $this->generateWaveNumber();

        $wave = PickingWave::create([
            'wave_number' => $waveNumber,
            'do_ids' => $doIds,
            'status' => 'created',
            'assigned_to' => $assignedTo,
        ]);

        // Generate picking tasks for all DOs in wave
        foreach ($doIds as $doId) {
            $this->generatePickingTasks($doId);
        }

        return [
            'success' => true,
            'wave' => $wave
        ];
    }

    /**
     * Generate unique task number
     */
    private function generateTaskNumber()
    {
        $prefix = 'PICK-';
        $lastTask = PickingTask::orderBy('created_at', 'desc')->first();
        
        if ($lastTask && preg_match('/' . $prefix . '(\d+)/', $lastTask->picking_task_number, $matches)) {
            $nextNumber = (int)$matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique wave number
     */
    private function generateWaveNumber()
    {
        $prefix = 'WAVE-';
        $lastWave = PickingWave::orderBy('created_at', 'desc')->first();
        
        if ($lastWave && preg_match('/' . $prefix . '(\d+)/', $lastWave->wave_number, $matches)) {
            $nextNumber = (int)$matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
}

