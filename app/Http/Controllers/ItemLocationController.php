<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ItemLocationService;

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
}

