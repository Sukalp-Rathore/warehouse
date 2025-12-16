@extends('layout')

@section('title', 'Bin Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Bin Management</h4>
                        <a href="{{ route('tenant.bins.create') }}" class="btn btn-primary">
                            <i class="ri-add-line me-1"></i> Generate Bins
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <select name="warehouse_id" class="form-select">
                                    <option value="">All Warehouses</option>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->_id }}" {{ request('warehouse_id') == $wh->_id ? 'selected' : '' }}>
                                            {{ $wh->warehouse_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                                    <option value="occupied" {{ request('status') == 'occupied' ? 'selected' : '' }}>Occupied</option>
                                    <option value="reserved" {{ request('status') == 'reserved' ? 'selected' : '' }}>Reserved</option>
                                    <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="search" class="form-control" placeholder="Search bins..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Bin Code</th>
                                    <th>Warehouse</th>
                                    <th>Storage Type</th>
                                    <th>Zone</th>
                                    <th>Status</th>
                                    <th>Volume</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bins as $bin)
                                    <tr>
                                        <td><strong>{{ $bin->bin_code }}</strong></td>
                                        <td>{{ $bin->warehouse->warehouse_name ?? 'N/A' }}</td>
                                        <td>{{ $bin->storageType->name ?? 'N/A' }}</td>
                                        <td>{{ $bin->zone_code ?? 'N/A' }}</td>
                                        <td>
                                            @if($bin->status === 'available')
                                                <span class="badge bg-success">Available</span>
                                            @elseif($bin->status === 'occupied')
                                                <span class="badge bg-danger">Occupied</span>
                                            @elseif($bin->status === 'reserved')
                                                <span class="badge bg-warning">Reserved</span>
                                            @else
                                                <span class="badge bg-secondary">Maintenance</span>
                                            @endif
                                        </td>
                                        <td>{{ $bin->current_volume }}/{{ $bin->max_volume }} m³</td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick="editBin('{{ $bin->_id }}')">
                                                <i class="ri-pencil-line"></i>
                                            </button>
                                            <form action="{{ route('tenant.bins.destroy', $bin->_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this bin?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center">No bins found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $bins->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

