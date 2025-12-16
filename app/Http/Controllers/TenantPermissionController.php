<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client\Permission;
use Illuminate\Support\Str;

class TenantPermissionController extends Controller
{
    /**
     * Display a listing of permissions.
     */
    public function index(Request $request)
    {
        $query = Permission::query();

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $permissions = $query->orderBy('label')->paginate(15)->withQueryString();

        $categories = Permission::select('category')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();

        return view('tenant.permissions.index', compact('permissions', 'categories'));
    }

    /**
     * Show the form for creating a new permission.
     */
    public function create()
    {
        return view('tenant.permissions.create');
    }

    /**
     * Store a newly created permission.
     */
    public function store(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'code' => 'nullable|string|max:255|unique:permissions,code',
            'description' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        $code = $request->code ?? Str::slug($request->label);

        Permission::create([
            'label' => $request->label,
            'code' => Str::lower($code),
            'description' => $request->description,
            'category' => $request->category,
            'is_active' => $request->boolean('is_active', true),
            'created_by' => session('user_id'),
        ]);

        // Clear permission cache to refresh the list
        \App\Helpers\PermissionHelper::clearPermissionCache();

        return redirect()->route('tenant.permissions.index')
            ->with('success', 'Permission created successfully.');
    }

    /**
     * Show the form for editing the specified permission.
     */
    public function edit($id)
    {
        $permission = Permission::findOrFail($id);

        return view('tenant.permissions.edit', compact('permission'));
    }

    /**
     * Update the specified permission.
     */
    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        $request->validate([
            'label' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        $permission->update([
            'label' => $request->label,
            'description' => $request->description,
            'category' => $request->category,
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Clear permission cache to refresh the list
        \App\Helpers\PermissionHelper::clearPermissionCache();

        return redirect()->route('tenant.permissions.index')
            ->with('success', 'Permission updated successfully.');
    }

    /**
     * Remove the specified permission.
     */
    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);

        // Optional: prevent delete if permission is used by roles/users (not implemented yet)
        $permission->delete();

        // Clear permission cache to refresh the list
        \App\Helpers\PermissionHelper::clearPermissionCache();

        return redirect()->route('tenant.permissions.index')
            ->with('success', 'Permission deleted successfully.');
    }

    /**
     * Toggle permission status.
     */
    public function toggleStatus($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->is_active = !$permission->is_active;
        $permission->save();

        // Clear permission cache to refresh the list
        \App\Helpers\PermissionHelper::clearPermissionCache();

        return redirect()->back()
            ->with('success', 'Permission status updated successfully.');
    }
}

