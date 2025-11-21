@extends('layout')

@section('title', 'Item Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Item Details</h4>
                        <div>
                            <a href="{{ route('tenant.items.edit', $item->_id) }}" class="btn btn-warning me-2">
                                <i class="ri-pencil-line me-1"></i> Edit
                            </a>
                            <a href="{{ route('tenant.items.index') }}" class="btn btn-secondary">
                                <i class="ri-arrow-left-line me-1"></i> Back to Items
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="mb-3">Basic Information</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="200">Item Code</th>
                                    <td><strong>{{ $item->item_code }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Description</th>
                                    <td>{{ $item->description }}</td>
                                </tr>
                                @if($item->short_description)
                                <tr>
                                    <th>Short Description</th>
                                    <td>{{ $item->short_description }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Category</th>
                                    <td>{{ $item->category ?? 'N/A' }}</td>
                                </tr>
                                @if($item->subcategory)
                                <tr>
                                    <th>Subcategory</th>
                                    <td>{{ $item->subcategory }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Unit of Measure</th>
                                    <td>{{ $item->uom }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        @if($item->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            <h5 class="mb-3 mt-4">Physical Properties</h5>
                            <table class="table table-bordered">
                                @if($item->weight)
                                <tr>
                                    <th width="200">Weight</th>
                                    <td>{{ number_format($item->weight, 2) }} kg</td>
                                </tr>
                                @endif
                                @if($item->dimensions)
                                <tr>
                                    <th>Dimensions</th>
                                    <td>
                                        {{ $item->dimensions['length'] ?? 0 }} × 
                                        {{ $item->dimensions['width'] ?? 0 }} × 
                                        {{ $item->dimensions['height'] ?? 0 }} 
                                        {{ $item->dimensions['unit'] ?? 'cm' }}
                                    </td>
                                </tr>
                                @endif
                                @if($item->volume)
                                <tr>
                                    <th>Volume</th>
                                    <td>{{ number_format($item->volume, 2) }} m³</td>
                                </tr>
                                @endif
                            </table>

                            <h5 class="mb-3 mt-4">Storage Information</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="200">Preferred Storage Type</th>
                                    <td>{{ $item->preferredStorageType->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Preferred Bin</th>
                                    <td>{{ $item->preferredBin->bin_code ?? 'N/A' }}</td>
                                </tr>
                                @if($item->temperature_controlled)
                                <tr>
                                    <th>Temperature Controlled</th>
                                    <td><span class="badge bg-info">Yes</span></td>
                                </tr>
                                @endif
                                @if($item->is_hazmat)
                                <tr>
                                    <th>Hazmat</th>
                                    <td>
                                        <span class="badge bg-danger">Yes</span>
                                        @if($item->hazmat_class)
                                            <span class="badge bg-warning ms-2">{{ $item->hazmat_class }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endif
                            </table>

                            <h5 class="mb-3 mt-4">Pricing & Inventory</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="200">Unit Price</th>
                                    <td>{{ $item->currency ?? 'USD' }} {{ number_format($item->unit_price ?? 0, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Total Available Quantity</th>
                                    <td><strong>{{ number_format($item->getTotalAvailableQuantity(), 2) }} {{ $item->uom }}</strong></td>
                                </tr>
                                @if($item->reorder_level)
                                <tr>
                                    <th>Reorder Level</th>
                                    <td>{{ number_format($item->reorder_level, 2) }}</td>
                                </tr>
                                @endif
                                @if($item->reorder_quantity)
                                <tr>
                                    <th>Reorder Quantity</th>
                                    <td>{{ number_format($item->reorder_quantity, 2) }}</td>
                                </tr>
                                @endif
                            </table>

                            <h5 class="mb-3 mt-4">Vendor Information</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="200">Primary Vendor</th>
                                    <td>{{ $item->vendor->vendor_name ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-4">
                            @if($item->image_url)
                            <div class="mb-3">
                                <img src="{{ $item->image_url }}" alt="{{ $item->item_code }}" class="img-fluid rounded">
                            </div>
                            @endif
                            @if($item->barcode)
                            <div class="mb-3">
                                <label class="form-label">Barcode</label>
                                <p class="form-control-plaintext">{{ $item->barcode }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

