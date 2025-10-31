<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client\Item;
use App\Models\Client\Vendor;
use App\Models\Client\StorageType;
use App\Models\Client\ItemCategory;
use App\Services\ItemImportExportService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    protected $importExportService;

    public function __construct(ItemImportExportService $importExportService)
    {
        $this->importExportService = $importExportService;
    }

    /**
     * Display a listing of items
     */
    public function index(Request $request)
    {
        $query = Item::query();

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        // Filter by vendor
        if ($request->filled('vendor_id')) {
            $query->byVendor($request->vendor_id);
        }

        // Filter active
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === '1');
        }

        // Filter hazmat
        if ($request->filled('is_hazmat')) {
            $query->hazmat();
        }

        $items = $query->with(['vendor', 'preferredStorageType', 'categoryModel'])->paginate(20);
        $vendors = Vendor::active()->get();
        $categories = ItemCategory::active()->root()->get();
        $storageTypes = StorageType::active()->get();

        return view('tenant.items.index', compact('items', 'vendors', 'categories', 'storageTypes'));
    }

    /**
     * Show the form for creating a new item
     */
    public function create()
    {
        $vendors = Vendor::active()->get();
        $categories = ItemCategory::active()->get();
        $storageTypes = StorageType::active()->get();

        return view('tenant.items.create', compact('vendors', 'categories', 'storageTypes'));
    }

    /**
     * Store a newly created item
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_code' => 'required|string|max:50|unique:items,item_code',
            'description' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:100',
            'category_id' => 'nullable|exists:item_categories,_id',
            'category' => 'nullable|string|max:100',
            'subcategory' => 'nullable|string|max:100',
            'uom' => 'required|string|max:20',
            'weight' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'volume' => 'nullable|numeric|min:0',
            'preferred_storage_type_id' => 'nullable|exists:storage_types,_id',
            'preferred_bin_id' => 'nullable|exists:bins,_id',
            'vendor_id' => 'nullable|exists:vendors,_id',
            'alternate_vendors' => 'nullable|array',
            'minimum_order_quantity' => 'nullable|integer|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'reorder_quantity' => 'nullable|integer|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'barcode' => 'nullable|string|max:100',
            'image' => 'nullable|image|max:2048',
            'is_hazmat' => 'nullable|boolean',
            'hazmat_class' => 'nullable|string|max:50',
            'temperature_controlled' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $dimensions = null;
            if ($request->has('length') || $request->has('width') || $request->has('height')) {
                $dimensions = [
                    'length' => $request->length ?? 0,
                    'width' => $request->width ?? 0,
                    'height' => $request->height ?? 0,
                    'unit' => 'cm'
                ];
            }

            // Handle image upload
            $imageUrl = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('items', 'public');
                $imageUrl = Storage::url($imagePath);
            }

            $item = Item::create([
                'item_code' => $request->item_code,
                'description' => $request->description,
                'short_description' => $request->short_description,
                'category_id' => $request->category_id,
                'category' => $request->category,
                'subcategory' => $request->subcategory,
                'uom' => $request->uom,
                'weight' => $request->weight,
                'dimensions' => $dimensions,
                'volume' => $request->volume,
                'preferred_storage_type_id' => $request->preferred_storage_type_id,
                'preferred_bin_id' => $request->preferred_bin_id,
                'vendor_id' => $request->vendor_id,
                'alternate_vendors' => $request->alternate_vendors ?? [],
                'minimum_order_quantity' => $request->minimum_order_quantity,
                'reorder_level' => $request->reorder_level,
                'reorder_quantity' => $request->reorder_quantity,
                'unit_price' => $request->unit_price,
                'currency' => $request->currency ?? 'USD',
                'barcode' => $request->barcode,
                'image_url' => $imageUrl,
                'is_active' => true,
                'is_hazmat' => $request->has('is_hazmat'),
                'hazmat_class' => $request->hazmat_class,
                'temperature_controlled' => $request->has('temperature_controlled'),
                'created_by' => session('user._id'),
            ]);

            Log::info('Item: Created successfully', ['item_id' => $item->_id]);

            return redirect()->route('tenant.items.index')
                ->with('success', 'Item created successfully.');

        } catch (\Exception $e) {
            Log::error('Item: Creation failed', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->with('error', 'Failed to create item: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified item
     */
    public function show($id)
    {
        $item = Item::with(['vendor', 'preferredStorageType', 'preferredBin', 'categoryModel'])->findOrFail($id);

        return view('tenant.items.show', compact('item'));
    }

    /**
     * Show the form for editing the specified item
     */
    public function edit($id)
    {
        $item = Item::findOrFail($id);
        $vendors = Vendor::active()->get();
        $categories = ItemCategory::active()->get();
        $storageTypes = StorageType::active()->get();

        return view('tenant.items.edit', compact('item', 'vendors', 'categories', 'storageTypes'));
    }

    /**
     * Update the specified item
     */
    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'item_code' => 'required|string|max:50|unique:items,item_code,' . $id . ',_id',
            'description' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:100',
            'category_id' => 'nullable|exists:item_categories,_id',
            'category' => 'nullable|string|max:100',
            'subcategory' => 'nullable|string|max:100',
            'uom' => 'required|string|max:20',
            'weight' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'volume' => 'nullable|numeric|min:0',
            'preferred_storage_type_id' => 'nullable|exists:storage_types,_id',
            'preferred_bin_id' => 'nullable|exists:bins,_id',
            'vendor_id' => 'nullable|exists:vendors,_id',
            'alternate_vendors' => 'nullable|array',
            'minimum_order_quantity' => 'nullable|integer|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'reorder_quantity' => 'nullable|integer|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'barcode' => 'nullable|string|max:100',
            'image' => 'nullable|image|max:2048',
            'is_hazmat' => 'nullable|boolean',
            'hazmat_class' => 'nullable|string|max:50',
            'temperature_controlled' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $dimensions = null;
            if ($request->has('length') || $request->has('width') || $request->has('height')) {
                $dimensions = [
                    'length' => $request->length ?? 0,
                    'width' => $request->width ?? 0,
                    'height' => $request->height ?? 0,
                    'unit' => 'cm'
                ];
            }

            $updateData = [
                'item_code' => $request->item_code,
                'description' => $request->description,
                'short_description' => $request->short_description,
                'category_id' => $request->category_id,
                'category' => $request->category,
                'subcategory' => $request->subcategory,
                'uom' => $request->uom,
                'weight' => $request->weight,
                'dimensions' => $dimensions,
                'volume' => $request->volume,
                'preferred_storage_type_id' => $request->preferred_storage_type_id,
                'preferred_bin_id' => $request->preferred_bin_id,
                'vendor_id' => $request->vendor_id,
                'alternate_vendors' => $request->alternate_vendors ?? [],
                'minimum_order_quantity' => $request->minimum_order_quantity,
                'reorder_level' => $request->reorder_level,
                'reorder_quantity' => $request->reorder_quantity,
                'unit_price' => $request->unit_price,
                'currency' => $request->currency,
                'barcode' => $request->barcode,
                'is_hazmat' => $request->has('is_hazmat'),
                'hazmat_class' => $request->hazmat_class,
                'temperature_controlled' => $request->has('temperature_controlled'),
            ];

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image
                if ($item->image_url) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $item->image_url));
                }
                $imagePath = $request->file('image')->store('items', 'public');
                $updateData['image_url'] = Storage::url($imagePath);
            }

            $item->update($updateData);

            return redirect()->route('tenant.items.index')
                ->with('success', 'Item updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update item: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified item
     */
    public function destroy($id)
    {
        $item = Item::findOrFail($id);

        // Check if item has inventory (can be added later)
        // if ($item->inventory()->count() > 0) {
        //     return redirect()->back()
        //         ->with('error', 'Cannot delete item with existing inventory.');
        // }

        // Delete image
        if ($item->image_url) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $item->image_url));
        }

        $item->delete();

        return redirect()->route('tenant.items.index')
            ->with('success', 'Item deleted successfully.');
    }

    /**
     * Show import form
     */
    public function showImport()
    {
        return view('tenant.items.import');
    }

    /**
     * Handle item import
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt',
            'update_existing' => 'nullable|boolean',
            'skip_first_row' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $file = $request->file('file');
            $filePath = $file->storeAs('imports', 'items_' . time() . '.csv');

            $fullPath = storage_path('app/' . $filePath);

            $result = $this->importExportService->importFromCSV($fullPath, [
                'update_existing' => $request->has('update_existing'),
                'skip_first_row' => $request->has('skip_first_row'),
            ]);

            // Clean up file
            unlink($fullPath);

            if ($result['errors'] > 0) {
                return redirect()->back()
                    ->with('warning', "Import completed with {$result['success']} successful and {$result['errors']} errors.")
                    ->with('import_details', $result['details']);
            }

            return redirect()->route('tenant.items.index')
                ->with('success', "Successfully imported {$result['success']} items.");

        } catch (\Exception $e) {
            Log::error('Item: Import failed', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Download import template
     */
    public function downloadTemplate()
    {
        try {
            $filePath = $this->importExportService->generateTemplate();
            
            return response()->download($filePath, 'item_import_template.csv')
                           ->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to generate template: ' . $e->getMessage());
        }
    }

    /**
     * Export items
     */
    public function export(Request $request)
    {
        try {
            $query = Item::query();

            // Apply filters if provided
            if ($request->filled('category')) {
                $query->byCategory($request->category);
            }

            if ($request->filled('vendor_id')) {
                $query->byVendor($request->vendor_id);
            }

            if ($request->filled('is_active')) {
                $query->where('is_active', $request->is_active === '1');
            }

            $items = $query->get();
            $filePath = $this->importExportService->exportToCSV($items);

            return response()->download($filePath, 'items_export_' . date('Y-m-d_His') . '.csv')
                           ->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Item: Export failed', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    /**
     * Toggle item status
     */
    public function toggleStatus($id)
    {
        $item = Item::findOrFail($id);
        $item->update(['is_active' => !$item->is_active]);

        return redirect()->back()
            ->with('success', 'Item status updated successfully.');
    }
}

