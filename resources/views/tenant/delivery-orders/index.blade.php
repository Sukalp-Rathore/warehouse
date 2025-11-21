@extends('layout')

@section('title', 'Delivery Orders')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Delivery Orders</h4>
                        <a href="{{ route('tenant.delivery-orders.create') }}" class="btn btn-primary">
                            <i class="ri-add-line me-1"></i> Create Delivery Order
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
                                <input type="text" name="search" class="form-control" placeholder="Search DO/Customer..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="created" {{ request('status') == 'created' ? 'selected' : '' }}>Created</option>
                                    <option value="picked" {{ request('status') == 'picked' ? 'selected' : '' }}>Picked</option>
                                    <option value="packed" {{ request('status') == 'packed' ? 'selected' : '' }}>Packed</option>
                                    <option value="dispatched" {{ request('status') == 'dispatched' ? 'selected' : '' }}>Dispatched</option>
                                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="priority" class="form-select">
                                    <option value="">All Priorities</option>
                                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                                    <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="warehouse_id" class="form-select">
                                    <option value="">All Warehouses</option>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->_id }}" {{ request('warehouse_id') == $warehouse->_id ? 'selected' : '' }}>
                                            {{ $warehouse->warehouse_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-outline-primary">Filter</button>
                                <a href="{{ route('tenant.delivery-orders.index') }}" class="btn btn-outline-secondary">Reset</a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>DO Number</th>
                                    <th>Customer</th>
                                    <th>Warehouse</th>
                                    <th>Items</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Delivery Date</th>
                                    <th>Created Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deliveryOrders as $do)
                                    <tr>
                                        <td><strong>{{ $do->do_number }}</strong></td>
                                        <td>
                                            {{ $do->customer_name }}
                                            @if($do->customer_code)
                                                <br><small class="text-muted">{{ $do->customer_code }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $do->warehouse->warehouse_name ?? 'N/A' }}</td>
                                        <td>
                                            {{ count($do->items ?? []) }} item(s)
                                            @php
                                                $totalQty = collect($do->items ?? [])->sum('quantity');
                                            @endphp
                                            <br><small class="text-muted">Total: {{ number_format($totalQty, 2) }}</small>
                                        </td>
                                        <td>
                                            @if($do->priority == 'urgent')
                                                <span class="badge bg-danger">Urgent</span>
                                            @elseif($do->priority == 'high')
                                                <span class="badge bg-warning">High</span>
                                            @elseif($do->priority == 'normal')
                                                <span class="badge bg-primary">Normal</span>
                                            @else
                                                <span class="badge bg-secondary">Low</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($do->status == 'created')
                                                <span class="badge bg-warning">Created</span>
                                            @elseif($do->status == 'picked')
                                                <span class="badge bg-info">Picked</span>
                                            @elseif($do->status == 'packed')
                                                <span class="badge bg-primary">Packed</span>
                                            @elseif($do->status == 'dispatched')
                                                <span class="badge bg-success">Dispatched</span>
                                            @elseif($do->status == 'delivered')
                                                <span class="badge bg-success">Delivered</span>
                                            @elseif($do->status == 'cancelled')
                                                <span class="badge bg-danger">Cancelled</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $do->status }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($do->delivery_date)
                                                {{ $do->delivery_date->format('Y-m-d') }}
                                            @else
                                                <span class="text-muted">Not set</span>
                                            @endif
                                        </td>
                                        <td>{{ $do->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <a href="{{ route('tenant.delivery-orders.show', $do->_id) }}" class="btn btn-sm btn-info">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No delivery orders found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $deliveryOrders->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

