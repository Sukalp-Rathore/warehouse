<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client\Vendor;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class VendorController extends Controller
{
    /**
     * Display a listing of vendors
     */
    public function index(Request $request)
    {
        $query = Vendor::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('vendor_code', 'like', "%{$search}%")
                  ->orWhere('vendor_name', 'like', "%{$search}%")
                  ->orWhere('contact_email', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->filled('vendor_type')) {
            $query->where('vendor_type', $request->vendor_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter pre-barcode vendors
        if ($request->filled('prebarcode_only')) {
            $query->where('allow_prebarcode', true);
        }

        $vendors = $query->paginate(15);

        return view('tenant.vendors.index', compact('vendors'));
    }

    /**
     * Show the form for creating a new vendor
     */
    public function create()
    {
        return view('tenant.vendors.create');
    }

    /**
     * Store a newly created vendor
     */
    public function store(Request $request)
    {
        // Convert empty strings to null for portal fields if portal is not enabled
        if (!$request->has('vendor_portal_enabled') || $request->vendor_portal_enabled != '1') {
            if (empty($request->portal_login)) {
                $request->merge(['portal_login' => null]);
            }
            if (empty($request->portal_password)) {
                $request->merge(['portal_password' => null]);
            }
        }

        $validator = Validator::make($request->all(), [
            'vendor_code' => 'required|string|max:50|unique:vendors,vendor_code',
            'vendor_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'address' => 'nullable|array',
            'vendor_type' => 'nullable|in:Manufacturer,Distributor,Retailer',
            'payment_terms' => 'nullable|string|max:50',
            'tax_id' => 'nullable|string|max:50',
            'allow_prebarcode' => 'nullable|boolean',
            'vendor_portal_enabled' => 'nullable|boolean',
            'portal_login' => 'nullable',
            'portal_password' => 'nullable',
            'rating' => 'nullable|numeric|min:0|max:5',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Only validate portal fields if portal is enabled
        $validator->sometimes('portal_login', 'required|string|max:255', function ($input) {
            return !empty($input->vendor_portal_enabled);
        });

        $validator->sometimes('portal_password', 'required|string|min:6', function ($input) {
            return !empty($input->vendor_portal_enabled);
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $vendor = Vendor::create([
                'vendor_code' => $request->vendor_code,
                'vendor_name' => $request->vendor_name,
                'contact_person' => $request->contact_person,
                'contact_phone' => $request->contact_phone,
                'contact_email' => $request->contact_email,
                'address' => [
                    'street' => $request->input('address.street'),
                    'city' => $request->input('address.city'),
                    'state' => $request->input('address.state'),
                    'country' => $request->input('address.country'),
                    'zipcode' => $request->input('address.zipcode'),
                ],
                'vendor_type' => $request->vendor_type,
                'payment_terms' => $request->payment_terms,
                'tax_id' => $request->tax_id,
                'allow_prebarcode' => $request->has('allow_prebarcode'),
                'vendor_portal_enabled' => $request->has('vendor_portal_enabled'),
                'portal_login' => $request->has('vendor_portal_enabled') ? $request->portal_login : null,
                'portal_password' => $request->has('vendor_portal_enabled') ? $request->portal_password : null,
                'rating' => $request->rating ?? 0,
                'status' => 'active',
                'notes' => $request->notes,
                'is_active' => true,
                'created_by' => session('user._id'),
            ]);

            Log::info('Vendor: Created successfully', ['vendor_id' => $vendor->_id]);

            return redirect()->route('tenant.vendors.index')
                ->with('success', 'Vendor created successfully.');

        } catch (\Exception $e) {
            Log::error('Vendor: Creation failed', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->with('error', 'Failed to create vendor: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified vendor
     */
    public function show($id)
    {
        $vendor = Vendor::findOrFail($id);
        $items = $vendor->items();

        return view('tenant.vendors.show', compact('vendor', 'items'));
    }

    /**
     * Show the form for editing the specified vendor
     */
    public function edit($id)
    {
        $vendor = Vendor::findOrFail($id);

        return view('tenant.vendors.edit', compact('vendor'));
    }

    /**
     * Update the specified vendor
     */
    public function update(Request $request, $id)
    {
        $vendor = Vendor::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'vendor_code' => 'required|string|max:50|unique:vendors,vendor_code,' . $id . ',_id',
            'vendor_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'address' => 'nullable|array',
            'vendor_type' => 'nullable|in:Manufacturer,Distributor,Retailer',
            'payment_terms' => 'nullable|string|max:50',
            'tax_id' => 'nullable|string|max:50',
            'allow_prebarcode' => 'nullable|boolean',
            'vendor_portal_enabled' => 'nullable|boolean',
            'portal_login' => 'nullable|string|max:255',
            'portal_password' => 'nullable|string|min:6',
            'rating' => 'nullable|numeric|min:0|max:5',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $updateData = [
                'vendor_code' => $request->vendor_code,
                'vendor_name' => $request->vendor_name,
                'contact_person' => $request->contact_person,
                'contact_phone' => $request->contact_phone,
                'contact_email' => $request->contact_email,
                'address' => [
                    'street' => $request->input('address.street'),
                    'city' => $request->input('address.city'),
                    'state' => $request->input('address.state'),
                    'country' => $request->input('address.country'),
                    'zipcode' => $request->input('address.zipcode'),
                ],
                'vendor_type' => $request->vendor_type,
                'payment_terms' => $request->payment_terms,
                'tax_id' => $request->tax_id,
                'allow_prebarcode' => $request->has('allow_prebarcode'),
                'vendor_portal_enabled' => $request->has('vendor_portal_enabled'),
                'rating' => $request->rating,
                'notes' => $request->notes,
            ];

            if ($request->filled('portal_login')) {
                $updateData['portal_login'] = $request->portal_login;
            }

            if ($request->filled('portal_password')) {
                $updateData['portal_password'] = $request->portal_password;
            }

            $vendor->update($updateData);

            return redirect()->route('tenant.vendors.index')
                ->with('success', 'Vendor updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update vendor: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified vendor
     */
    public function destroy($id)
    {
        $vendor = Vendor::findOrFail($id);

        if ($vendor->items()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete vendor with associated items.');
        }

        $vendor->delete();

        return redirect()->route('tenant.vendors.index')
            ->with('success', 'Vendor deleted successfully.');
    }

    /**
     * Toggle vendor status
     */
    public function toggleStatus($id)
    {
        $vendor = Vendor::findOrFail($id);
        $vendor->update([
            'is_active' => !$vendor->is_active,
            'status' => $vendor->is_active ? 'inactive' : 'active'
        ]);

        return redirect()->back()
            ->with('success', 'Vendor status updated successfully.');
    }
}

