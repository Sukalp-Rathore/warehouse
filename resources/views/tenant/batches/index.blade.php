@extends('layout')

@section('title', 'Batch Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Batch Management</h4>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="Search batch ID..." value="{{ request('search') }}">
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
                            <div class="col-md-2">
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="expiring_soon" class="form-select">
                                    <option value="">All Batches</option>
                                    <option value="7" {{ request('expiring_soon') == '7' ? 'selected' : '' }}>Expiring in 7 days</option>
                                    <option value="30" {{ request('expiring_soon') == '30' ? 'selected' : '' }}>Expiring in 30 days</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-outline-primary">Filter</button>
                                <a href="{{ route('tenant.batches.index') }}" class="btn btn-outline-secondary">Reset</a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Batch ID</th>
                                    <th>Item</th>
                                    <th>Manufacturing Date</th>
                                    <th>Expiry Date</th>
                                    <th>Days Until Expiry</th>
                                    <th>Quantity Received</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($batches as $batch)
                                    <tr>
                                        <td><strong>{{ $batch->batch_id }}</strong></td>
                                        <td>{{ $batch->item->item_code ?? 'N/A' }}</td>
                                        <td>{{ $batch->manufacturing_date ? $batch->manufacturing_date->format('Y-m-d') : '-' }}</td>
                                        <td>
                                            {{ $batch->expiry_date ? $batch->expiry_date->format('Y-m-d') : '-' }}
                                            @if($batch->isExpired())
                                                <span class="badge bg-danger ms-2">Expired</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $days = $batch->getDaysUntilExpiry();
                                            @endphp
                                            @if($days !== null)
                                                @if($days < 0)
                                                    <span class="text-danger">Expired ({{ abs($days) }} days ago)</span>
                                                @elseif($days <= 30)
                                                    <span class="text-warning">{{ $days }} days</span>
                                                @else
                                                    {{ $days }} days
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ number_format($batch->quantity_received, 2) }}</td>
                                        <td>
                                            @if($batch->status == 'active')
                                                <span class="badge bg-success">Active</span>
                                            @elseif($batch->status == 'expired')
                                                <span class="badge bg-danger">Expired</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($batch->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('tenant.batches.show', $batch->_id) }}" class="btn btn-sm btn-info">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No batches found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $batches->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

