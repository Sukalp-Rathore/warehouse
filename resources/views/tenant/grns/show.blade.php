@extends('layout')

@section('title', 'GRN Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">GRN: {{ $grn->grn_number }}</h4>
                        <div>
                            @if($grn->status == 'pending')
                                <form action="{{ route('tenant.grns.approve', $grn->_id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success">
                                        <i class="ri-check-line me-1"></i> Approve GRN
                                    </button>
                                </form>
                            @endif
                            @if($grn->status == 'approved' && ($grn->putaway_status == 'pending' || $grn->putaway_status == 'in_progress'))
                                <a href="{{ route('tenant.grns.putaway', $grn->_id) }}" class="btn btn-primary">
                                    <i class="ri-inbox-line me-1"></i> Putaway
                                </a>
                            @endif
                            @if($grn->status == 'in_progress')
                                <a href="{{ route('tenant.grns.putaway', $grn->_id) }}" class="btn btn-primary">
                                    <i class="ri-inbox-line me-1"></i> Putaway
                                </a>
                            @endif
                            <a href="{{ route('tenant.grns.index') }}" class="btn btn-secondary">
                                <i class="ri-arrow-left-line me-1"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <h5>GRN Information</h5>
                            <table class="table table-bordered">
                                <tr><th width="40%">GRN Number</th><td><strong>{{ $grn->grn_number }}</strong></td></tr>
                                <tr><th>Status</th><td>
                                    @if($grn->status == 'pending')
                                        <span class="badge bg-warning">Pending Approval</span>
                                    @elseif($grn->status == 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @elseif($grn->status == 'in_progress')
                                        <span class="badge bg-primary">In Progress</span>
                                    @elseif($grn->status == 'completed')
                                        <span class="badge bg-success">Completed</span>
                                    @elseif($grn->status == 'cancelled')
                                        <span class="badge bg-danger">Cancelled</span>
                                    @endif
                                </td></tr>
                                @if($grn->sap_grn_id)
                                <tr><th>SAP GRN ID</th><td>{{ $grn->sap_grn_id }}</td></tr>
                                @endif
                                @if($grn->approved_at)
                                <tr><th>Approved Date</th><td>{{ \Carbon\Carbon::parse($grn->approved_at)->format('Y-m-d H:i') }}</td></tr>
                                @endif
                                <tr><th>Putaway Status</th><td>
                                    @if($grn->putaway_status == 'pending')
                                        <span class="badge bg-secondary">Pending</span>
                                    @elseif($grn->putaway_status == 'in_progress')
                                        <span class="badge bg-info">In Progress</span>
                                    @elseif($grn->putaway_status == 'completed')
                                        <span class="badge bg-success">Completed</span>
                                    @endif
                                </td></tr>
                                <tr><th>Vendor</th><td>{{ $grn->vendor->vendor_name ?? 'N/A' }}</td></tr>
                                <tr><th>Warehouse</th><td>{{ $grn->warehouse->warehouse_name ?? 'N/A' }}</td></tr>
                                <tr><th>PO Reference</th><td>{{ $grn->po_reference ?? '-' }}</td></tr>
                                <tr><th>Created Date</th><td>{{ $grn->created_at->format('Y-m-d H:i') }}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Putaway Progress</h5>
                            <div class="progress mb-3" style="height: 30px;">
                                <div class="progress-bar" role="progressbar" style="width: {{ $grn->getPutawayProgress() }}%">
                                    {{ number_format($grn->getPutawayProgress(), 1) }}%
                                </div>
                            </div>
                            <p class="text-muted">Progress: {{ number_format($grn->getPutawayProgress(), 1) }}% complete</p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5>Items</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Item Code</th>
                                        <th>Description</th>
                                        <th>Quantity</th>
                                        <th>UOM</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($grn->items as $index => $grnItem)
                                        @php
                                            $item = $items[$grnItem['item_id']] ?? null;
                                        @endphp
                                        <tr>
                                            <td>{{ $item->item_code ?? 'N/A' }}</td>
                                            <td>{{ $item->description ?? 'N/A' }}</td>
                                            <td>{{ number_format($grnItem['quantity'], 2) }}</td>
                                            <td>{{ $item->uom ?? 'PCS' }}</td>
                                            <td>
                                                @php
                                                    $putawayQty = \App\Models\Client\Inventory::where('item_id', $grnItem['item_id'])
                                                        ->where(function($q) use ($grn) {
                                                            $q->where('metadata.grn_id', $grn->_id)
                                                              ->orWhere('reference_doc', $grn->grn_number);
                                                        })->sum('quantity');
                                                @endphp
                                                @if($putawayQty >= $grnItem['quantity'])
                                                    <span class="badge bg-success">Completed</span>
                                                @elseif($putawayQty > 0)
                                                    <span class="badge bg-warning">Partial ({{ number_format($putawayQty, 2) }})</span>
                                                @else
                                                    <span class="badge bg-secondary">Pending</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
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

