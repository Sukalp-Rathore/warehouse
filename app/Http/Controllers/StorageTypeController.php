<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client\StorageType;
use App\Models\Client\Warehouse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class StorageTypeController extends Controller
{
    /**
     * Return sections list for a storage type (JSON)
     */
    public function sections(Request $request)
    {
        $request->validate([
            'storage_type_id' => 'required|string'
        ]);

        $sections = \App\Models\Client\Section::where('storage_type_id', $request->storage_type_id)
            ->orderBy('section_code')
            ->get(['_id','section_code','section_name']);

        // Convert MongoDB ObjectId to string for JSON response
        $sectionsArray = $sections->map(function($section) {
            return [
                '_id' => (string)$section->_id,
                'section_code' => $section->section_code,
                'section_name' => $section->section_name
            ];
        });

        return response()->json($sectionsArray);
    }
    /**
     * Return storage types as JSON (used by AJAX in bin form)
     * Storage types are master data and can be assigned to any warehouse
     */
    public function list(Request $request)
    {
        $types = StorageType::active()->get(['_id','code','name']);

        // Convert MongoDB ObjectId to string for JSON response
        $typesArray = $types->map(function($type) {
            return [
                '_id' => (string)$type->_id,
                'code' => $type->code,
                'name' => $type->name
            ];
        });

        return response()->json($typesArray);
    }

    /**
     * Display a listing of storage types
     */
    public function index(Request $request)
    {
        $query = StorageType::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $storageTypes = $query->orderBy('code')->paginate(15);

        return view('tenant.storage-types.index', compact('storageTypes'));
    }

    /**
     * Store a newly created storage type
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|in:HIGH_RACK,COLD_ROOM|max:50',
            'name' => 'required|string|in:High Rack,Cold Room|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $storageType = StorageType::create([
                'code' => strtoupper($request->code),
                'name' => $request->name,
                'description' => $request->description,
                'status' => 'active',
                'is_active' => true,
            ]);

            return redirect()->back()
                ->with('success', 'Storage type created successfully.');

        } catch (\Exception $e) {
            Log::error('StorageType: Creation failed', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->with('error', 'Failed to create storage type: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update storage type
     */
    public function update(Request $request, $id)
    {
        $storageType = StorageType::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $storageType->update($request->only(['code', 'name', 'description']));

        return redirect()->back()
            ->with('success', 'Storage type updated successfully.');
    }

    /**
     * Remove storage type
     */
    public function destroy($id)
    {
        $storageType = StorageType::findOrFail($id);

        // Check if has bins
        if ($storageType->bins()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete storage type with existing bins.');
        }

        $storageType->delete();

        return redirect()->back()
            ->with('success', 'Storage type deleted successfully.');
    }
}

