<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client\Bin;
use App\Models\Client\Warehouse;
use App\Models\Client\StorageType;
use App\Models\Client\Section;
use App\Services\BinGenerationService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class BinController extends Controller
{
    protected $binGenerationService;

    public function __construct(BinGenerationService $binGenerationService)
    {
        $this->binGenerationService = $binGenerationService;
    }

    /**
     * Display a listing of bins
     */
    public function index(Request $request)
    {
        $query = Bin::query();

        // Filter by warehouse
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Filter by storage type
        if ($request->filled('storage_type_id')) {
            $query->where('storage_type_id', $request->storage_type_id);
        }

        // Filter by section
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        // Filter by zone
        if ($request->filled('zone_code')) {
            $query->where('zone_code', $request->zone_code);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('bin_code', 'like', "%{$search}%")
                  ->orWhere('bin_name', 'like', "%{$search}%");
            });
        }

        $bins = $query->with(['warehouse', 'storageType', 'section'])->paginate(50);
        $warehouses = Warehouse::active()->get();

        return view('tenant.bins.index', compact('bins', 'warehouses'));
    }

    /**
     * AJAX endpoint: List bins for a warehouse (for dropdowns)
     */
    public function list(Request $request)
    {
        $warehouseId = $request->input('warehouse_id');
        $storageTypeId = $request->input('storage_type_id');
        $status = $request->input('status', 'available'); // Default to available bins
        $includeBinId = $request->input('include_bin_id'); // Include specific bin even if status doesn't match

        if (!$warehouseId) {
            return response()->json(['success' => false, 'message' => 'Warehouse ID is required'], 400);
        }

        $query = Bin::where('warehouse_id', $warehouseId);

        if ($storageTypeId) {
            $query->where('storage_type_id', $storageTypeId);
        }

        // If include_bin_id is provided, we'll get bins matching status OR the specific bin
        if ($status && $includeBinId) {
            $query->where(function($q) use ($status, $includeBinId) {
                $q->where('status', $status)
                  ->orWhere('_id', $includeBinId);
            });
        } elseif ($status) {
            $query->where('status', $status);
        }

        $bins = $query->with(['storageType', 'section'])
                     ->orderBy('bin_code')
                     ->get();

        return response()->json([
            'success' => true,
            'bins' => $bins->map(function ($bin) {
                return [
                    'id' => (string) $bin->_id,
                    'code' => $bin->bin_code,
                    'name' => $bin->bin_name,
                    'storage_type' => $bin->storageType->name ?? 'N/A',
                    'section' => $bin->section->section_name ?? 'N/A',
                    'status' => $bin->status,
                    'available_volume' => $bin->max_volume - $bin->current_volume,
                    'available_weight' => $bin->max_weight - $bin->current_weight,
                ];
            })
        ]);
    }

    /**
     * Show bin generation form
     */
    public function create(Request $request)
    {
        $warehouseId = $request->warehouse_id;
        $warehouse = $warehouseId ? Warehouse::find($warehouseId) : null;
        $warehouses = Warehouse::active()->get();
        $storageTypes = $warehouseId ? StorageType::where('warehouse_id', $warehouseId)->active()->get() : collect();
        $sections = $warehouseId ? Section::where('warehouse_id', $warehouseId)->active()->get() : collect();

        return view('tenant.bins.create', compact('warehouses', 'warehouse', 'storageTypes', 'sections'));
    }

    /**
     * Generate bins
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'warehouse_id' => 'required|string',
            'storage_type_id' => 'required|string',
            'bin_count' => 'required|integer|min:1|max:1000',
            'bin_prefix' => 'nullable|string|max:10',
            'bin_volume' => 'nullable|numeric|min:0.1',
            'bin_weight' => 'nullable|numeric|min:0.1',
            'assign_zones' => 'nullable|boolean',
            'assign_sections' => 'nullable|boolean',
            'section_id' => 'nullable|string',
            'generate_coordinates' => 'nullable|boolean',
            'coordinate_pattern' => 'nullable|in:rack,shelf,flat',
            'start_number' => 'nullable|integer|min:1',
        ]);

        // MongoDB-safe existence checks
        $validator->after(function ($v) use ($request) {
            // Check warehouse
            if ($request->filled('warehouse_id')) {
                $warehouse = Warehouse::find($request->warehouse_id);
                if (!$warehouse) {
                    $v->errors()->add('warehouse_id', 'The selected warehouse id is invalid.');
                }
            }

            // Check storage type
            if ($request->filled('storage_type_id')) {
                $storageType = StorageType::find($request->storage_type_id);
                if (!$storageType) {
                    $v->errors()->add('storage_type_id', 'The selected storage type id is invalid.');
                } else {
                    // Verify storage type belongs to the selected warehouse
                    if ($request->filled('warehouse_id') && $storageType->warehouse_id && $storageType->warehouse_id !== $request->warehouse_id) {
                        $v->errors()->add('storage_type_id', 'The selected storage type does not belong to the selected warehouse.');
                    }
                    
                    // For High Rack storage types, section_id is required
                    $isHighRack = strtolower($storageType->name) === 'high rack' || strtoupper($storageType->code) === 'HIGH_RACK';
                    if ($isHighRack && !$request->filled('section_id')) {
                        $v->errors()->add('section_id', 'Section is required for High Rack storage types.');
                    }
                }
            }

            // Check section if provided (for High Rack)
            if ($request->filled('section_id')) {
                $section = Section::find($request->section_id);
                if (!$section) {
                    $v->errors()->add('section_id', 'The selected section id is invalid.');
                } else {
                    // Verify section belongs to selected storage type
                    if ($request->filled('storage_type_id') && $section->storage_type_id !== $request->storage_type_id) {
                        $v->errors()->add('section_id', 'The selected section does not belong to the selected storage type.');
                    }
                    // Verify section belongs to selected warehouse
                    if ($request->filled('warehouse_id') && $section->warehouse_id !== $request->warehouse_id) {
                        $v->errors()->add('section_id', 'The selected section does not belong to the selected warehouse.');
                    }
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $prefix = $request->bin_prefix ?? 'BIN-';
            $count = $request->bin_count;
            
            $result = $this->binGenerationService->generateBins(
                $request->warehouse_id,
                $request->storage_type_id,
                $count,
                $prefix,
                [
                    'bin_volume' => $request->bin_volume ?? 1.0,
                    'bin_weight' => $request->bin_weight ?? 100.0,
                    'assign_zones' => $request->has('assign_zones'),
                    'assign_sections' => $request->filled('section_id'), // Auto-set based on section_id presence
                    'section_id' => $request->section_id,
                    'generate_coordinates' => $request->has('generate_coordinates'),
                    'coordinate_pattern' => $request->coordinate_pattern ?? 'rack',
                    'start_number' => $request->start_number ?? 1,
                ]
            );

            return redirect()->route('tenant.bins.index', ['warehouse_id' => $request->warehouse_id])
                ->with('success', "Successfully generated {$result['generated']} bins.");

        } catch (\Exception $e) {
            Log::error('Bin: Generation failed', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->with('error', 'Failed to generate bins: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update bin
     */
    public function update(Request $request, $id)
    {
        $bin = Bin::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'bin_code' => 'required|string|max:50',
            'bin_name' => 'required|string|max:255',
            'zone_code' => 'nullable|string|max:10',
            'section_id' => 'nullable|exists:sections,_id',
            'max_volume' => 'nullable|numeric|min:0.1',
            'max_weight' => 'nullable|numeric|min:0.1',
            'status' => 'required|in:available,occupied,reserved,maintenance',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $bin->update($request->only([
            'bin_code', 'bin_name', 'zone_code', 'section_id',
            'max_volume', 'max_weight', 'status'
        ]));

        return redirect()->back()
            ->with('success', 'Bin updated successfully.');
    }

    /**
     * Remove bin
     */
    public function destroy($id)
    {
        $bin = Bin::findOrFail($id);

        if ($bin->status === 'occupied') {
            return redirect()->back()
                ->with('error', 'Cannot delete occupied bin.');
        }

        $bin->delete();

        return redirect()->back()
            ->with('success', 'Bin deleted successfully.');
    }

    /**
     * Bulk operations
     */
    public function bulkOperation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bin_ids' => 'required|array',
            'bin_ids.*' => 'exists:bins,_id',
            'operation' => 'required|in:activate,deactivate,assign_zone,assign_section,delete',
            'zone_code' => 'required_if:operation,assign_zone|string|max:10',
            'section_id' => 'required_if:operation,assign_section|exists:sections,_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $binIds = $request->bin_ids;
            $operation = $request->operation;

            switch ($operation) {
                case 'activate':
                    Bin::whereIn('_id', $binIds)->update(['is_active' => true, 'status' => 'available']);
                    break;
                case 'deactivate':
                    Bin::whereIn('_id', $binIds)->update(['is_active' => false]);
                    break;
                case 'assign_zone':
                    $this->binGenerationService->bulkAssignZones($binIds, $request->zone_code);
                    break;
                case 'assign_section':
                    Bin::whereIn('_id', $binIds)->update(['section_id' => $request->section_id]);
                    break;
                case 'delete':
                    Bin::whereIn('_id', $binIds)->where('status', '!=', 'occupied')->delete();
                    break;
            }

            return response()->json(['success' => true, 'message' => 'Operation completed successfully.']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}

