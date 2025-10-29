@extends('layout')

@section('title', 'Role Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Role Management</h4>
                        <a href="{{ route('tenant.roles.create') }}" class="btn btn-primary">
                            <i class="ri-add-line me-1"></i> Add New Role
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Roles Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Permissions</th>
                                    <th>Users</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $role)
                                    <tr>
                                        <td>{{ $role->name }}</td>
                                        <td>{{ $role->description ?? 'N/A' }}</td>
                                        <td>
                                            @if($role->permissions && count($role->permissions) > 0)
                                                <span class="badge bg-info">{{ count($role->permissions) }} permissions</span>
                                            @else
                                                <span class="text-muted">No permissions</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $role->users->count() }} users</span>
                                        </td>
                                        <td>
                                            @if($role->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('tenant.roles.show', $role->_id) }}" 
                                                   class="btn btn-sm btn-outline-info" title="View">
                                                    <i class="ri-eye-line"></i>
                                                </a>
                                                <a href="{{ route('tenant.roles.edit', $role->_id) }}" 
                                                   class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="ri-edit-line"></i>
                                                </a>
                                                <form method="POST" action="{{ route('tenant.roles.destroy', $role->_id) }}" 
                                                      class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                            title="Delete" onclick="return confirm('Are you sure you want to delete this role?')">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No roles found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $roles->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
