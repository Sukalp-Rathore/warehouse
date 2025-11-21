@extends('layout')

@section('title', 'Delivery Order Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Delivery Order: {{ $do->do_number }}</h4>
                        <a href="{{ route('tenant.delivery-orders.index') }}" class="btn btn-secondary">
                            <i class="ri-arrow-left-line me-1"></i> Back
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

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Order Details</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">DO Number</th>
                                    <td><strong>{{ $do->do_number }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Status</th>
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
                                </tr>
                                <tr>
                                    <th>Priority</th>
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
                                </tr>
                                <tr>
                                    <th>Warehouse</th>
                                    <td>{{ $do->warehouse->warehouse_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Delivery Date</th>
                                    <td>
                                        @if($do->delivery_date)
                                            {{ $do->delivery_date->format('Y-m-d') }}
                                        @else
                                            <span class="text-muted">Not set</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Picking Strategy</th>
                                    <td>{{ strtoupper($do->picking_strategy ?? 'fifo') }}</td>
                                </tr>
                                @if($do->sap_reference)
                                <tr>
                                    <th>SAP Reference</th>
                                    <td>{{ $do->sap_reference }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Customer Information</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Customer Name</th>
                                    <td>{{ $do->customer_name }}</td>
                                </tr>
                                @if($do->customer_code)
                                <tr>
                                    <th>Customer Code</th>
                                    <td>{{ $do->customer_code }}</td>
                                </tr>
                                @endif
                                @if($do->customer_address)
                                <tr>
                                    <th>Address</th>
                                    <td>
                                        @if(is_array($do->customer_address))
                                            {{ implode(', ', array_filter($do->customer_address)) }}
                                        @else
                                            {{ $do->customer_address }}
                                        @endif
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Created By</th>
                                    <td>{{ $do->createdBy->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Created Date</th>
                                    <td>{{ $do->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <h5 class="mb-3">Items</h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Item Code</th>
                                    <th>Description</th>
                                    <th>Quantity</th>
                                    <th>UOM</th>
                                    <th>Picked Qty</th>
                                    <th>Packed Qty</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($do->items ?? [] as $item)
                                    @php
                                        $itemModel = $items[$item['item_id']] ?? null;
                                        $pickedQty = $item['picked_qty'] ?? 0;
                                        $packedQty = $item['packed_qty'] ?? 0;
                                        $requiredQty = $item['quantity'] ?? 0;
                                    @endphp
                                    <tr>
                                        <td>{{ $item['item_code'] ?? 'N/A' }}</td>
                                        <td>{{ $itemModel->description ?? 'N/A' }}</td>
                                        <td><strong>{{ number_format($requiredQty, 2) }}</strong></td>
                                        <td>{{ $item['uom'] ?? 'PCS' }}</td>
                                        <td>
                                            {{ number_format($pickedQty, 2) }}
                                            @if($pickedQty < $requiredQty)
                                                <span class="badge bg-warning ms-1">Incomplete</span>
                                            @else
                                                <span class="badge bg-success ms-1">Complete</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ number_format($packedQty, 2) }}
                                            @if($packedQty < $requiredQty)
                                                <span class="badge bg-warning ms-1">Incomplete</span>
                                            @else
                                                <span class="badge bg-success ms-1">Complete</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($packedQty >= $requiredQty)
                                                <span class="badge bg-success">Packed</span>
                                            @elseif($pickedQty >= $requiredQty)
                                                <span class="badge bg-info">Picked</span>
                                            @else
                                                <span class="badge bg-warning">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No items found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($do->pickingTasks && $do->pickingTasks->count() > 0)
                    <h5 class="mb-3 mt-4">Picking Tasks</h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Task Number</th>
                                    <th>Item</th>
                                    <th>Bin</th>
                                    <th>Quantity</th>
                                    <th>Status</th>
                                    <th>Assigned To</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($do->pickingTasks as $task)
                                    <tr>
                                        <td>{{ $task->task_number ?? 'N/A' }}</td>
                                        <td>{{ $task->item_code ?? 'N/A' }}</td>
                                        <td>{{ $task->bin_code ?? 'N/A' }}</td>
                                        <td>{{ number_format($task->quantity ?? 0, 2) }}</td>
                                        <td>
                                            @if($task->status == 'completed')
                                                <span class="badge bg-success">Completed</span>
                                            @elseif($task->status == 'in_progress')
                                                <span class="badge bg-primary">In Progress</span>
                                            @else
                                                <span class="badge bg-warning">Pending</span>
                                            @endif
                                        </td>
                                        <td>{{ $task->assigned_to_name ?? 'Unassigned' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    <div class="mt-4">
                        @if($do->status == 'created')
                            <form action="{{ route('tenant.delivery-orders.generate-picking', $do->_id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-primary" onclick="return confirm('Generate picking tasks for this delivery order?')">
                                    <i class="ri-task-line me-1"></i> Generate Picking Tasks
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

