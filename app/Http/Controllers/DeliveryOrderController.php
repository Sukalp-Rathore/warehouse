<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client\DeliveryOrder;
use App\Models\Client\Item;
use App\Models\Client\Warehouse;
use App\Services\DeliveryOrderService;
use App\Services\PickingService;
use Illuminate\Support\Facades\Validator;

class DeliveryOrderController extends Controller
{
    protected $doService;
    protected $pickingService;

    public function __construct(
        DeliveryOrderService $doService,
        PickingService $pickingService
    ) {
        $this->doService = $doService;
        $this->pickingService = $pickingService;
    }

    /**
     * Display a listing of delivery orders
     */
    public function index(Request $request)
    {
        $query = DeliveryOrder::with(['warehouse']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('do_number', 'like', "%{$request->search}%")
                  ->orWhere('customer_name', 'like', "%{$request->search}%")
                  ->orWhere('customer_code', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        $deliveryOrders = $query->orderBy('created_at', 'desc')->paginate(20);
        $warehouses = Warehouse::active()->get();

        return view('tenant.delivery-orders.index', compact('deliveryOrders', 'warehouses'));
    }

    /**
     * Show the form for creating a new delivery order
     */
    public function create()
    {
        $warehouses = Warehouse::active()->with('plant')->get();
        $items = Item::active()->get();

        return view('tenant.delivery-orders.create', compact('warehouses', 'items'));
    }

    /**
     * Store a newly created delivery order
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'customer_code' => 'nullable|string|max:100',
            'warehouse_id' => 'required',
            'delivery_date' => 'nullable|date',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'picking_strategy' => 'nullable|in:fifo,fefo,wave,zone',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Prepare items array
        $items = [];
        foreach ($request->items as $item) {
            $itemModel = Item::find($item['item_id']);
            $items[] = [
                'item_id' => $item['item_id'],
                'item_code' => $itemModel->item_code ?? 'N/A',
                'quantity' => $item['quantity'],
                'uom' => $itemModel->uom ?? 'PCS',
                'picked_qty' => 0,
                'packed_qty' => 0,
            ];
        }

        $result = $this->doService->create([
            'customer_name' => $request->customer_name,
            'customer_code' => $request->customer_code,
            'customer_address' => $request->customer_address ?? [],
            'warehouse_id' => $request->warehouse_id,
            'delivery_date' => $request->delivery_date,
            'priority' => $request->priority ?? 'normal',
            'picking_strategy' => $request->picking_strategy ?? 'fifo',
            'sap_reference' => $request->sap_reference,
            'items' => $items,
        ]);

        if ($result['success']) {
            return redirect()->route('tenant.delivery-orders.show', $result['delivery_order']->_id)
                ->with('success', 'Delivery order created successfully');
        } else {
            return redirect()->back()
                ->withErrors(['error' => $result['message']])
                ->withInput();
        }
    }

    /**
     * Display the specified delivery order
     */
    public function show($id)
    {
        $do = DeliveryOrder::with(['warehouse', 'pickingTasks', 'shipment', 'createdBy'])->findOrFail($id);
        
        // Load item details
        $itemIds = collect($do->items)->pluck('item_id')->toArray();
        $items = Item::whereIn('_id', $itemIds)->get()->keyBy('_id');

        return view('tenant.delivery-orders.show', compact('do', 'items'));
    }

    /**
     * Generate picking tasks for DO
     */
    public function generatePickingTasks(Request $request, $id)
    {
        $do = DeliveryOrder::findOrFail($id);

        if ($do->status !== 'created') {
            return redirect()->back()
                ->with('error', 'Picking tasks can only be generated for created DOs');
        }

        $result = $this->pickingService->generatePickingTasks($id, $do->picking_strategy ?? 'fifo');

        if ($result['success']) {
            $do->updateStatus('picked');
            return redirect()->route('tenant.delivery-orders.show', $id)
                ->with('success', "Generated {$result['count']} picking tasks");
        } else {
            return redirect()->back()
                ->with('error', $result['message']);
        }
    }
}

