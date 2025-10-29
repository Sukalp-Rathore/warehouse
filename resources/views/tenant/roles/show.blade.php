@extends('layout')

@section('title', 'Role Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Role Details: {{ $role->name }}</h4>
                        <div>
                            <a href="{{ route('tenant.roles.edit', $role->_id) }}" class="btn btn-primary me-2">
                                <i class="ri-edit-line me-1"></i> Edit Role
                            </a>
                            <a href="{{ route('tenant.roles.index') }}" class="btn btn-outline-secondary">
                                <i class="ri-arrow-left-line me-1"></i> Back to Roles
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <h5 class="mb-3">Basic Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Role Name:</strong></td>
                                    <td>{{ $role->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Description:</strong></td>
                                    <td>{{ $role->description ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        @if($role->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td>{{ $role->created_at ? $role->created_at->format('M d, Y H:i') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Last Updated:</strong></td>
                                    <td>{{ $role->updated_at ? $role->updated_at->format('M d, Y H:i') : 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Permissions -->
                        <div class="col-md-6">
                            <h5 class="mb-3">Permissions</h5>
                            @if($role->permissions && count($role->permissions) > 0)
                                <div class="row">
                                    @foreach($role->permissions as $permission)
                                        <div class="col-md-6 mb-2">
                                            <span class="badge bg-info">{{ $permission }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">No permissions assigned to this role.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Users with this role -->
                    @if($role->users && $role->users->count() > 0)
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5>Users with this Role ({{ $role->users->count() }})</h5>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Status</th>
                                                <th>Created</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($role->users as $user)
                                                <tr>
                                                    <td>{{ $user->name }}</td>
                                                    <td>{{ $user->email }}</td>
                                                    <td>
                                                        @if($user->status === 'active')
                                                            <span class="badge bg-success">Active</span>
                                                        @else
                                                            <span class="badge bg-danger">Inactive</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <form method="POST" action="{{ route('tenant.roles.destroy', $role->_id) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" 
                                            onclick="return confirm('Are you sure you want to delete this role?')">
                                        <i class="ri-delete-bin-line me-1"></i> Delete Role
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
