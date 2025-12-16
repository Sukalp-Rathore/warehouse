<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client\Section;
use App\Models\Client\Warehouse;
use App\Models\Client\StorageType;
use App\Models\Client\Bin;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SectionController extends Controller
{
    /**
     * List sections with filters
     */
    public function index(Request $request)
    {
        $query = Section::query();

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->filled('storage_type_id')) {
            $query->where('storage_type_id', $request->storage_type_id);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s){
                $q->where('section_code', 'like', "%{$s}%")
                  ->orWhere('section_name', 'like', "%{$s}%");
            });
        }

        // Apply warehouse filter from session
        $query = \App\Services\DataFilteringService::filterByWarehouse($query);

        $sections = $query->orderBy('section_code')->paginate(20);
        
        // Filter warehouses by user's warehouse access
        $user = session('user');
        $userWarehouseAccess = $user['warehouse_access'] ?? [];
        $warehousesQuery = Warehouse::active();
        if (!empty($userWarehouseAccess)) {
            $warehousesQuery->whereIn('_id', $userWarehouseAccess);
        }
        $warehouses = $warehousesQuery->get();
        
        // Storage types are master data, filter only by name (High Rack)
        $storageTypes = StorageType::active()->where('name', 'High Rack')->get();

        return view('tenant.sections.index', compact('sections','warehouses','storageTypes'));
    }
    /**
     * Store a newly created section
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'section_code' => 'required|string|max:50',
            'section_name' => 'required|string|max:255',
            'storage_type_id' => 'required|string',
            'warehouse_id' => 'required|string',
            'zone_code' => 'nullable|string|max:10',
            'description' => 'nullable|string|max:500',
            'rows' => 'required|integer|min:1|max:100',
            'columns' => 'required|integer|min:1|max:100',
            'bin_volume' => 'required|numeric|min:0.1',
            'bin_weight' => 'required|numeric|min:0.1',
        ]);

        // MongoDB-safe existence checks
        $validator->after(function ($v) use ($request) {
            $st = StorageType::find($request->storage_type_id);
            if (!$st || strtolower($st->name) !== 'high rack') {
                $v->errors()->add('storage_type_id', 'Sections can only be created for High Rack storage types.');
            }
            if (!Warehouse::find($request->warehouse_id)) {
                $v->errors()->add('warehouse_id', 'Selected warehouse is invalid.');
            }
            
            // Check if section code is unique within warehouse
            $existingSection = Section::where('section_code', $request->section_code)
                ->where('warehouse_id', $request->warehouse_id)
                ->first();
            if ($existingSection) {
                $v->errors()->add('section_code', 'Section code must be unique within the warehouse.');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Create section
            $section = Section::create([
                'section_code' => $request->section_code,
                'section_name' => $request->section_name,
                'storage_type_id' => $request->storage_type_id,
                'warehouse_id' => $request->warehouse_id,
                'zone_code' => $request->zone_code,
                'description' => $request->description,
                'rows' => $request->rows,
                'columns' => $request->columns,
                'bin_volume' => $request->bin_volume,
                'bin_weight' => $request->bin_weight,
                'status' => 'active',
                'is_active' => true,
                'created_by' => session('user._id'),
            ]);

            // Generate bins for the section
            $binsGenerated = $this->generateBinsForSection($section);

            DB::commit();

            return redirect()->back()
                ->with('success', "Section created successfully with {$binsGenerated} bins.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Section creation failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to create section: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Generate bins for a section based on rows and columns
     */
    private function generateBinsForSection($section)
    {
        $binsGenerated = 0;
        $bins = [];

        // Generate bins in nested loop (rows x columns)
        for ($row = 1; $row <= $section->rows; $row++) {
            for ($column = 1; $column <= $section->columns; $column++) {
                // Generate bin code: {section_code}-r{row}c{column}
                $binCode = $section->section_code . '-r' . $row . 'c' . $column;
                
                // Generate bin name: Row {row} Column {column}
                $binName = 'Row ' . $row . ' Column ' . $column;

                // Check if bin code already exists in warehouse
                $existingBin = Bin::where('bin_code', $binCode)
                    ->where('warehouse_id', $section->warehouse_id)
                    ->first();

                if ($existingBin) {
                    Log::warning('Bin code already exists, skipping', [
                        'bin_code' => $binCode,
                        'warehouse_id' => $section->warehouse_id
                    ]);
                    continue;
                }

                // Prepare bin data for batch insert
                $bins[] = [
                    'bin_code' => $binCode,
                    'bin_name' => $binName,
                    'warehouse_id' => $section->warehouse_id,
                    'storage_type_id' => $section->storage_type_id,
                    'section_id' => $section->_id,
                    'zone_code' => $section->zone_code,
                    'row_number' => $row,
                    'column_number' => $column,
                    'max_volume' => $section->bin_volume,
                    'max_weight' => $section->bin_weight,
                    'current_volume' => 0,
                    'current_weight' => 0,
                    'status' => 'available',
                    'is_active' => true,
                    'created_by' => session('user._id'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $binsGenerated++;
            }
        }

        // Batch insert bins for better performance
        if (!empty($bins)) {
            // For MongoDB, we need to insert one by one or use bulk insert
            foreach ($bins as $binData) {
                Bin::create($binData);
            }
        }

        Log::info('Bins generated for section', [
            'section_id' => $section->_id,
            'section_code' => $section->section_code,
            'rows' => $section->rows,
            'columns' => $section->columns,
            'bins_generated' => $binsGenerated
        ]);

        return $binsGenerated;
    }

    /**
     * Update section
     */
    public function update(Request $request, $id)
    {
        $section = Section::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'section_code' => 'required|string|max:50',
            'section_name' => 'required|string|max:255',
            'zone_code' => 'nullable|string|max:10',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $section->update($request->only(['section_code', 'section_name', 'zone_code', 'description']));

        return redirect()->back()
            ->with('success', 'Section updated successfully.');
    }

    /**
     * Remove section
     */
    public function destroy($id)
    {
        $section = Section::findOrFail($id);

        // Check if any bins have inventory
        $binsWithInventory = Bin::where('section_id', $section->_id)
            ->where(function($query) {
                $query->where('current_volume', '>', 0)
                      ->orWhere('current_weight', '>', 0);
            })
            ->count();

        if ($binsWithInventory > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete section with bins that contain inventory.');
        }

        try {
            DB::beginTransaction();

            // Delete all bins in this section
            Bin::where('section_id', $section->_id)->delete();

            // Delete the section
            $section->delete();

            DB::commit();

            return redirect()->back()
                ->with('success', 'Section and all associated bins deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Section deletion failed', [
                'error' => $e->getMessage(),
                'section_id' => $id
            ]);

            return redirect()->back()
                ->with('error', 'Failed to delete section: ' . $e->getMessage());
        }
    }
}

