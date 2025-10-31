<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client\Warehouse;
use App\Models\Client\Plant;
use App\Models\Client\StorageType;
use App\Services\BinGenerationService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class WarehouseController extends Controller
{
    protected $binGenerationService;

    public function __construct(BinGenerationService $binGenerationService)
    {
        $this->binGenerationService = $binGenerationService;
    }

    /**
     * Display a listing of warehouses
     */
    public function index(Request $request)
    {
        $query = Warehouse::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('warehouse_code', 'like', "%{$search}%")
                  ->orWhere('warehouse_name', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Filter by plant
        if ($request->filled('plant_id')) {
            $query->where('plant_id', $request->plant_id);
        }

        // Filter by type
        if ($request->filled('warehouse_type')) {
            $query->where('warehouse_type', $request->warehouse_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $warehouses = $query->with('plant')->paginate(15);
        $plants = Plant::active()->get();

        return view('tenant.warehouses.index', compact('warehouses', 'plants'));
    }

    /**
     * Show the form for creating a new warehouse
     */
    public function create()
    {
        $plants = Plant::active()->get();
        $storageTypes = StorageType::active()->get();
        
        return view('tenant.warehouses.create', compact('plants', 'storageTypes'));
    }

    /**
     * Store a newly created warehouse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'warehouse_code' => 'required|string|max:50|unique:warehouses,warehouse_code',
            'warehouse_name' => 'required|string|max:255',
            'plant_id' => 'required',
            'address' => 'required|string|max:500',
            'warehouse_type' => 'required|in:FG,RM,Cold Storage,Quarantine,Transit,Distribution',
            'storage_types' => 'nullable|array',
            'storage_types.*' => 'string',
            'capacity' => 'nullable|integer|min:1',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'description' => 'nullable|string|max:1000',
            'generate_bins' => 'nullable|boolean',
            'bin_count' => 'required_if:generate_bins,1|integer|min:1|max:1000',
            'bin_storage_type' => 'nullable|required_if:generate_bins,1|string',
            'bin_prefix' => 'nullable|string|max:10',
            'bin_volume' => 'nullable|numeric|min:0.1',
        ]);

        // MongoDB-safe existence checks
        $validator->after(function ($v) use ($request) {
            if (!$request->filled('plant_id') || !Plant::find($request->plant_id)) {
                $v->errors()->add('plant_id', 'Selected plant id is invalid.');
            }

            if (is_array($request->storage_types)) {
                foreach ($request->storage_types as $idx => $stId) {
                    if (!StorageType::find($stId)) {
                        $v->errors()->add("storage_types.$idx", 'One or more selected storage types are invalid.');
                        break;
                    }
                }
            }

            if ($request->has('generate_bins') && $request->generate_bins) {
                if (!$request->filled('bin_storage_type') || !StorageType::find($request->bin_storage_type)) {
                    $v->errors()->add('bin_storage_type', 'Selected storage type for bins is invalid.');
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $plant = Plant::findOrFail($request->plant_id);

            $warehouse = Warehouse::create([
                'warehouse_code' => $request->warehouse_code,
                'warehouse_name' => $request->warehouse_name,
                'name' => $request->warehouse_name, // For compatibility
                'plant_id' => $request->plant_id,
                'plant_code' => $plant->plant_code,
                'address' => $request->address,
                'warehouse_type' => $request->warehouse_type,
                'status' => 'active',
                'storage_types' => $request->storage_types ?? [],
                'description' => $request->description,
                'capacity' => $request->capacity ?? 1000,
                'current_utilization' => 0,
                'contact_person' => $request->contact_person,
                'contact_phone' => $request->contact_phone,
                'contact_email' => $request->contact_email,
                'is_active' => true,
                'created_by' => session('user._id'),
            ]);

            // Auto-generate bins if requested
            if ($request->has('generate_bins') && $request->generate_bins) {
                $binPrefix = $request->bin_prefix ?? 'BIN-';
                $binCount = $request->bin_count ?? 50;
                $binVolume = $request->bin_volume ?? 1.0;

                $this->binGenerationService->generateBins(
                    $warehouse->_id,
                    $request->bin_storage_type,
                    $binCount,
                    $binPrefix,
                    [
                        'bin_volume' => $binVolume,
                        'assign_zones' => true,
                        'generate_coordinates' => false
                    ]
                );

                Log::info('Warehouse: Bins auto-generated', [
                    'warehouse_id' => $warehouse->_id,
                    'bin_count' => $binCount
                ]);
            }

            Log::info('Warehouse: Created successfully', [
                'warehouse_id' => $warehouse->_id,
                'warehouse_code' => $warehouse->warehouse_code
            ]);

            return redirect()->route('tenant.warehouses.index')
                ->with('success', 'Warehouse created successfully.' . 
                       ($request->has('generate_bins') && $request->generate_bins ? ' ' . $request->bin_count . ' bins generated.' : ''));

        } catch (\Exception $e) {
            Log::error('Warehouse: Creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to create warehouse: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified warehouse
     */
    public function show($id)
    {
        $warehouse = Warehouse::with(['plant', 'bins', 'sections'])->findOrFail($id);
        $storageTypes = StorageType::where('warehouse_id', $id)->orWhereNull('warehouse_id')->get();
        
        return view('tenant.warehouses.show', compact('warehouse', 'storageTypes'));
    }

    /**
     * Show the form for editing the specified warehouse
     */
    public function edit($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $plants = Plant::active()->get();
        $storageTypes = StorageType::active()->get();

        return view('tenant.warehouses.edit', compact('warehouse', 'plants', 'storageTypes'));
    }

    /**
     * Update the specified warehouse
     */
    public function update(Request $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'warehouse_code' => 'required|string|max:50|unique:warehouses,warehouse_code,' . $id . ',_id',
            'warehouse_name' => 'required|string|max:255',
            'plant_id' => 'required',
            'address' => 'required|string|max:500',
            'warehouse_type' => 'required|in:FG,RM,Cold Storage,Quarantine,Transit,Distribution',
            'storage_types' => 'nullable|array',
            'storage_types.*' => 'string',
            'capacity' => 'nullable|integer|min:1',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        // MongoDB-safe existence checks
        $validator->after(function ($v) use ($request) {
            if (!$request->filled('plant_id') || !Plant::find($request->plant_id)) {
                $v->errors()->add('plant_id', 'Selected plant id is invalid.');
            }

            if (is_array($request->storage_types)) {
                foreach ($request->storage_types as $idx => $stId) {
                    if (!StorageType::find($stId)) {
                        $v->errors()->add("storage_types.$idx", 'One or more selected storage types are invalid.');
                        break;
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
            $plant = Plant::findOrFail($request->plant_id);

            $warehouse->update([
                'warehouse_code' => $request->warehouse_code,
                'warehouse_name' => $request->warehouse_name,
                'name' => $request->warehouse_name,
                'plant_id' => $request->plant_id,
                'plant_code' => $plant->plant_code,
                'address' => $request->address,
                'warehouse_type' => $request->warehouse_type,
                'storage_types' => $request->storage_types ?? [],
                'description' => $request->description,
                'capacity' => $request->capacity,
                'contact_person' => $request->contact_person,
                'contact_phone' => $request->contact_phone,
                'contact_email' => $request->contact_email,
            ]);

            Log::info('Warehouse: Updated successfully', [
                'warehouse_id' => $warehouse->_id
            ]);

            return redirect()->route('tenant.warehouses.index')
                ->with('success', 'Warehouse updated successfully.');

        } catch (\Exception $e) {
            Log::error('Warehouse: Update failed', [
                'error' => $e->getMessage(),
                'warehouse_id' => $id
            ]);

            return redirect()->back()
                ->with('error', 'Failed to update warehouse: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified warehouse
     */
    public function destroy($id)
    {
        $warehouse = Warehouse::findOrFail($id);

        // Check if warehouse has bins
        if ($warehouse->bins()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete warehouse with existing bins. Please remove bins first.');
        }

        try {
            $warehouse->delete();

            Log::info('Warehouse: Deleted successfully', [
                'warehouse_id' => $id
            ]);

            return redirect()->route('tenant.warehouses.index')
                ->with('success', 'Warehouse deleted successfully.');

        } catch (\Exception $e) {
            Log::error('Warehouse: Deletion failed', [
                'error' => $e->getMessage(),
                'warehouse_id' => $id
            ]);

            return redirect()->back()
                ->with('error', 'Failed to delete warehouse: ' . $e->getMessage());
        }
    }

    /**
     * Toggle warehouse status
     */
    public function toggleStatus($id)
    {
        $warehouse = Warehouse::findOrFail($id);

        $warehouse->update([
            'is_active' => !$warehouse->is_active,
            'status' => $warehouse->is_active ? 'inactive' : 'active'
        ]);

        return redirect()->back()
            ->with('success', 'Warehouse status updated successfully.');
    }
}

