@extends('layout')

@section('title', 'Batch Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Batch: {{ $batch->batch_id }}</h4>
                        <a href="{{ route('tenant.batches.index') }}" class="btn btn-secondary">
                            <i class="ri-arrow-left-line me-1"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Batch Information</h5>
                            <table class="table table-bordered">
                                <tr><th width="40%">Batch ID</th><td><strong>{{ $batch->batch_id }}</strong></td></tr>
                                <tr><th>Item</th><td>{{ $batch->item->item_code ?? 'N/A' }} - {{ $batch->item->description ?? 'N/A' }}</td></tr>
                                <tr><th>Manufacturing Date</th><td>{{ $batch->manufacturing_date ? $batch->manufacturing_date->format('Y-m-d') : '-' }}</td></tr>
                                <tr><th>Expiry Date</th><td>
                                    {{ $batch->expiry_date ? $batch->expiry_date->format('Y-m-d') : '-' }}
                                    @if($batch->expiry_date)
                                        @php $days = $batch->getDaysUntilExpiry(); @endphp
                                        @if($days < 0)
                                            <span class="badge bg-danger ms-2">Expired</span>
                                        @elseif($days <= 30)
                                            <span class="badge bg-warning ms-2">Expiring Soon</span>
                                        @endif
                                    @endif
                                </td></tr>
                                <tr><th>Production Lot</th><td>{{ $batch->production_lot_number ?? '-' }}</td></tr>
                                <tr><th>Vendor Batch</th><td>{{ $batch->vendor_batch_number ?? '-' }}</td></tr>
                                <tr><th>Quantity Received</th><td>{{ number_format($batch->quantity_received, 2) }}</td></tr>
                                <tr><th>Remaining Quantity</th><td><strong>{{ number_format($batch->getRemainingQuantity(), 2) }}</strong></td></tr>
                                <tr><th>Status</th><td>
                                    @if($batch->status == 'active')
                                        <span class="badge bg-success">Active</span>
                                    @elseif($batch->status == 'expired')
                                        <span class="badge bg-danger">Expired</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($batch->status) }}</span>
                                    @endif
                                </td></tr>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5>Inventory Locations</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Location</th>
                                        <th>Quantity</th>
                                        <th>Available</th>
                                        <th>Blocked</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($inventory as $inv)
                                        <tr>
                                            <td>{{ $inv->getLocationPath() }}</td>
                                            <td>{{ number_format($inv->quantity, 2) }}</td>
                                            <td>{{ number_format($inv->available_qty, 2) }}</td>
                                            <td>{{ number_format($inv->blocked_qty, 2) }}</td>
                                            <td>
                                                @if($inv->status == 'available')
                                                    <span class="badge bg-success">Available</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst($inv->status) }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('tenant.inventory.show', $inv->_id) }}" class="btn btn-sm btn-info">
                                                    <i class="ri-eye-line"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No inventory records found for this batch</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

