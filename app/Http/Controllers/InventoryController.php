<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Inventory;

class InventoryController extends Controller
{
    //
    public function inventory()
    {
        // Fetch all products
        $products = Inventory::get();
    
        // Dynamically calculate total stock amount
        $totalStockAmount = 0;
        foreach ($products as $product) {
            $totalStockAmount += $product->selling_price * $product->quantity; // Calculate dynamically
        }
    
        return view('inventory', compact('products', 'totalStockAmount'));
    }

    public function createInventory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string|max:255',
            'selling_price' => 'nullable|numeric',
            'product_image' => 'nullable|image|max:2048', // max 2MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $base64Image = null;

        if ($request->hasFile('product_image')) {
            $image = $request->file('product_image');
            $imageData = file_get_contents($image->getRealPath());
            $base64Image = 'data:' . $image->getMimeType() . ';base64,' . base64_encode($imageData);
        }

        $product = new Inventory();
        $product->product_name = $request->product_name;
        $product->type = $request->type;
        $product->color = $request->color;
        $product->selling_price = (int)$request->selling_price;
        $product->product_image = $base64Image; // base64 image stored in MongoDB
        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Product added successfully'
        ]);
    }

    public function showImage(Request $request)
    {
        $request->validate([
            'productId' => 'required|string',
        ]);

        $product = Inventory::where('_id', $request->productId)->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found']);
        }

        return response()->json([
            'success' => true,
            'product_image' => $product->product_image, // Base64 image or URL
        ]);
    }

    public function showEditInventory(Request $request)
    {
        $request->validate([
            'productId' => 'required|string',
        ]);

        $product = Inventory::where('_id', $request->productId)->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found']);
        }

        return view('edit-inventory', compact('product'));
    }

    public function updateInventory(Request $request)
    {
        $request->validate([
            'product_id' => 'required|string',
            'product_name' => 'required|string|max:255',
            'selling_price' => 'required|numeric',
            'product_image' => 'nullable|image|max:2048', // Max 2MB
        ]);

        $product = Inventory::where('_id', $request->product_id)->first();

        if (!$product) {
            return redirect()->back()->with('error', 'Product not found.');
        }

        // Update product details
        $product->product_name = $request->product_name;
        $product->type = $request->type;
        $product->color = $request->color; // Update color
        $product->selling_price = (int)$request->selling_price;

        // Handle image upload
        if ($request->hasFile('product_image')) {
            $image = $request->file('product_image');
            $base64Image = base64_encode(file_get_contents($image->getRealPath()));
            $product->product_image = 'data:' . $image->getMimeType() . ';base64,' . $base64Image;
        }

        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully'
        ]);
    }

    public function deleteInventory(Request $request)
    {
        $request->validate([
            'productId' => 'required',
        ]);
        $product = $request->input('productId');
        Inventory::where('_id', $product)->delete();
        return response()->json(['success' => true, 'message' => 'Product deleted successfully']);
    }

    public function fetchStock()
    {
        $products = Inventory::get(); // Fetch all inventory items
        return view('update-stock-table', compact('products'));
    }

    public function updateStock(Request $request)
    {
        $request->validate([
            'stock' => 'required|array', // Ensure stock is an array
            'stock.*.id' => 'required|string', // Validate each item's ID
            'stock.*.quantity' => 'required|integer|min:0', // Validate each item's quantity
        ]);

        foreach ($request->stock as $item) {
            $product = Inventory::where('_id', $item['id'])->first();
            if ($product) {
                $product->quantity = $product->quantity + (int)$item['quantity'];
                $product->save();
            }
        }

        return response()->json(['success' => true, 'message' => 'Stock updated successfully']);
    }
}
