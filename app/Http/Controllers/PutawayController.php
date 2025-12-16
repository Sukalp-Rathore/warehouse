<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PutawayService;
use App\Models\Client\Item;
use App\Models\Client\Bin;

class PutawayController extends Controller
{
    protected $putawayService;

    public function __construct(PutawayService $putawayService)
    {
        $this->putawayService = $putawayService;
    }

    /**
     * AJAX endpoint: Suggest bins for putaway
     */
    public function suggestBins(Request $request)
    {
        $request->validate([
            'item_id' => 'required',
            'quantity' => 'required|numeric|min:0.01',
            'warehouse_id' => 'required',
        ]);

        $suggestions = $this->putawayService->suggestBins(
            $request->item_id,
            $request->quantity,
            $request->warehouse_id
        );

        return response()->json([
            'success' => true,
            'suggestions' => $suggestions->map(function ($suggestion) {
                return [
                    'bin_id' => $suggestion['bin_id'],
                    'bin_code' => $suggestion['bin_code'],
                    'bin_name' => $suggestion['bin_name'],
                    'storage_type' => $suggestion['storage_type'],
                    'section' => $suggestion['section'],
                    'available_volume' => round($suggestion['available_volume'], 2),
                    'available_weight' => round($suggestion['available_weight'], 2),
                    'capacity_utilization' => round($suggestion['capacity_utilization'], 2),
                    'score' => round($suggestion['score'], 2),
                ];
            }),
        ]);
    }

    /**
     * AJAX endpoint: Validate bin for putaway
     */
    public function validateBin(Request $request)
    {
        $request->validate([
            'bin_id' => 'required',
            'item_id' => 'required',
            'quantity' => 'required|numeric|min:0.01',
        ]);

        $result = $this->putawayService->validatePutaway(
            $request->bin_id,
            $request->item_id,
            $request->quantity
        );

        return response()->json($result);
    }
}

