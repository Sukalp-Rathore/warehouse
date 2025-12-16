<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client\Batch;
use App\Models\Client\Item;
use App\Models\Client\Inventory;

class BatchController extends Controller
{
    /**
     * Display a listing of batches
     */
    public function index(Request $request)
    {
        $query = Batch::with(['item']);

        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('expiring_soon')) {
            $query->expiringSoon((int) $request->expiring_soon);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('batch_id', 'like', "%{$request->search}%")
                  ->orWhere('production_lot_number', 'like', "%{$request->search}%")
                  ->orWhere('vendor_batch_number', 'like', "%{$request->search}%");
            });
        }

        $batches = $query->orderBy('created_at', 'desc')->paginate(20);
        $items = Item::active()->get();

        return view('tenant.batches.index', compact('batches', 'items'));
    }

    /**
     * Display the specified batch
     */
    public function show($id)
    {
        $batch = Batch::with(['item'])->findOrFail($id);
        
        // Get all inventory locations for this batch
        $inventory = Inventory::where('batch_id', $id)
            ->with(['warehouse', 'warehouse.plant', 'storageType', 'section', 'bin'])
            ->get();

        // Calculate totals
        $totalQuantity = $inventory->sum('quantity');
        $availableQuantity = $inventory->sum('available_qty');
        $blockedQuantity = $inventory->sum('blocked_qty');

        return view('tenant.batches.show', compact('batch', 'inventory', 'totalQuantity', 'availableQuantity', 'blockedQuantity'));
    }
}

