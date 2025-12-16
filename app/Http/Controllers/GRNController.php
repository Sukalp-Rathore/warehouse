<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client\GRN;
use App\Models\Client\Item;
use App\Models\Client\Vendor;
use App\Models\Client\Warehouse;
use App\Models\Client\Batch;
use App\Services\InventoryMovementService;
use App\Services\BarcodeGenerationService;
use App\Services\SAPIntegrationService;
use Illuminate\Support\Facades\Validator;
use App\Helpers\PermissionHelper;

class GRNController extends Controller
{
    protected $movementService;
    protected $barcodeService;
    protected $sapService;

    public function __construct(
        InventoryMovementService $movementService,
        BarcodeGenerationService $barcodeService,
        SAPIntegrationService $sapService
    ) {
        $this->movementService = $movementService;
        $this->barcodeService = $barcodeService;
        $this->sapService = $sapService;
    }

    /**
     * Display a listing of GRNs
     * Shows pending approval and approved GRNs separately
     */
    public function index(Request $request)
    {
        // Get pending approval GRNs
        $pendingQuery = GRN::with(['vendor', 'warehouse'])
            ->where('status', 'pending');

        // Get approved GRNs
        $approvedQuery = GRN::with(['vendor', 'warehouse'])
            ->where('status', 'approved');

        // Apply filters
        if ($request->filled('warehouse_id')) {
            $pendingQuery->where('warehouse_id', $request->warehouse_id);
            $approvedQuery->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('vendor_id')) {
            $pendingQuery->where('vendor_id', $request->vendor_id);
            $approvedQuery->where('vendor_id', $request->vendor_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $pendingQuery->where(function($q) use ($search) {
                $q->where('grn_number', 'like', "%{$search}%")
                  ->orWhere('po_reference', 'like', "%{$search}%");
            });
            $approvedQuery->where(function($q) use ($search) {
                $q->where('grn_number', 'like', "%{$search}%")
                  ->orWhere('po_reference', 'like', "%{$search}%");
            });
        }

        // Apply warehouse filter from session
        $pendingQuery = \App\Services\DataFilteringService::filterByWarehouse($pendingQuery);
        $approvedQuery = \App\Services\DataFilteringService::filterByWarehouse($approvedQuery);

        $pendingGRNs = $pendingQuery->orderBy('created_at', 'desc')->paginate(10, ['*'], 'pending_page');
        $approvedGRNs = $approvedQuery->orderBy('created_at', 'desc')->paginate(10, ['*'], 'approved_page');
        
        // Filter warehouses by user's warehouse access
        $user = session('user');
        $userWarehouseAccess = $user['warehouse_access'] ?? [];
        $warehousesQuery = Warehouse::active();
        if (!empty($userWarehouseAccess)) {
            $warehousesQuery->whereIn('_id', $userWarehouseAccess);
        }
        $warehouses = $warehousesQuery->get();
        
        $vendors = Vendor::active()->get();

        return view('tenant.grns.index', compact('pendingGRNs', 'approvedGRNs', 'warehouses', 'vendors'));
    }

    /**
     * Sync GRNs from SAP
     */
    public function syncFromSAP(Request $request)
    {
        // Feature gating: ensure tenant's plan includes SAP GRN integration
        if (!PermissionHelper::tenantHasFeature('grn_sap_integration')) {
            return redirect()->route('tenant.grns.index')
                ->withErrors(['error' => 'Your current plan does not include SAP GRN integration. Please contact your administrator.']);
        }

        $status = $request->input('status'); // Optional: pending or approved
        
        $result = $this->sapService->fetchGRNs($status);

        if ($result['success']) {
            return redirect()->route('tenant.grns.index')
                ->with('success', $result['message'])
                ->with('sync_errors', $result['errors'] ?? []);
        } else {
            return redirect()->route('tenant.grns.index')
                ->withErrors(['error' => $result['message']]);
        }
    }


    /**
     * Display the specified GRN
     */
    public function show($id)
    {
        $grn = GRN::with(['vendor', 'warehouse', 'receivedBy', 'approvedBy'])->findOrFail($id);
        
        // Load item details for each GRN item
        $itemIds = collect($grn->items)->pluck('item_id')->toArray();
        $items = Item::whereIn('_id', $itemIds)->get()->keyBy('_id');

        return view('tenant.grns.show', compact('grn', 'items'));
    }

    /**
     * Approve GRN (for pending approval GRNs)
     */
    public function approve(Request $request, $id)
    {
        $grn = GRN::findOrFail($id);

        if ($grn->status !== 'pending') {
            return redirect()->back()
                ->withErrors(['error' => 'GRN cannot be approved in its current status']);
        }

        $grn->update([
            'status' => 'approved',
            'approved_by' => session('user._id'),
            'approved_at' => now(),
        ]);

        return redirect()->route('tenant.grns.index')
            ->with('success', 'GRN approved successfully');
    }

    /**
     * Show putaway interface
     */
    public function putaway($id)
    {
        $grn = GRN::with(['vendor', 'warehouse'])->findOrFail($id);

        if ($grn->status !== 'approved' && $grn->status !== 'in_progress') {
            return redirect()->route('tenant.grns.show', $id)
                ->withErrors(['error' => 'GRN must be approved before putaway']);
        }

        // Load item details
        $itemIds = collect($grn->items)->pluck('item_id')->toArray();
        $items = Item::whereIn('_id', $itemIds)->get()->keyBy('_id');

        return view('tenant.grns.putaway', compact('grn', 'items'));
    }

    /**
     * Execute putaway for a GRN item
     */
    public function executePutaway(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'item_index' => 'required|integer',
            'bin_id' => 'required',
            'quantity' => 'required|numeric|min:0.01',
            'batch_id' => 'nullable',
            'batch_id_new' => 'nullable|string',
            'manufacturing_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:manufacturing_date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $grn = GRN::findOrFail($id);
            $itemIndex = (int) $request->item_index;
            $grnItem = $grn->items[$itemIndex] ?? null;

            if (!$grnItem) {
                return redirect()->back()
                    ->withErrors(['error' => 'Invalid item selection']);
            }

            // Check if quantity is valid
            if ($request->quantity > $grnItem['quantity']) {
                return redirect()->back()
                    ->withErrors(['quantity' => 'Quantity exceeds GRN quantity']);
            }

            // Handle batch creation if needed
            $batchId = $request->batch_id;
            if ($request->batch_id === 'new' && $request->filled('batch_id_new')) {
                $item = Item::find($grnItem['item_id']);
                $batch = Batch::create([
                    'batch_id' => $request->batch_id_new,
                    'item_id' => $grnItem['item_id'],
                    'item_code' => $item->item_code,
                    'manufacturing_date' => $request->manufacturing_date,
                    'expiry_date' => $request->expiry_date,
                    'quantity_received' => $request->quantity,
                    'status' => 'active',
                    'created_by' => session('user._id'),
                ]);
                $batchId = (string) $batch->_id;
            }

            // Generate codes with warehouse and bin IDs for uniqueness
            $item = Item::find($grnItem['item_id']);
            $codes = $this->barcodeService->generateAllCodes(
                $item->item_code,
                $grn->warehouse_id,
                $request->bin_id
            );

            // Execute putaway
            $result = $this->movementService->putaway(
                $grn->warehouse_id,
                $request->bin_id,
                $grnItem['item_id'],
                $request->quantity,
                $batchId,
                $grn->_id,
                $grn->grn_number,
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

            // Update GRN putaway status
            $grn->startPutaway();

            // Check if all items are putaway
            if ($grn->isFullyPutaway()) {
                $grn->complete();
            }

            return redirect()->route('tenant.grns.putaway', $id)
                ->with('success', 'Item putaway completed successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Putaway failed: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Complete GRN
     */
    public function complete($id)
    {
        $grn = GRN::findOrFail($id);
        $grn->complete();

        return redirect()->route('tenant.grns.show', $id)
            ->with('success', 'GRN marked as completed');
    }

}

