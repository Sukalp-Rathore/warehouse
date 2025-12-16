@extends('layout')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1>Warehouse Management System Dashboard</h1>
            
            <!-- Debug Information -->
            <div class="alert alert-info">
                <h6>Debug Information:</h6>
                <p><strong>Tenant Info Available:</strong> {{ isset($tenantInfo) ? 'Yes' : 'No' }}</p>
                <p><strong>User Data:</strong> {{ isset($tenantInfo['user']) ? 'Yes' : 'No' }}</p>
                <p><strong>Auth Check:</strong> {{ $tenantInfo['auth_check'] ?? 'Not set' }}</p>
                <p><strong>Tenant Type:</strong> {{ $tenantInfo['type'] ?? 'Not set' }}</p>
                <p><strong>User Role:</strong> {{ $tenantInfo['user']['role'] ?? 'Not set' }}</p>
                <p><strong>Can Access Tenant Management:</strong> 
                    @if(isset($tenantInfo['type']) && $tenantInfo['type'] === 'master' && isset($tenantInfo['user']['role']) && in_array($tenantInfo['user']['role'], ['admin', 'super_admin']))
                        <span class="badge badge-success">YES</span>
                    @else
                        <span class="badge badge-danger">NO</span>
                    @endif
                </p>
            </div>
            
            <!-- Debug Information Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Debug Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Auth Check:</strong> 
                                <span class="badge badge-{{ $tenantInfo['auth_check'] ? 'success' : 'danger' }}">
                                    {{ $tenantInfo['auth_check'] ? 'Authenticated' : 'Not Authenticated' }}
                                </span>
                            </p>
                            <p><strong>User Data:</strong> {{ $tenantInfo['user'] ? 'Present' : 'Missing' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Session Data:</strong> {{ count($tenantInfo['session_data']) }} items</p>
                            <p><strong>Tenant Type:</strong> {{ $tenantInfo['type'] ?? 'Not Set' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current User Information Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-user-line me-2"></i>Current User Information
                    </h5>
                </div>
                <div class="card-body">
                    @if($tenantInfo['user'])
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted small">Full Name</label>
                                    <p class="mb-0 fw-semibold">{{ $tenantInfo['user']['name'] ?? 'N/A' }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted small">Email Address</label>
                                    <p class="mb-0">{{ $tenantInfo['user']['email'] ?? 'N/A' }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted small">User Role</label>
                                    <p class="mb-0">
                                        <span class="badge badge-{{ $tenantInfo['user']['role'] === 'super_admin' ? 'danger' : ($tenantInfo['user']['role'] === 'admin' ? 'primary' : 'info') }}">
                                            {{ ucfirst(str_replace('_', ' ', $tenantInfo['user']['role'] ?? 'N/A')) }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted small">Tenant Type</label>
                                    <p class="mb-0">
                                        <span class="badge badge-{{ $tenantInfo['type'] === 'master' ? 'primary' : 'success' }}">
                                            {{ ucfirst($tenantInfo['type'] ?? 'Unknown') }}
                                        </span>
                                    </p>
                                </div>
                                @if($tenantInfo['type'] === 'client')
                                    <div class="mb-3">
                                        <label class="form-label text-muted small">Client ID</label>
                                        <p class="mb-0 font-monospace">{{ $tenantInfo['client_id'] }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-muted small">Database</label>
                                        <p class="mb-0 font-monospace">{{ $tenantInfo['database'] }}</p>
                                    </div>
                                @endif
                                @if(isset($tenantInfo['user']['permissions']) && is_array($tenantInfo['user']['permissions']))
                                    <div class="mb-3">
                                        <label class="form-label text-muted small">Permissions</label>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($tenantInfo['user']['permissions'] as $permission)
                                                <span class="badge badge-light">{{ $permission }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted">
                            <i class="ri-user-line fs-1 mb-3"></i>
                            <p>No user data available</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- System Information Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-information-line me-2"></i>System Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Authentication Status</label>
                                <p class="mb-0">
                                    <span class="badge badge-{{ $tenantInfo['auth_check'] ? 'success' : 'danger' }}">
                                        {{ $tenantInfo['auth_check'] ? 'Authenticated' : 'Not Authenticated' }}
                                    </span>
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small">Session ID</label>
                                <p class="mb-0 font-monospace small">{{ session()->getId() }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Login Time</label>
                                <p class="mb-0">{{ now()->format('Y-m-d H:i:s') }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small">Session Data</label>
                                <p class="mb-0">{{ count($tenantInfo['session_data']) }} items stored</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Master Admin Controls - Only visible to master admin users -->
            @if(isset($tenantInfo['type']) && $tenantInfo['type'] === 'master' && isset($tenantInfo['user']['role']) && in_array($tenantInfo['user']['role'], ['admin', 'super_admin']))
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="ri-settings-3-line me-2"></i>Master Admin Controls
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-grid gap-2">
                                <a href="{{ route('tenant.onboard') }}" class="btn btn-success btn-lg">
                                    <i class="ri-add-circle-line me-2"></i>Create New Tenant
                                </a>
                                <a href="{{ route('tenant.list') }}" class="btn btn-info btn-lg">
                                    <i class="ri-list-check me-2"></i>Manage Tenants
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-info mb-0">
                                <h6 class="alert-heading">Master Admin Access</h6>
                                <p class="mb-0">You have full access to tenant management features. You can create, view, edit, and delete tenant configurations.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Quick Stats -->
            <div class="row">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Warehouses</h5>
                            <h3 class="text-primary">0</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Inventory Items</h5>
                            <h3 class="text-success">0</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Total Value</h5>
                            <h3 class="text-warning">$0</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Low Stock</h5>
                            <h3 class="text-danger">0</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test Login Information -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Test Login Credentials</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Master Admin</h6>
                            <p><strong>Email:</strong> admin@wms.com</p>
                            <p><strong>Password:</strong> password</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Client Users</h6>
                            <p><strong>ABC Corp Admin:</strong> admin@abccorporation.com / password</p>
                            <p><strong>ABC Corp Manager:</strong> manager@abccorporation.com / password</p>
                            <p><strong>XYZ Industries Admin:</strong> admin@xyzindustries.com / password</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('js')
@endsection