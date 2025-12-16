@extends('layout')

@section('title', 'Create Transfer Order')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Create Transfer Order</h4>
                        <a href="{{ route('tenant.transfer-orders.index') }}" class="btn btn-secondary">
                            <i class="ri-arrow-left-line me-1"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
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

                    <form action="{{ route('tenant.transfer-orders.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="item_id" class="form-label">Item *</label>
                                    <select name="item_id" id="item_id" class="form-select @error('item_id') is-invalid @enderror" required>
                                        <option value="">Select Item</option>
                                        @foreach($items as $item)
                                            <option value="{{ $item->_id }}" {{ old('item_id') == $item->_id ? 'selected' : '' }}>
                                                {{ $item->item_code }} - {{ $item->description }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('item_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Quantity *</label>
                                    <input type="number" step="0.01" name="quantity" id="quantity" 
                                           class="form-control @error('quantity') is-invalid @enderror" 
                                           value="{{ old('quantity') }}" required>
                                    @error('quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="from_bin_id" class="form-label">From Bin *</label>
                                    <select name="from_bin_id" id="from_bin_id" class="form-select @error('from_bin_id') is-invalid @enderror" required disabled>
                                        <option value="">Select Item and Enter Quantity First</option>
                                    </select>
                                    <small class="form-text text-muted">Select item and enter quantity to see available bins with sufficient stock</small>
                                    @error('from_bin_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="to_bin_id" class="form-label">To Bin *</label>
                                    <select name="to_bin_id" id="to_bin_id" class="form-select @error('to_bin_id') is-invalid @enderror" required disabled>
                                        <option value="">Select Item and Enter Quantity First</option>
                                    </select>
                                    <small class="form-text text-muted">Select item and enter quantity to see available destination bins with sufficient capacity</small>
                                    @error('to_bin_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="priority" class="form-label">Priority</label>
                                    <select name="priority" id="priority" class="form-select">
                                        <option value="normal" {{ old('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                        <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="reason" class="form-label">Reason</label>
                                    <select name="reason" id="reason" class="form-select">
                                        <option value="manual" {{ old('reason') == 'manual' ? 'selected' : '' }}>Manual</option>
                                        <option value="replenishment" {{ old('reason') == 'replenishment' ? 'selected' : '' }}>Replenishment</option>
                                        <option value="consolidation" {{ old('reason') == 'consolidation' ? 'selected' : '' }}>Consolidation</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="assigned_to" class="form-label">Assign To (Optional)</label>
                                    <select name="assigned_to" id="assigned_to" class="form-select">
                                        <option value="">Unassigned</option>
                                        @foreach($operators as $operator)
                                            <option value="{{ $operator->_id }}" {{ old('assigned_to') == $operator->_id ? 'selected' : '' }}>
                                                {{ $operator->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes (Optional)</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('tenant.transfer-orders.index') }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Transfer Order</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Function to fetch bins for item with quantity filter
function fetchBinsForItem() {
    const itemId = document.getElementById('item_id').value;
    const quantity = parseFloat(document.getElementById('quantity').value) || 0;
    const fromBinSelect = document.getElementById('from_bin_id');
    
    // Clear and disable from_bin dropdown
    fromBinSelect.innerHTML = '<option value="">Loading bins...</option>';
    fromBinSelect.disabled = true;
    
    // Require both item and quantity to be provided
    if (!itemId) {
        fromBinSelect.innerHTML = '<option value="">Select Item First</option>';
        return;
    }
    
    if (quantity <= 0) {
        fromBinSelect.innerHTML = '<option value="">Enter Quantity First</option>';
        return;
    }
    
    // Build query string with quantity (required)
    let queryString = `item_id=${encodeURIComponent(itemId)}&quantity=${encodeURIComponent(quantity)}`;
    
    // Fetch bins for this item with quantity filter
    fetch(`{{ route('tenant.transfer-orders.get-bins-for-item') }}?${queryString}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.bins.length > 0) {
            fromBinSelect.innerHTML = '<option value="">Select Source Bin</option>';
            
            data.bins.forEach(bin => {
                const option = document.createElement('option');
                option.value = bin._id;
                
                let label = `${bin.bin_code} - ${bin.warehouse_name}`;
                label += ` (Available: ${bin.available_qty})`;
                
                option.textContent = label;
                fromBinSelect.appendChild(option);
            });
            
            fromBinSelect.disabled = false;
            
            // Restore old value if exists
            const oldValue = '{{ old("from_bin_id") }}';
            if (oldValue) {
                fromBinSelect.value = oldValue;
            }
        } else {
            const message = `No bins have sufficient available quantity (${quantity} required)`;
            fromBinSelect.innerHTML = `<option value="">${message}</option>`;
        }
    })
    .catch(error => {
        console.error('Error fetching bins:', error);
        fromBinSelect.innerHTML = '<option value="">Error loading bins</option>';
    });
}

// When item changes, check if we should fetch bins
document.getElementById('item_id').addEventListener('change', function() {
    const quantity = parseFloat(document.getElementById('quantity').value) || 0;
    const fromBinSelect = document.getElementById('from_bin_id');
    const toBinSelect = document.getElementById('to_bin_id');
    
    if (!this.value) {
        fromBinSelect.innerHTML = '<option value="">Select Item and Enter Quantity First</option>';
        fromBinSelect.disabled = true;
        toBinSelect.innerHTML = '<option value="">Select Item and Enter Quantity First</option>';
        toBinSelect.disabled = true;
        return;
    }
    
    // Only fetch if quantity is also provided
    if (quantity > 0) {
        fetchBinsForItem();
        fetchDestinationBins();
    } else {
        fromBinSelect.innerHTML = '<option value="">Enter Quantity to See Available Bins</option>';
        fromBinSelect.disabled = true;
        toBinSelect.innerHTML = '<option value="">Enter Quantity to See Available Bins</option>';
        toBinSelect.disabled = true;
    }
});

// Function to fetch destination bins with capacity filter
function fetchDestinationBins() {
    const itemId = document.getElementById('item_id').value;
    const quantity = parseFloat(document.getElementById('quantity').value) || 0;
    const fromBinId = document.getElementById('from_bin_id').value;
    const toBinSelect = document.getElementById('to_bin_id');
    
    // Clear and disable to_bin dropdown
    toBinSelect.innerHTML = '<option value="">Loading bins...</option>';
    toBinSelect.disabled = true;
    
    // Require both item and quantity to be provided
    if (!itemId) {
        toBinSelect.innerHTML = '<option value="">Select Item First</option>';
        return;
    }
    
    if (quantity <= 0) {
        toBinSelect.innerHTML = '<option value="">Enter Quantity First</option>';
        return;
    }
    
    // Build query string
    let queryString = `item_id=${encodeURIComponent(itemId)}&quantity=${encodeURIComponent(quantity)}`;
    if (fromBinId) {
        queryString += `&from_bin_id=${encodeURIComponent(fromBinId)}`;
    }
    
    // Fetch destination bins with capacity filter
    fetch(`{{ route('tenant.transfer-orders.get-destination-bins') }}?${queryString}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.bins.length > 0) {
            toBinSelect.innerHTML = '<option value="">Select Destination Bin</option>';
            
            data.bins.forEach(bin => {
                const option = document.createElement('option');
                option.value = bin._id;
                
                let label = `${bin.bin_code} - ${bin.warehouse_name}`;
                label += ` (Avail Vol: ${bin.available_volume}, Avail Wt: ${bin.available_weight})`;
                
                option.textContent = label;
                toBinSelect.appendChild(option);
            });
            
            toBinSelect.disabled = false;
            
            // Restore old value if exists
            const oldValue = '{{ old("to_bin_id") }}';
            if (oldValue) {
                toBinSelect.value = oldValue;
            }
        } else {
            const message = `No bins have sufficient capacity (Vol: ${data.required_volume || 0}, Wt: ${data.required_weight || 0} required)`;
            toBinSelect.innerHTML = `<option value="">${message}</option>`;
        }
    })
    .catch(error => {
        console.error('Error fetching destination bins:', error);
        toBinSelect.innerHTML = '<option value="">Error loading bins</option>';
    });
}

// Refetch bins when quantity changes (with debouncing)
let quantityTimeout;
let destinationBinTimeout;
document.getElementById('quantity').addEventListener('input', function() {
    const itemId = document.getElementById('item_id').value;
    const quantity = parseFloat(this.value) || 0;
    const fromBinSelect = document.getElementById('from_bin_id');
    const toBinSelect = document.getElementById('to_bin_id');
    
    if (!itemId) {
        fromBinSelect.innerHTML = '<option value="">Select Item First</option>';
        fromBinSelect.disabled = true;
        toBinSelect.innerHTML = '<option value="">Select Item First</option>';
        toBinSelect.disabled = true;
        return;
    }
    
    if (quantity <= 0) {
        fromBinSelect.innerHTML = '<option value="">Enter Quantity to See Available Bins</option>';
        fromBinSelect.disabled = true;
        toBinSelect.innerHTML = '<option value="">Enter Quantity to See Available Bins</option>';
        toBinSelect.disabled = true;
        return;
    }
    
    // Clear previous timeouts
    clearTimeout(quantityTimeout);
    clearTimeout(destinationBinTimeout);
    
    // Debounce: wait 500ms after user stops typing
    quantityTimeout = setTimeout(function() {
        fetchBinsForItem();
    }, 500);
    
    destinationBinTimeout = setTimeout(function() {
        fetchDestinationBins();
    }, 500);
});

// When from_bin changes, refetch destination bins (to exclude source bin)
document.getElementById('from_bin_id').addEventListener('change', function() {
    const itemId = document.getElementById('item_id').value;
    const quantity = parseFloat(document.getElementById('quantity').value) || 0;
    
    // Refetch destination bins to exclude the selected source bin
    if (itemId && quantity > 0) {
        fetchDestinationBins();
    }
});

// When to_bin changes, disable it in from_bin dropdown (if static list exists)
document.getElementById('to_bin_id').addEventListener('change', function() {
    const toBinId = this.value;
    const fromBinSelect = document.getElementById('from_bin_id');
    
    // Disable the selected to bin in the from bin dropdown
    Array.from(fromBinSelect.options).forEach(option => {
        if (option.value === toBinId) {
            option.disabled = true;
        } else {
            option.disabled = false;
        }
    });
});

// Trigger item change on page load if item is already selected
document.addEventListener('DOMContentLoaded', function() {
    const itemSelect = document.getElementById('item_id');
    if (itemSelect.value) {
        itemSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endsection

