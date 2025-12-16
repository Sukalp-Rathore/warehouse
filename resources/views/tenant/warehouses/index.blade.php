@extends('layout')

@section('title', 'Warehouse Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Warehouse Management</h4>
                        <a href="{{ route('tenant.warehouses.create') }}" class="btn btn-primary">
                            <i class="ri-add-line me-1"></i> Add New Warehouse
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Search and Filter Form -->
                    <form method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="Search warehouses..." 
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="plant_id" class="form-select">
                                    <option value="">All Plants</option>
                                    @foreach($plants as $plant)
                                        <option value="{{ $plant->_id }}" {{ request('plant_id') == $plant->_id ? 'selected' : '' }}>
                                            {{ $plant->plant_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="warehouse_type" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="FG" {{ request('warehouse_type') == 'FG' ? 'selected' : '' }}>FG</option>
                                    <option value="RM" {{ request('warehouse_type') == 'RM' ? 'selected' : '' }}>RM</option>
                                    <option value="Cold Storage" {{ request('warehouse_type') == 'Cold Storage' ? 'selected' : '' }}>Cold Storage</option>
                                    <option value="Quarantine" {{ request('warehouse_type') == 'Quarantine' ? 'selected' : '' }}>Quarantine</option>
                                    <option value="Transit" {{ request('warehouse_type') == 'Transit' ? 'selected' : '' }}>Transit</option>
                                    <option value="Distribution" {{ request('warehouse_type') == 'Distribution' ? 'selected' : '' }}>Distribution</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                            </div>
                        </div>
                    </form>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Warehouses Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Warehouse Code</th>
                                    <th>Warehouse Name</th>
                                    <th>Plant</th>
                                    <th>Type</th>
                                    <th>Address</th>
                                    <th>Capacity</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($warehouses as $warehouse)
                                    <tr>
                                        <td><strong>{{ $warehouse->warehouse_code }}</strong></td>
                                        <td>{{ $warehouse->warehouse_name }}</td>
                                        <td>
                                            @if($warehouse->plant)
                                                {{ $warehouse->plant->plant_name }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td><span class="badge bg-info">{{ $warehouse->warehouse_type }}</span></td>
                                        <td>{{ Str::limit($warehouse->address, 30) }}</td>
                                        <td>{{ number_format($warehouse->capacity ?? 0) }}</td>
                                        <td>
                                            @if($warehouse->is_active && $warehouse->status === 'active')
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('tenant.warehouses.show', $warehouse->_id) }}" class="btn btn-sm btn-info" title="View">
                                                    <i class="ri-eye-line"></i>
                                                </a>
                                                <a href="{{ route('tenant.warehouses.edit', $warehouse->_id) }}" class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="ri-pencil-line"></i>
                                                </a>
                                                <form action="{{ route('tenant.warehouses.toggle-status', $warehouse->_id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-secondary" title="Toggle Status">
                                                        <i class="ri-toggle-line"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('tenant.warehouses.destroy', $warehouse->_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this warehouse?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No warehouses found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-3">
                        {{ $warehouses->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

