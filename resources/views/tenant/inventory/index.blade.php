@extends('layout')

@section('title', 'Inventory Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Inventory Management</h4>
                        <a href="{{ route('tenant.inventory.create') }}" class="btn btn-primary">
                            <i class="ri-add-line me-1"></i> Manual Placement
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="Search by item code, barcode, QR, RFID..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
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
                                    <option value="reserved" {{ request('status') == 'reserved' ? 'selected' : '' }}>Reserved</option>
                                    <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Blocked</option>
                                    <option value="qc_hold" {{ request('status') == 'qc_hold' ? 'selected' : '' }}>QC Hold</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="item_id" class="form-select">
                                    <option value="">All Items</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->_id }}" {{ request('item_id') == $item->_id ? 'selected' : '' }}>
                                            {{ $item->item_code }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-outline-primary">Filter</button>
                                <a href="{{ route('tenant.inventory.index') }}" class="btn btn-outline-secondary">Reset</a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Item Code</th>
                                    <th>Item Name</th>
                                    <th>Location</th>
                                    <th>Batch</th>
                                    <th>Quantity</th>
                                    <th>Available</th>
                                    <th>Status</th>
                                    <th>Barcode/QR/RFID</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($inventory as $inv)
                                    <tr>
                                        <td><strong>{{ $inv->item_code }}</strong></td>
                                        <td>{{ $inv->item->description ?? 'N/A' }}</td>
                                        <td>
                                            @if($inv->warehouse)
                                                {{ $inv->warehouse->warehouse_name }}
                                                @if($inv->bin)
                                                    → {{ $inv->bin->bin_code }}
                                                @endif
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            @if($inv->batch)
                                                <span class="badge bg-info">{{ $inv->batch->batch_id }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ number_format($inv->quantity, 2) }} {{ $inv->item->uom ?? 'PCS' }}</td>
                                        <td>{{ number_format($inv->available_qty, 2) }}</td>
                                        <td>
                                            @if($inv->status == 'available')
                                                <span class="badge bg-success">Available</span>
                                            @elseif($inv->status == 'reserved')
                                                <span class="badge bg-warning">Reserved</span>
                                            @elseif($inv->status == 'blocked')
                                                <span class="badge bg-danger">Blocked</span>
                                            @elseif($inv->status == 'qc_hold')
                                                <span class="badge bg-secondary">QC Hold</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($inv->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($inv->barcode)
                                                <small class="d-block"><strong>BC:</strong> {{ $inv->barcode }}</small>
                                            @endif
                                            @if($inv->qr_code)
                                                <small class="d-block"><strong>QR:</strong> {{ substr($inv->qr_code, 0, 15) }}...</small>
                                            @endif
                                            @if($inv->rfid_tag)
                                                <small class="d-block"><strong>RFID:</strong> {{ substr($inv->rfid_tag, 0, 15) }}...</small>
                                            @endif
                                            @if(!$inv->barcode && !$inv->qr_code && !$inv->rfid_tag)
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('tenant.inventory.show', $inv->_id) }}" class="btn btn-sm btn-info" title="View Details">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                            @if($inv->status == 'available')
                                                <a href="{{ route('tenant.inventory.transfer', $inv->_id) }}" class="btn btn-sm btn-warning" title="Transfer">
                                                    <i class="ri-arrow-left-right-line"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No inventory records found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $inventory->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

