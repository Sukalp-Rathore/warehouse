@extends('layout')

@section('title', 'Vendor Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Vendor Management</h4>
                        <a href="{{ route('tenant.vendors.create') }}" class="btn btn-primary">
                            <i class="ri-add-line me-1"></i> Add New Vendor
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="Search vendors..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
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
                                    <th>Vendor Code</th>
                                    <th>Vendor Name</th>
                                    <th>Contact Person</th>
                                    <th>Contact Email</th>
                                    <th>Type</th>
                                    <th>Pre-barcode</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vendors as $vendor)
                                    <tr>
                                        <td><strong>{{ $vendor->vendor_code }}</strong></td>
                                        <td>{{ $vendor->vendor_name }}</td>
                                        <td>{{ $vendor->contact_person ?? 'N/A' }}</td>
                                        <td>{{ $vendor->contact_email ?? 'N/A' }}</td>
                                        <td>{{ $vendor->vendor_type ?? 'N/A' }}</td>
                                        <td>
                                            @if($vendor->allow_prebarcode)
                                                <span class="badge bg-success">Yes</span>
                                            @else
                                                <span class="badge bg-secondary">No</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($vendor->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('tenant.vendors.show', $vendor->_id) }}" class="btn btn-sm btn-info">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                            <a href="{{ route('tenant.vendors.edit', $vendor->_id) }}" class="btn btn-sm btn-warning">
                                                <i class="ri-pencil-line"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center">No vendors found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $vendors->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

