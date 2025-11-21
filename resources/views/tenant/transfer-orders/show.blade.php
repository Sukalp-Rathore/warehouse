@extends('layout')

@section('title', 'Transfer Order Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Transfer Order: {{ $transferOrder->to_number }}</h4>
                        <a href="{{ route('tenant.transfer-orders.index') }}" class="btn btn-secondary">
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
                            <h5>Transfer Details</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">TO Number</th>
                                    <td><strong>{{ $transferOrder->to_number }}</strong>
                                        @if($transferOrder->auto_generated)
                                            <span class="badge bg-info ms-2">Auto-Generated</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        @if($transferOrder->status == 'created')
                                            <span class="badge bg-warning">Created</span>
                                        @elseif($transferOrder->status == 'approved')
                                            <span class="badge bg-info">Approved</span>
                                        @elseif($transferOrder->status == 'in_progress')
                                            <span class="badge bg-primary">In Progress</span>
                                        @elseif($transferOrder->status == 'completed')
                                            <span class="badge bg-success">Completed</span>
                                        @elseif($transferOrder->status == 'cancelled')
                                            <span class="badge bg-danger">Cancelled</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Priority</th>
                                    <td>
                                        @if($transferOrder->priority == 'urgent')
                                            <span class="badge bg-danger">Urgent</span>
                                        @elseif($transferOrder->priority == 'high')
                                            <span class="badge bg-warning">High</span>
                                        @elseif($transferOrder->priority == 'normal')
                                            <span class="badge bg-primary">Normal</span>
                                        @else
                                            <span class="badge bg-secondary">Low</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Reason</th>
                                    <td>{{ ucfirst($transferOrder->reason) }}</td>
                                </tr>
                                <tr>
                                    <th>Item</th>
                                    <td>{{ $transferOrder->item_code }} - {{ $transferOrder->item->description ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Quantity</th>
                                    <td><strong>{{ number_format($transferOrder->quantity, 2) }}</strong> {{ $transferOrder->item->uom ?? 'PCS' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Bin Information</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">From Bin</th>
                                    <td>
                                        <strong>{{ $transferOrder->fromBin->bin_code ?? 'N/A' }}</strong><br>
                                        <small class="text-muted">{{ $transferOrder->fromBin->warehouse->warehouse_name ?? 'N/A' }}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>To Bin</th>
                                    <td>
                                        <strong>{{ $transferOrder->toBin->bin_code ?? 'N/A' }}</strong><br>
                                        <small class="text-muted">{{ $transferOrder->toBin->warehouse->warehouse_name ?? 'N/A' }}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Assigned To</th>
                                    <td>{{ $transferOrder->assignedTo->name ?? 'Unassigned' }}</td>
                                </tr>
                                <tr>
                                    <th>Approved By</th>
                                    <td>{{ $transferOrder->approvedBy->name ?? 'Not approved yet' }}</td>
                                </tr>
                                <tr>
                                    <th>Created By</th>
                                    <td>{{ $transferOrder->createdBy->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Created Date</th>
                                    <td>{{ $transferOrder->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                @if($transferOrder->approved_at)
                                <tr>
                                    <th>Approved Date</th>
                                    <td>{{ $transferOrder->approved_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                @endif
                                @if($transferOrder->completed_at)
                                <tr>
                                    <th>Completed Date</th>
                                    <td>{{ $transferOrder->completed_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    @if($transferOrder->notes)
                    <div class="mb-4">
                        <h5>Notes</h5>
                        <p class="text-muted">{{ $transferOrder->notes }}</p>
                    </div>
                    @endif

                    <div class="d-flex gap-2">
                        @if($transferOrder->status == 'created')
                            <form action="{{ route('tenant.transfer-orders.approve', $transferOrder->_id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="ri-check-line me-1"></i> Approve
                                </button>
                            </form>
                        @endif

                        @if(in_array($transferOrder->status, ['approved', 'in_progress']))
                            <form action="{{ route('tenant.transfer-orders.execute', $transferOrder->_id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-primary" onclick="return confirm('Execute this transfer order?')">
                                    <i class="ri-play-line me-1"></i> Execute
                                </button>
                            </form>
                        @endif

                        @if(!in_array($transferOrder->status, ['completed', 'cancelled']))
                            <a href="{{ route('tenant.transfer-orders.edit', $transferOrder->_id) }}" class="btn btn-warning">
                                <i class="ri-edit-line me-1"></i> Edit
                            </a>
                            <form action="{{ route('tenant.transfer-orders.cancel', $transferOrder->_id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Cancel this transfer order?')">
                                    <i class="ri-close-line me-1"></i> Cancel
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

