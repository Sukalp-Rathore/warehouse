@extends('layout')

@section('title', 'GRN Putaway')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Putaway: {{ $grn->grn_number }}</h4>
                        <a href="{{ route('tenant.grns.show', $grn->_id) }}" class="btn btn-secondary">
                            <i class="ri-arrow-left-line me-1"></i> Back to GRN
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

                    <div class="alert alert-info">
                        <strong>Warehouse:</strong> {{ $grn->warehouse->warehouse_name }}<br>
                        <strong>Progress:</strong> {{ number_format($grn->getPutawayProgress(), 1) }}% complete
                    </div>

                    @foreach($grn->items as $index => $grnItem)
                        @php
                            $item = $items[$grnItem['item_id']] ?? null;
                            if (!$item) continue;
                            $putawayQty = \App\Models\Client\Inventory::where('item_id', $grnItem['item_id'])
                                ->where(function($q) use ($grn) {
                                    $q->where('metadata.grn_id', $grn->_id)->orWhere('reference_doc', $grn->grn_number);
                                })->sum('quantity');
                            $remainingQty = $grnItem['quantity'] - $putawayQty;
                        @endphp
                        @if($remainingQty > 0)
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="mb-0">{{ $item->item_code }} - {{ $item->description }}</h5>
                                <small>Remaining: {{ number_format($remainingQty, 2) }} / {{ number_format($grnItem['quantity'], 2) }} {{ $item->uom ?? 'PCS' }}</small>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('tenant.grns.putaway.store', $grn->_id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="item_index" value="{{ $index }}">
                                    
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label">Quantity *</label>
                                            <input type="number" name="quantity" class="form-control" step="0.01" min="0.01" max="{{ $remainingQty }}" required>
                                        </div>
                                        <div class="col-md-9">
                                            <label class="form-label">Bin *</label>
                                            <select name="bin_id" class="form-select bin-select" required>
                                                <option value="">Select Bin</option>
                                            </select>
                                            <small class="text-muted">Select a bin or use suggestions below</small>
                                        </div>
                                    </div>

                                    <div class="mt-3" id="bin-suggestions-{{ $index }}">
                                        <button type="button" class="btn btn-sm btn-outline-primary get-suggestions" data-item-id="{{ $grnItem['item_id'] }}" data-quantity="{{ $remainingQty }}" data-warehouse-id="{{ $grn->warehouse_id }}">
                                            <i class="ri-lightbulb-line me-1"></i> Get Bin Suggestions
                                        </button>
                                        <div class="suggestions-container mt-2" style="display: none;"></div>
                                    </div>

                                    <div class="mt-3">
                                        <label class="form-label">Batch</label>
                                        <select name="batch_id" class="form-select">
                                            <option value="">No Batch</option>
                                            <option value="new">Create New Batch</option>
                                        </select>
                                        <div id="new-batch-section-{{ $index }}" style="display: none;" class="mt-2">
                                            <input type="text" name="batch_id_new" class="form-control mb-2" placeholder="Batch ID (e.g., BATCH-2025-001)">
                                            <input type="date" name="manufacturing_date" class="form-control mb-2" placeholder="Manufacturing Date">
                                            <input type="date" name="expiry_date" class="form-control" placeholder="Expiry Date">
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ri-inbox-line me-1"></i> Putaway Item
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endif
                    @endforeach

                    @if($grn->isFullyPutaway())
                        <div class="alert alert-success">
                            <h5>All items have been putaway!</h5>
                            <form action="{{ route('tenant.grns.complete', $grn->_id) }}" method="POST" class="mt-2">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="ri-check-line me-1"></i> Mark GRN as Completed
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load bins for warehouse
    const warehouseId = '{{ $grn->warehouse_id }}';
    
    // Load all bins for this warehouse
    fetch(`{{ route('tenant.bins.list') }}?warehouse_id=${warehouseId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.bins.length > 0) {
                // Populate all bin selects with available bins
                document.querySelectorAll('.bin-select').forEach(select => {
                    // Clear existing options except the first one
                    while (select.options.length > 1) {
                        select.remove(1);
                    }
                    
                    // Add bins to dropdown
                    data.bins.forEach(bin => {
                        const option = document.createElement('option');
                        option.value = bin.id;
                        option.textContent = `${bin.code} - ${bin.name} (${bin.storage_type}${bin.section !== 'N/A' ? ' - ' + bin.section : ''}) [${bin.status}]`;
                        select.appendChild(option);
                    });
                });
            } else {
                // Show message if no bins found
                document.querySelectorAll('.bin-select').forEach(select => {
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = 'No bins available for this warehouse';
                    option.disabled = true;
                    select.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error loading bins:', error);
        });

    // Batch selection handler
    document.querySelectorAll('select[name="batch_id"]').forEach(select => {
        select.addEventListener('change', function() {
            const index = this.closest('.card').querySelector('input[name="item_index"]').value;
            const newBatchSection = document.getElementById(`new-batch-section-${index}`);
            if (this.value === 'new') {
                newBatchSection.style.display = 'block';
            } else {
                newBatchSection.style.display = 'none';
            }
        });
    });

    // Get bin suggestions
    document.querySelectorAll('.get-suggestions').forEach(btn => {
        btn.addEventListener('click', function() {
            const itemId = this.dataset.itemId;
            const quantity = this.dataset.quantity;
            const warehouseId = this.dataset.warehouseId;
            const container = this.nextElementSibling;
            
            fetch(`{{ route('tenant.putaway.suggest') }}?item_id=${itemId}&quantity=${quantity}&warehouse_id=${warehouseId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.suggestions.length > 0) {
                        let html = '<div class="list-group">';
                        data.suggestions.forEach(suggestion => {
                            html += `<a href="#" class="list-group-item list-group-item-action suggestion-item" data-bin-id="${suggestion.bin_id}">
                                <strong>${suggestion.bin_code}</strong> - ${suggestion.storage_type || ''} ${suggestion.section ? '- ' + suggestion.section : ''}<br>
                                <small>Available: ${suggestion.available_volume} vol, ${suggestion.available_weight} wt | Score: ${suggestion.score}</small>
                            </a>`;
                        });
                        html += '</div>';
                        container.innerHTML = html;
                        container.style.display = 'block';
                        
                        // Click handler for suggestions
                        container.querySelectorAll('.suggestion-item').forEach(item => {
                            item.addEventListener('click', function(e) {
                                e.preventDefault();
                                const binId = this.dataset.binId;
                                const card = this.closest('.card');
                                card.querySelector('select[name="bin_id"]').value = binId;
                            });
                        });
                    } else {
                        container.innerHTML = '<div class="alert alert-warning">No suitable bins found</div>';
                        container.style.display = 'block';
                    }
                });
        });
    });
});
</script>
@endsection

