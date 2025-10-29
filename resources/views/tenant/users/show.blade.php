@extends('layout')

@section('title', 'User Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">User Details: {{ $user->name }}</h4>
                        <div>
                            <a href="{{ route('tenant.users.edit', $user->_id) }}" class="btn btn-primary me-2">
                                <i class="ri-edit-line me-1"></i> Edit User
                            </a>
                            <a href="{{ route('tenant.users.index') }}" class="btn btn-outline-secondary">
                                <i class="ri-arrow-left-line me-1"></i> Back to Users
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
                                    <td><strong>Username:</strong></td>
                                    <td>{{ $user->username }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Full Name:</strong></td>
                                    <td>{{ $user->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Mobile:</strong></td>
                                    <td>{{ $user->mobile ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        @if($user->status === 'active')
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td>{{ $user->created_at ? $user->created_at->format('M d, Y H:i') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Last Updated:</strong></td>
                                    <td>{{ $user->updated_at ? $user->updated_at->format('M d, Y H:i') : 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Role and Permissions -->
                        <div class="col-md-6">
                            <h5 class="mb-3">Role & Permissions</h5>
                            
                            @if($user->role)
                                <div class="mb-3">
                                    <strong>Role:</strong>
                                    <span class="badge bg-info ms-2">{{ $user->role->name }}</span>
                                    @if($user->role->description)
                                        <p class="text-muted mt-1">{{ $user->role->description }}</p>
                                    @endif
                                </div>
                            @else
                                <div class="mb-3">
                                    <strong>Role:</strong>
                                    <span class="text-muted">No role assigned</span>
                                </div>
                            @endif

                            @if($user->permissions && count($user->permissions) > 0)
                                <div class="mb-3">
                                    <strong>Custom Permissions:</strong>
                                    <div class="mt-2">
                                        @foreach($user->permissions as $permission)
                                            <span class="badge bg-secondary me-1 mb-1">{{ $permission }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($user->plant_access && count($user->plant_access) > 0)
                                <div class="mb-3">
                                    <strong>Plant Access:</strong>
                                    <div class="mt-2">
                                        @foreach($user->plants as $plant)
                                            <span class="badge bg-primary me-1 mb-1">
                                                {{ $plant->plant_name }} ({{ $plant->plant_code }})
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="mb-3">
                                    <strong>Plant Access:</strong>
                                    <span class="text-muted">No plants assigned</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Role Permissions (if role exists) -->
                    @if($user->role && $user->role->permissions && count($user->role->permissions) > 0)
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5>Role Permissions</h5>
                                <div class="row">
                                    @foreach($user->role->permissions as $permission)
                                        <div class="col-md-3 mb-2">
                                            <span class="badge bg-info">{{ $permission }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                @if($user->_id !== session('user._id'))
                                    <form method="POST" action="{{ route('tenant.users.toggle-status', $user->_id) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                class="btn {{ $user->status === 'active' ? 'btn-warning' : 'btn-success' }}"
                                                onclick="return confirm('Are you sure?')">
                                            <i class="ri-{{ $user->status === 'active' ? 'pause' : 'play' }}-line me-1"></i>
                                            {{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('tenant.users.destroy', $user->_id) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" 
                                                onclick="return confirm('Are you sure you want to delete this user?')">
                                            <i class="ri-delete-bin-line me-1"></i> Delete User
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
