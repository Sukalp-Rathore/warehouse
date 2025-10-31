<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client\ItemCategory;
use Illuminate\Support\Facades\Validator;

class ItemCategoryController extends Controller
{
    /**
     * Display a listing of categories
     */
    public function index()
    {
        $categories = ItemCategory::with('parent', 'children')->active()->get();
        
        // Group by root categories
        $rootCategories = $categories->whereNull('parent_id');
        
        return view('tenant.categories.index', compact('rootCategories', 'categories'));
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_code' => 'required|string|max:50|unique:item_categories,category_code',
            'category_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'parent_id' => 'nullable|exists:item_categories,_id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        ItemCategory::create([
            'category_code' => strtoupper($request->category_code),
            'category_name' => $request->category_name,
            'description' => $request->description,
            'parent_id' => $request->parent_id,
            'is_active' => true,
            'created_by' => session('user._id'),
        ]);

        return redirect()->back()
            ->with('success', 'Category created successfully.');
    }

    /**
     * Update category
     */
    public function update(Request $request, $id)
    {
        $category = ItemCategory::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'category_code' => 'required|string|max:50|unique:item_categories,category_code,' . $id . ',_id',
            'category_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'parent_id' => 'nullable|exists:item_categories,_id|different:' . $id,
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Prevent circular reference
        if ($request->parent_id) {
            $parent = ItemCategory::find($request->parent_id);
            if ($parent && $parent->parent_id == $id) {
                return redirect()->back()
                    ->with('error', 'Cannot set parent to its own child.')
                    ->withInput();
            }
        }

        $category->update($request->only(['category_code', 'category_name', 'description', 'parent_id']));

        return redirect()->back()
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove category
     */
    public function destroy($id)
    {
        $category = ItemCategory::findOrFail($id);

        if ($category->children()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete category with subcategories.');
        }

        if ($category->items()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete category with associated items.');
        }

        $category->delete();

        return redirect()->back()
            ->with('success', 'Category deleted successfully.');
    }
}

