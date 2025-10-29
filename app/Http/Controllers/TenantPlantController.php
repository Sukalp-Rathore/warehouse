<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client\Plant;
use Illuminate\Support\Facades\Validator;

class TenantPlantController extends Controller
{
    /**
     * Display a listing of plants
     */
    public function index()
    {
        $plants = Plant::paginate(15);
        return view('tenant.plants.index', compact('plants'));
    }

    /**
     * Show the form for creating a new plant
     */
    public function create()
    {
        return view('tenant.plants.create');
    }

    /**
     * Store a newly created plant
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plant_code' => 'required|string|max:50|unique:plants,plant_code',
            'plant_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'is_active' => 'nullable'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Plant::create([
            'plant_code' => $request->plant_code,
            'plant_name' => $request->plant_name,
            'location' => $request->location,
            'address' => $request->address,
            'contact_person' => $request->contact_person,
            'contact_phone' => $request->contact_phone,
            'contact_email' => $request->contact_email,
            'is_active' => $request->has('is_active'),
            'created_by' => session('user._id')
        ]);

        return redirect()->route('tenant.plants.index')
            ->with('success', 'Plant created successfully.');
    }

    /**
     * Display the specified plant
     */
    public function show($id)
    {
        $plant = Plant::findOrFail($id);
        $users = $plant->users();
        return view('tenant.plants.show', compact('plant', 'users'));
    }

    /**
     * Show the form for editing the specified plant
     */
    public function edit($id)
    {
        $plant = Plant::findOrFail($id);
        return view('tenant.plants.edit', compact('plant'));
    }

    /**
     * Update the specified plant
     */
    public function update(Request $request, $id)
    {
        $plant = Plant::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'plant_code' => 'required|string|max:50|unique:plants,plant_code,' . $id . ',_id',
            'plant_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'is_active' => 'nullable'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $plant->update([
            'plant_code' => $request->plant_code,
            'plant_name' => $request->plant_name,
            'location' => $request->location,
            'address' => $request->address,
            'contact_person' => $request->contact_person,
            'contact_phone' => $request->contact_phone,
            'contact_email' => $request->contact_email,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('tenant.plants.index')
            ->with('success', 'Plant updated successfully.');
    }

    /**
     * Remove the specified plant
     */
    public function destroy($id)
    {
        $plant = Plant::findOrFail($id);
        
        // Check if plant has users assigned
        if ($plant->users()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete plant. Users are currently assigned to this plant.');
        }
        
        $plant->delete();
        
        return redirect()->route('tenant.plants.index')
            ->with('success', 'Plant deleted successfully.');
    }
}
