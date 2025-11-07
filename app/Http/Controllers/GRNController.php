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
use Illuminate\Support\Facades\Validator;

class GRNController extends Controller
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
     * Display a listing of GRNs
     */
    public function index(Request $request)
    {
        $query = GRN::with(['vendor', 'warehouse']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('grn_number', 'like', "%{$request->search}%")
                  ->orWhere('po_reference', 'like', "%{$request->search}%");
            });
        }

        $grns = $query->orderBy('created_at', 'desc')->paginate(20);
        $warehouses = Warehouse::active()->get();
        $vendors = Vendor::active()->get();

        return view('tenant.grns.index', compact('grns', 'warehouses', 'vendors'));
    }

    /**
     * Show the form for creating a new GRN
     */
    public function create()
    {
        $vendors = Vendor::active()->get();
        $warehouses = Warehouse::active()->with('plant')->get();
        $items = Item::active()->get();

        return view('tenant.grns.create', compact('vendors', 'warehouses', 'items'));
    }

    /**
     * Store a newly created GRN
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendor_id' => 'required',
            'warehouse_id' => 'required',
            'po_reference' => 'nullable|string|max:100',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        $validator->after(function ($validator) use ($request) {
            $vendor = Vendor::find($request->vendor_id);
            if (!$vendor) {
                $validator->errors()->add('vendor_id', 'Selected vendor is invalid');
            }

            $warehouse = Warehouse::find($request->warehouse_id);
            if (!$warehouse) {
                $validator->errors()->add('warehouse_id', 'Selected warehouse is invalid');
            }

            foreach ($request->items as $index => $item) {
                $itemModel = Item::find($item['item_id']);
                if (!$itemModel) {
                    $validator->errors()->add("items.{$index}.item_id", "Item at row " . ($index + 1) . " is invalid");
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Generate GRN number
            $grnNumber = $this->generateGRNNumber();

            $grn = GRN::create([
                'grn_number' => $grnNumber,
                'vendor_id' => $request->vendor_id,
                'warehouse_id' => $request->warehouse_id,
                'po_reference' => $request->po_reference,
                'status' => 'pending',
                'putaway_status' => 'pending',
                'items' => $request->items,
                'created_by' => session('user._id'),
            ]);

            return redirect()->route('tenant.grns.show', $grn->_id)
                ->with('success', 'GRN created successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to create GRN: ' . $e->getMessage()])
                ->withInput();
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
     * Approve GRN
     */
    public function approve(Request $request, $id)
    {
        $grn = GRN::findOrFail($id);

        if ($grn->status !== 'pending') {
            return redirect()->back()
                ->withErrors(['error' => 'GRN cannot be approved in its current status']);
        }

        $grn->approve(session('user._id'));

        return redirect()->route('tenant.grns.show', $id)
            ->with('success', 'GRN approved successfully');
    }

    /**
     * Show putaway interface
     */
    public function putaway($id)
    {
        $grn = GRN::with(['vendor', 'warehouse'])->findOrFail($id);

        if ($grn->status !== 'in_progress' && $grn->status !== 'approved' && $grn->status !== 'partially_putaway') {
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

            // Generate codes
            $item = Item::find($grnItem['item_id']);
            $codes = $this->barcodeService->generateAllCodes(
                $item->item_code,
                null,
                null
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

    /**
     * Generate unique GRN number
     */
    private function generateGRNNumber()
    {
        $year = date('Y');
        $lastGRN = GRN::where('grn_number', 'like', "GRN-{$year}-%")
            ->orderBy('grn_number', 'desc')
            ->first();

        if ($lastGRN) {
            $lastNumber = (int) substr($lastGRN->grn_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('GRN-%s-%04d', $year, $nextNumber);
    }
}

