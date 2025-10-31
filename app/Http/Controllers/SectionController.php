<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client\Section;
use App\Models\Client\Warehouse;
use App\Models\Client\StorageType;
use Illuminate\Support\Facades\Validator;

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

        $sections = $query->orderBy('section_code')->paginate(20);
        $warehouses = Warehouse::active()->get();
        // Only show High Rack storage types for section creation
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
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Section::create([
            'section_code' => $request->section_code,
            'section_name' => $request->section_name,
            'storage_type_id' => $request->storage_type_id,
            'warehouse_id' => $request->warehouse_id,
            'zone_code' => $request->zone_code,
            'description' => $request->description,
            'status' => 'active',
            'is_active' => true,
            'created_by' => session('user._id'),
        ]);

        return redirect()->back()
            ->with('success', 'Section created successfully.');
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

        if ($section->bins()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete section with existing bins.');
        }

        $section->delete();

        return redirect()->back()
            ->with('success', 'Section deleted successfully.');
    }
}

