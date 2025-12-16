<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ItemLocationService;
use App\Models\Client\Item;

class ItemLocationController extends Controller
{
    protected $locationService;

    public function __construct(ItemLocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     * Show locate item interface
     */
    public function locate()
    {
        return view('tenant.items.locate');
    }

    /**
     * AJAX endpoint: Search for item location
     */
    public function search(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:255',
        ]);

        $code = trim($request->code);

        // Try different search methods
        $results = $this->locationService->locateByCode($code);

        if ($results->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No items found with the provided code'
            ]);
        }

        // Group by item
        $grouped = $results->groupBy(function ($result) {
            return (string) $result['item']->_id;
        });

        $formattedResults = $grouped->map(function ($locations, $itemId) {
            $item = $locations->first()['item'];
            return [
                'item' => [
                    'id' => (string) $item->_id,
                    'code' => $item->item_code,
                    'sku' => $item->sku,
                    'description' => $item->description,
                ],
                'locations' => $locations->map(function ($loc) {
                    return [
                        'inventory_id' => (string) $loc['inventory']->_id,
                        'location_path' => $loc['location_path'],
                        'quantity' => $loc['inventory']->available_qty,
                        'total_quantity' => $loc['inventory']->quantity,
                        'status' => $loc['inventory']->status,
                        'batch' => $loc['inventory']->batch ? [
                            'batch_id' => $loc['inventory']->batch->batch_id,
                            'expiry_date' => $loc['inventory']->batch->expiry_date,
                        ] : null,
                        'codes' => [
                            'barcode' => $loc['inventory']->barcode,
                            'qr_code' => $loc['inventory']->qr_code,
                            'rfid_tag' => $loc['inventory']->rfid_tag,
                        ],
                        'match_type' => $loc['match_type'],
                    ];
                })->values(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'results' => $formattedResults,
        ]);
    }

    /**
     * AJAX endpoint: Live search for items (returns up to 10 suggestions)
     */
    public function liveSearch(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:1|max:255',
        ]);

        $query = trim($request->input('query'));

        // Search items by code, SKU, description, or barcode
        $items = Item::where(function($q) use ($query) {
                $q->where('item_code', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('short_description', 'like', "%{$query}%")
                  ->orWhere('barcode', 'like', "%{$query}%");
            })
            ->where('is_active', true)
            ->limit(10)
            ->get(['_id', 'item_code', 'sku', 'description', 'short_description', 'barcode']);

        $suggestions = $items->map(function($item) {
            return [
                'id' => (string) $item->_id,
                'item_code' => $item->item_code,
                'sku' => $item->sku,
                'description' => $item->description,
                'short_description' => $item->short_description,
                'barcode' => $item->barcode,
                'display_text' => $item->item_code . ' - ' . ($item->description ?: $item->short_description ?: 'N/A'),
            ];
        });

        return response()->json([
            'success' => true,
            'suggestions' => $suggestions,
        ]);
    }
}

