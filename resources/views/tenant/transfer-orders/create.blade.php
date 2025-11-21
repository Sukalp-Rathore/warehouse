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
                                    <select name="from_bin_id" id="from_bin_id" class="form-select @error('from_bin_id') is-invalid @enderror" required>
                                        <option value="">Select Item First</option>
                                    </select>
                                    <small class="form-text text-muted">Only bins containing the selected item are listed</small>
                                    @error('from_bin_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="to_bin_id" class="form-label">To Bin *</label>
                                    <select name="to_bin_id" id="to_bin_id" class="form-select @error('to_bin_id') is-invalid @enderror" required>
                                        <option value="">Select Destination Bin</option>
                                        @foreach($bins as $bin)
                                            <option value="{{ $bin->_id }}" {{ old('to_bin_id') == $bin->_id ? 'selected' : '' }}>
                                                {{ $bin->bin_code }} - {{ $bin->warehouse->warehouse_name ?? 'N/A' }}
                                            </option>
                                        @endforeach
                                    </select>
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
// Filter from_bin dropdown based on selected item
document.getElementById('item_id').addEventListener('change', function() {
    const itemId = this.value;
    const fromBinSelect = document.getElementById('from_bin_id');
    const toBinSelect = document.getElementById('to_bin_id');
    
    // Clear and disable from_bin dropdown
    fromBinSelect.innerHTML = '<option value="">Loading bins...</option>';
    fromBinSelect.disabled = true;
    
    if (!itemId) {
        fromBinSelect.innerHTML = '<option value="">Select Item First</option>';
        return;
    }
    
    // Fetch bins for this item
    fetch(`{{ route('tenant.transfer-orders.get-bins-for-item') }}?item_id=${itemId}`, {
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
            fromBinSelect.innerHTML = '<option value="">No bins contain this item</option>';
        }
    })
    .catch(error => {
        console.error('Error fetching bins:', error);
        fromBinSelect.innerHTML = '<option value="">Error loading bins</option>';
    });
});

// Disable selected bins in opposite dropdowns
document.getElementById('from_bin_id').addEventListener('change', function() {
    const fromBinId = this.value;
    const toBinSelect = document.getElementById('to_bin_id');
    
    // Disable the selected from bin in the to bin dropdown
    Array.from(toBinSelect.options).forEach(option => {
        if (option.value === fromBinId) {
            option.disabled = true;
        } else {
            option.disabled = false;
        }
    });
});

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

