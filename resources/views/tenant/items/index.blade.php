@extends('layout')

@section('title', 'Item Master')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Item Master</h4>
                        <div>
                            <a href="{{ route('tenant.items.import') }}" class="btn btn-success me-2">
                                <i class="ri-file-upload-line me-1"></i> Import Items
                            </a>
                            <a href="{{ route('tenant.items.export') }}" class="btn btn-info me-2">
                                <i class="ri-file-download-line me-1"></i> Export Items
                            </a>
                            <a href="{{ route('tenant.items.create') }}" class="btn btn-primary">
                                <i class="ri-add-line me-1"></i> Add New Item
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="Search items..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <select name="category" class="form-select">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->category_code }}" {{ request('category') == $cat->category_code ? 'selected' : '' }}>
                                            {{ $cat->category_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="vendor_id" class="form-select">
                                    <option value="">All Vendors</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->_id }}" {{ request('vendor_id') == $vendor->_id ? 'selected' : '' }}>
                                            {{ $vendor->vendor_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="is_active" class="form-select">
                                    <option value="">All Items</option>
                                    <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-outline-primary">Filter</button>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Item Code</th>
                                    <th>Description</th>
                                    <th>Category</th>
                                    <th>UOM</th>
                                    <th>Vendor</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $item)
                                    <tr>
                                        <td><strong>{{ $item->item_code }}</strong></td>
                                        <td>{{ $item->description }}</td>
                                        <td>{{ $item->category ?? 'N/A' }}</td>
                                        <td>{{ $item->uom }}</td>
                                        <td>{{ $item->vendor->vendor_name ?? 'N/A' }}</td>
                                        <td>{{ $item->currency ?? 'USD' }} {{ number_format($item->unit_price ?? 0, 2) }}</td>
                                        <td>
                                            @if($item->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('tenant.items.show', $item->_id) }}" class="btn btn-sm btn-info">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                            <a href="{{ route('tenant.items.edit', $item->_id) }}" class="btn btn-sm btn-warning">
                                                <i class="ri-pencil-line"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center">No items found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $items->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

