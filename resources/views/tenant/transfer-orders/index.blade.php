@extends('layout')

@section('title', 'Transfer Orders')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Transfer Orders</h4>
                        <div>
                            <button type="button" class="btn btn-info me-2" onclick="autoGenerateTOs()">
                                <i class="ri-refresh-line me-1"></i> Auto-Generate Replenishment
                            </button>
                            <a href="{{ route('tenant.transfer-orders.create') }}" class="btn btn-primary">
                                <i class="ri-add-line me-1"></i> Create Transfer Order
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

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="Search TO/Item..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="created" {{ request('status') == 'created' ? 'selected' : '' }}>Created</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
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
                                <select name="auto_generated" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="true" {{ request('auto_generated') == 'true' ? 'selected' : '' }}>Auto-Generated</option>
                                    <option value="false" {{ request('auto_generated') == 'false' ? 'selected' : '' }}>Manual</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-outline-primary">Filter</button>
                                <a href="{{ route('tenant.transfer-orders.index') }}" class="btn btn-outline-secondary">Reset</a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>TO Number</th>
                                    <th>Item</th>
                                    <th>From Bin</th>
                                    <th>To Bin</th>
                                    <th>Quantity</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Assigned To</th>
                                    <th>Created Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transferOrders as $to)
                                    <tr>
                                        <td>
                                            <strong>{{ $to->to_number }}</strong>
                                            @if($to->auto_generated)
                                                <span class="badge bg-info ms-1">Auto</span>
                                            @endif
                                        </td>
                                        <td>{{ $to->item_code ?? 'N/A' }}</td>
                                        <td>{{ $to->fromBin->bin_code ?? 'N/A' }}</td>
                                        <td>{{ $to->toBin->bin_code ?? 'N/A' }}</td>
                                        <td>{{ number_format($to->quantity, 2) }}</td>
                                        <td>
                                            @if($to->priority == 'urgent')
                                                <span class="badge bg-danger">Urgent</span>
                                            @elseif($to->priority == 'high')
                                                <span class="badge bg-warning">High</span>
                                            @elseif($to->priority == 'normal')
                                                <span class="badge bg-primary">Normal</span>
                                            @else
                                                <span class="badge bg-secondary">Low</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($to->status == 'created')
                                                <span class="badge bg-warning">Created</span>
                                            @elseif($to->status == 'approved')
                                                <span class="badge bg-info">Approved</span>
                                            @elseif($to->status == 'in_progress')
                                                <span class="badge bg-primary">In Progress</span>
                                            @elseif($to->status == 'completed')
                                                <span class="badge bg-success">Completed</span>
                                            @elseif($to->status == 'cancelled')
                                                <span class="badge bg-danger">Cancelled</span>
                                            @endif
                                        </td>
                                        <td>{{ $to->assignedTo->name ?? 'Unassigned' }}</td>
                                        <td>{{ $to->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <a href="{{ route('tenant.transfer-orders.show', $to->_id) }}" class="btn btn-sm btn-info">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">No transfer orders found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $transferOrders->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function autoGenerateTOs() {
    if (!confirm('Generate automatic replenishment transfer orders for bins below reorder level?')) {
        return;
    }

    fetch('{{ route("tenant.transfer-orders.auto-generate") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Successfully generated ${data.count} transfer orders.`);
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to generate transfer orders'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while generating transfer orders');
    });
}
</script>
@endsection

