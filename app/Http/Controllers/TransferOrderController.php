<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client\TransferOrder;
use App\Models\Client\Item;
use App\Models\Client\Bin;
use App\Models\Client\Inventory;
use App\Models\Client\User;
use App\Models\Client\Warehouse;
use App\Services\TransferOrderService;
use App\Services\ReplenishmentService;
use Illuminate\Support\Facades\Validator;

class TransferOrderController extends Controller
{
    protected $transferOrderService;
    protected $replenishmentService;

    public function __construct(
        TransferOrderService $transferOrderService,
        ReplenishmentService $replenishmentService
    ) {
        $this->transferOrderService = $transferOrderService;
        $this->replenishmentService = $replenishmentService;
    }

    /**
     * Display a listing of transfer orders
     */
    public function index(Request $request)
    {
        $query = TransferOrder::with(['fromBin', 'toBin', 'item', 'batch', 'assignedTo', 'approvedBy']);

        // Apply warehouse filter from session - filter by source warehouse
        $query = \App\Services\DataFilteringService::filterByWarehouse($query, 'source_warehouse_id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('auto_generated')) {
            $query->where('auto_generated', $request->auto_generated === 'true');
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('to_number', 'like', "%{$request->search}%")
                  ->orWhere('item_code', 'like', "%{$request->search}%");
            });
        }

        $transferOrders = $query->orderBy('created_at', 'desc')->paginate(20);
        $operators = User::where('status', 'active')->get();
        
        // Filter warehouses by user's warehouse access
        $user = session('user');
        $userWarehouseAccess = $user['warehouse_access'] ?? [];
        $warehousesQuery = Warehouse::active()->with('plant');
        if (!empty($userWarehouseAccess)) {
            $warehousesQuery->whereIn('_id', $userWarehouseAccess);
        }
        $warehouses = $warehousesQuery->get();

        return view('tenant.transfer-orders.index', compact('transferOrders', 'operators', 'warehouses'));
    }

    /**
     * Show the form for creating a new transfer order
     */
    public function create()
    {
        $items = Item::active()->get();
        $bins = Bin::available()->with(['warehouse', 'storageType'])->get();
        $operators = User::where('status', 'active')->get();
        $warehouses = Warehouse::active()->with('plant')->get();

        return view('tenant.transfer-orders.create', compact('items', 'bins', 'operators', 'warehouses'));
    }

    /**
     * Get bins for selected item (AJAX endpoint)
     * Returns only bins that currently contain the selected item with sufficient available quantity
     */
    public function getBinsForItem(Request $request)
    {
        $itemId = $request->input('item_id');
        $quantity = $request->input('quantity', 0); // Optional quantity filter
        
        if (!$itemId) {
            return response()->json([
                'success' => false,
                'message' => 'Item ID is required'
            ], 400);
        }

        $item = Item::find($itemId);
        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found'
            ], 404);
        }

        // Get all inventory records for this item (available quantities only)
        $inventoryRecords = Inventory::where('item_id', $itemId)
            ->where('status', 'available')
            ->where('available_qty', '>', 0)
            ->get();

        if ($inventoryRecords->isEmpty()) {
            return response()->json([
                'success' => true,
                'bins' => []
            ]);
        }

        // Group by bin and sum available quantities
        $binQuantities = $inventoryRecords
            ->groupBy(function ($record) {
                return (string) $record->bin_id;
            })
            ->map(function ($records) {
                return $records->sum('available_qty');
            });

        // Filter bins by quantity if provided
        if ($quantity > 0) {
            $binQuantities = $binQuantities->filter(function ($availableQty) use ($quantity) {
                return $availableQty >= $quantity;
            });
        }

        if ($binQuantities->isEmpty()) {
            return response()->json([
                'success' => true,
                'bins' => [],
                'message' => $quantity > 0 ? 'No bins have sufficient available quantity' : 'No bins contain this item'
            ]);
        }

        // Keep original ObjectId values for querying bins
        $binIds = $binQuantities->keys();

        // Get full bin details
        $bins = Bin::whereIn('_id', $binIds)
            ->with(['warehouse', 'storageType'])
            ->get()
            ->map(function($bin) use ($binQuantities) {
                $binId = (string) $bin->_id;
                return [
                    '_id' => $binId,
                    'bin_code' => $bin->bin_code,
                    'warehouse_name' => $bin->warehouse->warehouse_name ?? 'N/A',
                    'available_qty' => $binQuantities->get($binId, 0),
                ];
            })
            ->sortByDesc('available_qty')
            ->values();

        return response()->json([
            'success' => true,
            'bins' => $bins
        ]);
    }

    /**
     * Get destination bins with sufficient capacity (AJAX endpoint)
     * Returns bins that can accommodate the transfer quantity
     */
    public function getDestinationBins(Request $request)
    {
        $itemId = $request->input('item_id');
        $quantity = $request->input('quantity', 0);
        $fromBinId = $request->input('from_bin_id'); // Exclude source bin
        
        if (!$itemId) {
            return response()->json([
                'success' => false,
                'message' => 'Item ID is required'
            ], 400);
        }

        if ($quantity <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Quantity is required'
            ], 400);
        }

        $item = Item::find($itemId);
        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found'
            ], 404);
        }

        // Calculate required volume and weight
        $volumePerUnit = $item->volume_per_unit ?? $item->volume ?? 0.01;
        $weightPerUnit = $item->weight_per_unit ?? $item->weight ?? 0.01;
        
        $requiredVolume = $quantity * $volumePerUnit;
        $requiredWeight = $quantity * $weightPerUnit;

        // Get all available bins (excluding source bin if provided)
        $query = Bin::where('status', 'available')
            ->where('is_active', true)
            ->with(['warehouse', 'storageType']);
        
        if ($fromBinId) {
            $query->where('_id', '!=', $fromBinId);
        }

        // Apply warehouse filter from session
        $query = \App\Services\DataFilteringService::filterByWarehouse($query);

        $bins = $query->get()
            ->filter(function($bin) use ($requiredVolume, $requiredWeight) {
                $availableVolume = ($bin->max_volume ?? 0) - ($bin->current_volume ?? 0);
                $availableWeight = ($bin->max_weight ?? 0) - ($bin->current_weight ?? 0);
                return $availableVolume >= $requiredVolume && $availableWeight >= $requiredWeight;
            })
            ->map(function($bin) use ($requiredVolume, $requiredWeight) {
                $availableVolume = ($bin->max_volume ?? 0) - ($bin->current_volume ?? 0);
                $availableWeight = ($bin->max_weight ?? 0) - ($bin->current_weight ?? 0);
                
                return [
                    '_id' => (string) $bin->_id,
                    'bin_code' => $bin->bin_code,
                    'warehouse_name' => $bin->warehouse->warehouse_name ?? 'N/A',
                    'available_volume' => round($availableVolume, 2),
                    'available_weight' => round($availableWeight, 2),
                    'storage_type' => $bin->storageType->name ?? 'N/A',
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'bins' => $bins,
            'required_volume' => round($requiredVolume, 2),
            'required_weight' => round($requiredWeight, 2),
        ]);
    }

    /**
     * Store a newly created transfer order
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_bin_id' => 'required',
            'to_bin_id' => 'required|different:from_bin_id',
            'item_id' => 'required',
            'quantity' => 'required|numeric|min:0.01',
            'batch_id' => 'nullable',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'reason' => 'nullable|in:manual,replenishment,consolidation',
            'assigned_to' => 'nullable',
            'notes' => 'nullable|string|max:500',
        ]);

        $validator->after(function ($validator) use ($request) {
            $fromBin = Bin::find($request->from_bin_id);
            $toBin = Bin::find($request->to_bin_id);
            $item = Item::find($request->item_id);

            if (!$fromBin) {
                $validator->errors()->add('from_bin_id', 'Source bin not found');
            }

            if (!$toBin) {
                $validator->errors()->add('to_bin_id', 'Destination bin not found');
            }

            if (!$item) {
                $validator->errors()->add('item_id', 'Item not found');
            }

            if ($fromBin && $toBin && $fromBin->warehouse_id !== $toBin->warehouse_id) {
                // Allow cross-warehouse transfers but warn
            }

            // Check source inventory - ensure sufficient quantity is available
            if ($fromBin && $item) {
                // Use the actual bin and item objects' IDs to ensure correct ObjectId matching
                $fromBinId = $fromBin->_id;
                $itemId = $item->_id;
                
                // Get all inventory records first for debugging
                $allInventoryRecords = Inventory::where('bin_id', $fromBinId)
                    ->where('item_id', $itemId)
                    ->get();
                
                // Filter by status and sum manually to ensure accuracy
                $sourceInventory = $allInventoryRecords
                    ->where('status', 'available')
                    ->sum('available_qty');
                
                // Debug logging
                \Log::info('TransferOrderController: Inventory validation', [
                    'bin_id_request' => $request->from_bin_id,
                    'bin_id_used' => (string) $fromBinId,
                    'bin_code' => $fromBin->bin_code,
                    'item_id_request' => $request->item_id,
                    'item_id_used' => (string) $itemId,
                    'item_code' => $item->item_code,
                    'total_records' => $allInventoryRecords->count(),
                    'available_status_records' => $allInventoryRecords->where('status', 'available')->count(),
                    'sum_available_qty' => $sourceInventory,
                    'required_qty' => $request->quantity,
                    'records_detail' => $allInventoryRecords->map(function($inv) {
                        return [
                            'id' => (string) $inv->_id,
                            'bin_id' => (string) $inv->bin_id,
                            'item_id' => (string) $inv->item_id,
                            'status' => $inv->status,
                            'available_qty' => $inv->available_qty,
                            'quantity' => $inv->quantity,
                        ];
                    })->toArray(),
                ]);

                if ($sourceInventory < $request->quantity) {
                    // Check if there's any inventory in the bin at all (any item)
                    $anyInventoryInBin = Inventory::where('bin_id', $fromBinId)->count();
                    
                    // Check if the item exists in other bins
                    $itemInOtherBins = Inventory::where('item_id', $itemId)
                        ->where('status', 'available')
                        ->where('available_qty', '>', 0)
                        ->count();
                    
                    $errorMessage = "Insufficient inventory. Available: {$sourceInventory}. Found {$allInventoryRecords->count()} inventory record(s) in bin.";
                    
                    if ($allInventoryRecords->count() === 0) {
                        if ($anyInventoryInBin === 0) {
                            $errorMessage .= " This bin is empty. Please add inventory to this bin first using Putaway or Inventory Adjustment.";
                        } else {
                            $errorMessage .= " This item is not in this bin. ";
                            if ($itemInOtherBins > 0) {
                                $errorMessage .= "This item exists in other bins. Please select a different source bin or add this item to this bin first.";
                            } else {
                                $errorMessage .= "This item has no inventory in the system. Please add inventory using Putaway or Inventory Adjustment.";
                            }
                        }
                    } else {
                        // Inventory exists but not enough or not available
                        $nonAvailableCount = $allInventoryRecords->where('status', '!=', 'available')->count();
                        if ($nonAvailableCount > 0) {
                            $errorMessage .= " {$nonAvailableCount} record(s) have non-available status (reserved/blocked/qc_hold).";
                        }
                    }
                    
                    $validator->errors()->add('quantity', $errorMessage);
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Get the actual bin and item objects to ensure correct IDs
        $fromBin = Bin::find($request->from_bin_id);
        $toBin = Bin::find($request->to_bin_id);
        $item = Item::find($request->item_id);
        
        $result = $this->transferOrderService->create([
            'from_bin_id' => $fromBin ? $fromBin->_id : $request->from_bin_id,
            'to_bin_id' => $toBin ? $toBin->_id : $request->to_bin_id,
            'item_id' => $item ? $item->_id : $request->item_id,
            'batch_id' => $request->batch_id,
            'quantity' => $request->quantity,
            'priority' => $request->priority ?? 'normal',
            'reason' => $request->reason ?? 'manual',
            'assigned_to' => $request->assigned_to,
            'notes' => $request->notes,
            'auto_generated' => false,
        ]);

        if ($result['success']) {
            return redirect()->route('tenant.transfer-orders.show', $result['transfer_order']->_id)
                ->with('success', 'Transfer order created successfully');
        } else {
            return redirect()->back()
                ->withErrors(['error' => $result['message']])
                ->withInput();
        }
    }

    /**
     * Display the specified transfer order
     */
    public function show($id)
    {
        $transferOrder = TransferOrder::with([
            'fromBin.warehouse',
            'toBin.warehouse',
            'item',
            'batch',
            'assignedTo',
            'approvedBy',
            'createdBy'
        ])->findOrFail($id);

        return view('tenant.transfer-orders.show', compact('transferOrder'));
    }

    /**
     * Show the form for editing the specified transfer order
     */
    public function edit($id)
    {
        $transferOrder = TransferOrder::findOrFail($id);

        if (!in_array($transferOrder->status, ['created', 'approved'])) {
            return redirect()->route('tenant.transfer-orders.show', $id)
                ->with('error', 'Cannot edit transfer order in current status');
        }

        $items = Item::active()->get();
        $bins = Bin::available()->with(['warehouse', 'storageType'])->get();
        $operators = User::where('status', 'active')->get();

        return view('tenant.transfer-orders.edit', compact('transferOrder', 'items', 'bins', 'operators'));
    }

    /**
     * Update the specified transfer order
     */
    public function update(Request $request, $id)
    {
        $transferOrder = TransferOrder::findOrFail($id);

        if (!in_array($transferOrder->status, ['created', 'approved'])) {
            return redirect()->route('tenant.transfer-orders.show', $id)
                ->with('error', 'Cannot update transfer order in current status');
        }

        $validator = Validator::make($request->all(), [
            'priority' => 'nullable|in:low,normal,high,urgent',
            'assigned_to' => 'nullable',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $transferOrder->update([
            'priority' => $request->priority ?? $transferOrder->priority,
            'assigned_to' => $request->assigned_to ?? $transferOrder->assigned_to,
            'notes' => $request->notes ?? $transferOrder->notes,
        ]);

        return redirect()->route('tenant.transfer-orders.show', $id)
            ->with('success', 'Transfer order updated successfully');
    }

    /**
     * Approve transfer order
     */
    public function approve(Request $request, $id)
    {
        $transferOrder = TransferOrder::findOrFail($id);

        if (!$transferOrder->canBeApproved()) {
            return redirect()->back()
                ->with('error', 'Transfer order cannot be approved in current status');
        }

        $result = $this->transferOrderService->approve($id, session('user._id'));

        if ($result['success']) {
            return redirect()->route('tenant.transfer-orders.show', $id)
                ->with('success', 'Transfer order approved successfully');
        } else {
            return redirect()->back()
                ->with('error', $result['message']);
        }
    }

    /**
     * Assign transfer order to operator
     */
    public function assign(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'assigned_to' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $result = $this->transferOrderService->assign($id, $request->assigned_to);

        if ($result['success']) {
            return redirect()->route('tenant.transfer-orders.show', $id)
                ->with('success', 'Transfer order assigned successfully');
        } else {
            return redirect()->back()
                ->with('error', $result['message']);
        }
    }

    /**
     * Execute transfer order
     */
    public function execute(Request $request, $id)
    {
        $transferOrder = TransferOrder::findOrFail($id);

        if (!$transferOrder->canBeExecuted()) {
            return redirect()->back()
                ->with('error', 'Transfer order cannot be executed in current status');
        }

        $result = $this->transferOrderService->execute($id, session('user._id'));

        if ($result['success']) {
            return redirect()->route('tenant.transfer-orders.show', $id)
                ->with('success', 'Transfer order executed successfully');
        } else {
            return redirect()->back()
                ->with('error', $result['message']);
        }
    }

    /**
     * Cancel transfer order
     */
    public function cancel($id)
    {
        $result = $this->transferOrderService->cancel($id);

        if ($result['success']) {
            return redirect()->route('tenant.transfer-orders.index')
                ->with('success', 'Transfer order cancelled successfully');
        } else {
            return redirect()->back()
                ->with('error', $result['message']);
        }
    }

    /**
     * Auto-generate replenishment transfer orders (AJAX)
     */
    public function autoGenerate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'warehouse_id' => 'nullable',
            'bin_id' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->replenishmentService->generateReplenishmentTOs(
            $request->warehouse_id,
            $request->bin_id
        );

        return response()->json($result);
    }
}

