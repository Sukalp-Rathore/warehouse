@extends('layout')

@section('title', 'GRN Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">GRN Management</h4>
                        <div>
                            <form action="{{ route('tenant.grns.sync-from-sap') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-refresh-line me-1"></i> Sync from SAP
                                </button>
                            </form>
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

                    @if(session('sync_errors') && count(session('sync_errors')) > 0)
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <strong>Sync completed with some errors:</strong>
                            <ul class="mb-0">
                                @foreach(session('sync_errors') as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="Search GRN/PO..." value="{{ request('search') }}">
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
                                <select name="vendor_id" class="form-select">
                                    <option value="">All Vendors</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->_id }}" {{ request('vendor_id') == $vendor->_id ? 'selected' : '' }}>
                                            {{ $vendor->vendor_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-outline-primary">Filter</button>
                                <a href="{{ route('tenant.grns.index') }}" class="btn btn-outline-secondary">Reset</a>
                            </div>
                        </div>
                    </form>

                    <!-- Pending Approval GRNs -->
                    <div class="mb-4">
                        <h5 class="mb-3">
                            <i class="ri-time-line me-2"></i>Pending Approval GRNs
                            <span class="badge bg-warning">{{ $pendingGRNs->total() }}</span>
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>GRN Number</th>
                                        <th>Vendor</th>
                                        <th>Warehouse</th>
                                        <th>PO Reference</th>
                                        <th>Items</th>
                                        <th>Created Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pendingGRNs as $grn)
                                        <tr>
                                            <td><strong>{{ $grn->grn_number }}</strong></td>
                                            <td>{{ $grn->vendor->vendor_name ?? 'N/A' }}</td>
                                            <td>{{ $grn->warehouse->warehouse_name ?? 'N/A' }}</td>
                                            <td>{{ $grn->po_reference ?? '-' }}</td>
                                            <td>
                                                <span class="badge bg-info">{{ count($grn->items ?? []) }} items</span>
                                            </td>
                                            <td>{{ $grn->created_at->format('Y-m-d H:i') }}</td>
                                            <td>
                                                <a href="{{ route('tenant.grns.show', $grn->_id) }}" class="btn btn-sm btn-info">
                                                    <i class="ri-eye-line"></i> View
                                                </a>
                                                <form action="{{ route('tenant.grns.approve', $grn->_id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        <i class="ri-check-line"></i> Approve
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">No pending approval GRNs found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2">
                            {{ $pendingGRNs->links() }}
                        </div>
                    </div>

                    <!-- Approved GRNs -->
                    <div class="mb-4">
                        <h5 class="mb-3">
                            <i class="ri-checkbox-circle-line me-2"></i>Approved GRNs
                            <span class="badge bg-success">{{ $approvedGRNs->total() }}</span>
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>GRN Number</th>
                                        <th>Vendor</th>
                                        <th>Warehouse</th>
                                        <th>PO Reference</th>
                                        <th>Items</th>
                                        <th>Putaway Status</th>
                                        <th>Approved Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($approvedGRNs as $grn)
                                        <tr>
                                            <td><strong>{{ $grn->grn_number }}</strong></td>
                                            <td>{{ $grn->vendor->vendor_name ?? 'N/A' }}</td>
                                            <td>{{ $grn->warehouse->warehouse_name ?? 'N/A' }}</td>
                                            <td>{{ $grn->po_reference ?? '-' }}</td>
                                            <td>
                                                <span class="badge bg-info">{{ count($grn->items ?? []) }} items</span>
                                            </td>
                                            <td>
                                                @if($grn->putaway_status == 'pending')
                                                    <span class="badge bg-secondary">Pending</span>
                                                @elseif($grn->putaway_status == 'in_progress')
                                                    <span class="badge bg-info">In Progress</span>
                                                @elseif($grn->putaway_status == 'completed')
                                                    <span class="badge bg-success">Completed</span>
                                                @endif
                                            </td>
                                            <td>{{ $grn->approved_at ? \Carbon\Carbon::parse($grn->approved_at)->format('Y-m-d H:i') : '-' }}</td>
                                            <td>
                                                <a href="{{ route('tenant.grns.show', $grn->_id) }}" class="btn btn-sm btn-info">
                                                    <i class="ri-eye-line"></i> View
                                                </a>
                                                @if($grn->putaway_status == 'pending' || $grn->putaway_status == 'in_progress')
                                                    <a href="{{ route('tenant.grns.putaway', $grn->_id) }}" class="btn btn-sm btn-primary">
                                                        <i class="ri-inbox-line"></i> Putaway
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">No approved GRNs found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2">
                            {{ $approvedGRNs->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
