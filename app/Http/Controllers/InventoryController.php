<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client\Inventory;
use App\Models\Client\Item;
use App\Models\Client\Warehouse;
use App\Models\Client\Bin;
use App\Models\Client\Batch;
use App\Models\Client\StorageType;
use App\Services\InventoryMovementService;
use App\Services\BarcodeGenerationService;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
    protected $movementService;
    protected $barcodeService;

    public function __construct(
        InventoryMovementService $movementService,
        BarcodeGenerationService $barcodeService
    ) {
        $this->movementService = $movementService;
        $this->barcodeService = $barcodeService;
    }

    /**
     * Get bins with sufficient capacity for putaway (AJAX endpoint)
     */
    public function getBinsForPutaway(Request $request)
    {
        $request->validate([
            'item_id' => 'required',
            'warehouse_id' => 'required',
            'quantity' => 'required|numeric|min:0.01',
        ]);

        $item = Item::find($request->item_id);
        $warehouse = Warehouse::find($request->warehouse_id);

        if (!$item || !$warehouse) {
            return response()->json([
                'success' => false,
                'message' => 'Item or warehouse not found'
            ], 404);
        }

        // Calculate required volume and weight
        $volumePerUnit = $item->volume_per_unit ?? $item->volume ?? 0.01;
        $weightPerUnit = $item->weight_per_unit ?? $item->weight ?? 0.01;
        
        $requiredVolume = $request->quantity * $volumePerUnit;
        $requiredWeight = $request->quantity * $weightPerUnit;

        // Get bins with sufficient capacity
        $bins = Bin::where('warehouse_id', $request->warehouse_id)
            ->where('status', 'available')
            ->where('is_active', true)
            ->with(['storageType', 'section'])
            ->get()
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
                    'bin_name' => $bin->bin_name ?? $bin->bin_code,
                    'storage_type' => $bin->storageType->name ?? 'N/A',
                    'section' => $bin->section->section_name ?? 'N/A',
                    'available_volume' => round($availableVolume, 2),
                    'available_weight' => round($availableWeight, 2),
                    'capacity_utilization' => round($bin->getCapacityUtilization(), 2),
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
     * Display a listing of inventory
     */
    public function index(Request $request)
    {
        $query = Inventory::with(['item', 'warehouse', 'bin', 'batch', 'storageType', 'section']);

        // Apply warehouse filter from session
        $query = \App\Services\DataFilteringService::filterByWarehouse($query);

        // Filters
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        if ($request->filled('bin_id')) {
            $query->where('bin_id', $request->bin_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('batch_id')) {
            $query->where('batch_id', $request->batch_id);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('item_code', 'like', "%{$request->search}%")
                  ->orWhere('barcode', 'like', "%{$request->search}%")
                  ->orWhere('qr_code', 'like', "%{$request->search}%")
                  ->orWhere('rfid_tag', 'like', "%{$request->search}%");
            });
        }

        $inventory = $query->paginate(20);
        
        // Filter warehouses by user's warehouse access
        $user = session('user');
        $userWarehouseAccess = $user['warehouse_access'] ?? [];
        $warehousesQuery = Warehouse::active()->with('plant');
        if (!empty($userWarehouseAccess)) {
            $warehousesQuery->whereIn('_id', $userWarehouseAccess);
        }
        $warehouses = $warehousesQuery->get();
        
        $items = Item::active()->get();

        return view('tenant.inventory.index', compact('inventory', 'warehouses', 'items'));
    }

    /**
     * Show the form for creating a new inventory record (manual placement)
     */
    public function create()
    {
        $items = Item::active()->get();
        
        // Filter warehouses by user's warehouse access and selected warehouse
        $user = session('user');
        $userWarehouseAccess = $user['warehouse_access'] ?? [];
        $warehousesQuery = Warehouse::active()->with('plant');
        if (!empty($userWarehouseAccess)) {
            $warehousesQuery->whereIn('_id', $userWarehouseAccess);
        }
        $warehouses = $warehousesQuery->get();
        
        // Filter bins by selected warehouse
        $binsQuery = Bin::available();
        $binsQuery = \App\Services\DataFilteringService::filterByWarehouse($binsQuery);
        $bins = $binsQuery->get();

        return view('tenant.inventory.create', compact('items', 'warehouses', 'bins'));
    }

    /**
     * Store a newly created inventory record
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required',
            'warehouse_id' => 'required',
            'bin_id' => 'required',
            'quantity' => 'required|numeric|min:0.01',
            'batch_id' => 'nullable',
            'batch_id_new' => 'nullable|string',
            'manufacturing_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:manufacturing_date',
            'production_lot_number' => 'nullable|string|max:100',
        ], [
            'item_id.required' => 'Please select an item',
            'warehouse_id.required' => 'Please select a warehouse',
            'bin_id.required' => 'Please select a bin',
            'quantity.required' => 'Quantity is required',
            'quantity.min' => 'Quantity must be greater than 0',
        ]);

        $validator->after(function ($validator) use ($request) {
            // Validate item exists
            $item = Item::find($request->item_id);
            if (!$item) {
                $validator->errors()->add('item_id', 'Selected item is invalid');
                return;
            }

            // Validate warehouse exists
            $warehouse = Warehouse::find($request->warehouse_id);
            if (!$warehouse) {
                $validator->errors()->add('warehouse_id', 'Selected warehouse is invalid');
                return;
            }

            // Validate bin exists and belongs to warehouse
            $bin = Bin::find($request->bin_id);
            if (!$bin) {
                $validator->errors()->add('bin_id', 'Selected bin is invalid');
                return;
            }

            if ((string) $bin->warehouse_id !== (string) $request->warehouse_id) {
                $validator->errors()->add('bin_id', 'Selected bin does not belong to the selected warehouse');
                return;
            }

            // Validate batch if provided
            if ($request->filled('batch_id') && $request->batch_id !== 'new') {
                $batch = Batch::find($request->batch_id);
                if (!$batch || (string) $batch->item_id !== (string) $request->item_id) {
                    $validator->errors()->add('batch_id', 'Selected batch is invalid or does not belong to the item');
                    return;
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Handle batch creation if needed
            $batchId = $request->batch_id;
            if ($request->batch_id === 'new' && $request->filled('batch_id_new')) {
                $batch = Batch::create([
                    'batch_id' => $request->batch_id_new,
                    'item_id' => $request->item_id,
                    'item_code' => Item::find($request->item_id)->item_code,
                    'manufacturing_date' => $request->manufacturing_date,
                    'expiry_date' => $request->expiry_date,
                    'production_lot_number' => $request->production_lot_number,
                    'quantity_received' => $request->quantity,
                    'status' => 'active',
                    'created_by' => session('user._id'),
                ]);
                $batchId = (string) $batch->_id;
            }

            // Generate barcode/QR/RFID codes with warehouse and bin IDs for uniqueness
            $item = Item::find($request->item_id);
            $codes = $this->barcodeService->generateAllCodes(
                $item->item_code,
                $request->warehouse_id,
                $request->bin_id
            );
            
            // Additional validation: Ensure codes are unique
            $barcodeExists = \App\Models\Client\Inventory::where('barcode', $codes['barcode'])->exists();
            $qrCodeExists = \App\Models\Client\Inventory::where('qr_code', $codes['qr_code'])->exists();
            $rfidTagExists = \App\Models\Client\Inventory::where('rfid_tag', $codes['rfid_tag'])->exists();
            
            if ($barcodeExists || $qrCodeExists || $rfidTagExists) {
                // Regenerate codes if duplicates found (shouldn't happen with new logic, but safety check)
                $codes = $this->barcodeService->generateAllCodes(
                    $item->item_code,
                    $request->warehouse_id,
                    $request->bin_id
                );
            }

            // Execute putaway
            $result = $this->movementService->putaway(
                $request->warehouse_id,
                $request->bin_id,
                $request->item_id,
                $request->quantity,
                $batchId,
                null,
                null,
                [
                    'barcode' => $codes['barcode'],
                    'qr_code' => $codes['qr_code'],
                    'rfid_tag' => $codes['rfid_tag'],
                ]
            );

            if (!$result['success']) {
                return redirect()->back()
                    ->withErrors(['error' => $result['message']])
                    ->withInput();
            }

            return redirect()->route('tenant.inventory.index')
                ->with('success', 'Item placed successfully in bin');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to place item: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified inventory record
     */
    public function show($id)
    {
        $inventory = Inventory::with([
            'item',
            'warehouse',
            'warehouse.plant',
            'storageType',
            'section',
            'bin',
            'batch'
        ])->findOrFail($id);

        // Get transaction history
        $transactions = \App\Models\Client\InventoryTransaction::where('item_id', $inventory->item_id)
            ->where(function($q) use ($inventory) {
                $q->where('from_bin_id', $inventory->bin_id)
                  ->orWhere('to_bin_id', $inventory->bin_id);
            })
            ->orderBy('timestamp', 'desc')
            ->limit(50)
            ->get();

        return view('tenant.inventory.show', compact('inventory', 'transactions'));
    }

    /**
     * Show transfer form
     */
    public function transferForm($id)
    {
        $inventory = Inventory::with(['item', 'bin', 'warehouse'])->findOrFail($id);
        // Don't pre-load bins - they will be loaded dynamically based on quantity
        $bins = collect(); // Empty collection, will be populated via AJAX

        return view('tenant.inventory.transfer', compact('inventory', 'bins'));
    }

    /**
     * Get destination bins with sufficient capacity for inventory transfer (AJAX endpoint)
     */
    public function getDestinationBinsForTransfer(Request $request)
    {
        $inventoryId = $request->input('inventory_id');
        $quantity = $request->input('quantity', 0);
        
        if (!$inventoryId) {
            return response()->json([
                'success' => false,
                'message' => 'Inventory ID is required'
            ], 400);
        }

        if ($quantity <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Quantity is required'
            ], 400);
        }

        $inventory = Inventory::with(['item', 'bin'])->find($inventoryId);
        if (!$inventory) {
            return response()->json([
                'success' => false,
                'message' => 'Inventory record not found'
            ], 404);
        }

        $item = $inventory->item;
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

        // Get all available bins in the same warehouse (excluding source bin)
        $query = Bin::where('warehouse_id', $inventory->warehouse_id)
            ->where('status', 'available')
            ->where('is_active', true)
            ->where('_id', '!=', $inventory->bin_id)
            ->with(['warehouse', 'storageType', 'section']);

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
                    'bin_name' => $bin->bin_name ?? $bin->bin_code,
                    'storage_type' => $bin->storageType->name ?? 'N/A',
                    'section' => $bin->section->section_name ?? 'N/A',
                    'available_volume' => round($availableVolume, 2),
                    'available_weight' => round($availableWeight, 2),
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
     * Execute transfer
     */
    public function transfer(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'to_bin_id' => 'required',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
        ]);

        $validator->after(function ($validator) use ($request, $id) {
            $inventory = Inventory::with('item')->find($id);
            if (!$inventory) {
                $validator->errors()->add('inventory', 'Inventory record not found');
                return;
            }

            if ($request->quantity > $inventory->available_qty) {
                $validator->errors()->add('quantity', 'Quantity exceeds available quantity');
                return;
            }

            $bin = Bin::find($request->to_bin_id);
            if (!$bin || (string) $bin->warehouse_id !== (string) $inventory->warehouse_id) {
                $validator->errors()->add('to_bin_id', 'Invalid destination bin');
                return;
            }

            // Check bin capacity
            if ($inventory->item) {
                $item = $inventory->item;
                $volumePerUnit = $item->volume_per_unit ?? $item->volume ?? 0.01;
                $weightPerUnit = $item->weight_per_unit ?? $item->weight ?? 0.01;
                
                $requiredVolume = $request->quantity * $volumePerUnit;
                $requiredWeight = $request->quantity * $weightPerUnit;
                
                $availableVolume = ($bin->max_volume ?? 0) - ($bin->current_volume ?? 0);
                $availableWeight = ($bin->max_weight ?? 0) - ($bin->current_weight ?? 0);
                
                if ($availableVolume < $requiredVolume) {
                    $validator->errors()->add('to_bin_id', 'Destination bin does not have sufficient volume capacity. Required: ' . round($requiredVolume, 2) . ', Available: ' . round($availableVolume, 2));
                    return;
                }
                
                if ($availableWeight < $requiredWeight) {
                    $validator->errors()->add('to_bin_id', 'Destination bin does not have sufficient weight capacity. Required: ' . round($requiredWeight, 2) . ', Available: ' . round($availableWeight, 2));
                    return;
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $inventory = Inventory::find($id);
            $result = $this->movementService->transfer(
                $inventory->bin_id,
                $request->to_bin_id,
                $inventory->item_id,
                $request->quantity,
                $inventory->batch_id,
                null,
                $request->notes,
                $inventory->_id // Pass the specific inventory record ID
            );

            if (!$result['success']) {
                return redirect()->back()
                    ->withErrors(['error' => $result['message']])
                    ->withInput();
            }

            return redirect()->route('tenant.inventory.show', $id)
                ->with('success', 'Items transferred successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Transfer failed: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show adjustment form
     */
    public function adjustForm($id)
    {
        $inventory = Inventory::with(['item', 'bin', 'warehouse'])->findOrFail($id);
        return view('tenant.inventory.adjust', compact('inventory'));
    }

    /**
     * Execute adjustment
     */
    public function adjust(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'adjustment_type' => 'required|in:increase,decrease',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:500',
        ]);

        $validator->after(function ($validator) use ($request, $id) {
            $inventory = Inventory::find($id);
            if (!$inventory) {
                $validator->errors()->add('inventory', 'Inventory record not found');
                return;
            }

            if ($request->adjustment_type === 'decrease' && $request->quantity > $inventory->available_qty) {
                $validator->errors()->add('quantity', 'Quantity exceeds available quantity');
                return;
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $inventory = Inventory::find($id);
            $quantity = $request->adjustment_type === 'increase' 
                ? $request->quantity 
                : -$request->quantity;

            $result = $this->movementService->adjustment(
                $inventory->bin_id,
                $inventory->item_id,
                abs($quantity),
                $inventory->batch_id,
                $request->reason,
                $request->adjustment_type
            );

            if (!$result['success']) {
                return redirect()->back()
                    ->withErrors(['error' => $result['message']])
                    ->withInput();
            }

            return redirect()->route('tenant.inventory.show', $id)
                ->with('success', 'Stock adjustment completed successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Adjustment failed: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Move to QC hold
     */
    public function qcHold(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'hold_bin_id' => 'nullable',
            'notes' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $result = $this->movementService->qcHold(
                $id,
                $request->hold_bin_id,
                $request->notes
            );

            if (!$result['success']) {
                return redirect()->back()
                    ->withErrors(['error' => $result['message']]);
            }

            return redirect()->route('tenant.inventory.show', $id)
                ->with('success', 'Item moved to QC hold');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'QC hold failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Release from QC
     */
    public function qcRelease(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'destination_bin_id' => 'required',
            'notes' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $result = $this->movementService->qcRelease(
                $id,
                $request->destination_bin_id,
                $request->notes
            );

            if (!$result['success']) {
                return redirect()->back()
                    ->withErrors(['error' => $result['message']]);
            }

            // Redirect to the correct inventory record (might be different if transferred to new bin)
            $inventoryId = $result['inventory']->_id ?? $id;
            
            return redirect()->route('tenant.inventory.show', $inventoryId)
                ->with('success', 'Item released from QC hold');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'QC release failed: ' . $e->getMessage()]);
        }
    }
}

