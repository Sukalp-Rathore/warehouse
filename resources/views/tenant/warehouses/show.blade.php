@extends('layout')

@section('title', 'Warehouse Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Warehouse Details: {{ $warehouse->warehouse_name }}</h4>
                        <div>
                            <a href="{{ route('tenant.warehouses.edit', $warehouse->_id) }}" class="btn btn-warning">
                                <i class="ri-pencil-line me-1"></i> Edit
                            </a>
                            <a href="{{ route('tenant.warehouses.index') }}" class="btn btn-outline-secondary">
                                <i class="ri-arrow-left-line me-1"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Basic Information</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Warehouse Code</th>
                                    <td>{{ $warehouse->warehouse_code }}</td>
                                </tr>
                                <tr>
                                    <th>Warehouse Name</th>
                                    <td>{{ $warehouse->warehouse_name }}</td>
                                </tr>
                                <tr>
                                    <th>Plant</th>
                                    <td>{{ $warehouse->plant->plant_name ?? 'N/A' }} ({{ $warehouse->plant_code }})</td>
                                </tr>
                                <tr>
                                    <th>Warehouse Type</th>
                                    <td><span class="badge bg-info">{{ $warehouse->warehouse_type }}</span></td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        @if($warehouse->is_active && $warehouse->status === 'active')
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Address</th>
                                    <td>{{ $warehouse->address }}</td>
                                </tr>
                                <tr>
                                    <th>Capacity</th>
                                    <td>{{ number_format($warehouse->capacity ?? 0) }}</td>
                                </tr>
                                <tr>
                                    <th>Current Utilization</th>
                                    <td>{{ number_format($warehouse->current_utilization ?? 0) }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Contact Information</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Contact Person</th>
                                    <td>{{ $warehouse->contact_person ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Contact Phone</th>
                                    <td>{{ $warehouse->contact_phone ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Contact Email</th>
                                    <td>{{ $warehouse->contact_email ?? 'N/A' }}</td>
                                </tr>
                            </table>
                            
                            <h5 class="mt-4">Storage Types</h5>
                            <div>
                                @php
                                    $assignedTypes = $warehouse->storageTypes();
                                @endphp
                                @if($assignedTypes->count() > 0)
                                    @foreach($assignedTypes as $st)
                                        <span class="badge bg-primary me-1">{{ $st->name }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">No storage types assigned</span>
                                @endif
                            </div>
                            
                            <h5 class="mt-4">Statistics</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Total Bins</th>
                                    <td>{{ $warehouse->bins()->count() }}</td>
                                </tr>
                                <tr>
                                    <th>Total Sections</th>
                                    <td>{{ $warehouse->sections()->count() }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($warehouse->description)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h5>Description</h5>
                            <p>{{ $warehouse->description }}</p>
                        </div>
                    </div>
                    @endif
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Bins in this Warehouse</h5>
                            <a href="{{ route('tenant.bins.index', ['warehouse_id' => $warehouse->_id]) }}" class="btn btn-sm btn-primary">
                                View All Bins
                            </a>
                            <a href="{{ route('tenant.bins.create', ['warehouse_id' => $warehouse->_id]) }}" class="btn btn-sm btn-success">
                                <i class="ri-add-line me-1"></i> Generate Bins
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

