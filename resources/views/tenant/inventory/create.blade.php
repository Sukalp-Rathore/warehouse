@extends('layout')

@section('title', 'Manual Item Placement')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Manual Item Placement</h4>
                        <a href="{{ route('tenant.inventory.index') }}" class="btn btn-secondary">
                            <i class="ri-arrow-left-line me-1"></i> Back to Inventory
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

                    <form action="{{ route('tenant.inventory.store') }}" method="POST">
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
                                    <label for="warehouse_id" class="form-label">Warehouse *</label>
                                    <select name="warehouse_id" id="warehouse_id" class="form-select @error('warehouse_id') is-invalid @enderror" required>
                                        <option value="">Select Warehouse</option>
                                        @foreach($warehouses as $wh)
                                            <option value="{{ $wh->_id }}" {{ old('warehouse_id') == $wh->_id ? 'selected' : '' }}>
                                                {{ $wh->warehouse_name }} @if($wh->plant) ({{ $wh->plant->plant_name }}) @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('warehouse_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bin_id" class="form-label">Bin *</label>
                                    <select name="bin_id" id="bin_id" class="form-select @error('bin_id') is-invalid @enderror" required>
                                        <option value="">Select Bin</option>
                                        @foreach($bins as $bin)
                                            <option value="{{ $bin->_id }}" {{ old('bin_id') == $bin->_id ? 'selected' : '' }}>
                                                {{ $bin->bin_code }} - {{ $bin->bin_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('bin_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Select a bin from the available bins. System will auto-generate barcode, QR code, and RFID tag.</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Quantity *</label>
                                    <input type="number" name="quantity" id="quantity" class="form-control @error('quantity') is-invalid @enderror" 
                                           value="{{ old('quantity') }}" step="0.01" min="0.01" required>
                                    @error('quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Batch Information</label>
                                    <div class="card bg-light p-3">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="batch_id" id="batch_existing" value="existing" {{ old('batch_id') != 'new' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="batch_existing">
                                                Use Existing Batch
                                            </label>
                                        </div>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="radio" name="batch_id" id="batch_new" value="new" {{ old('batch_id') == 'new' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="batch_new">
                                                Create New Batch
                                            </label>
                                        </div>

                                        <div id="existing_batch_section" style="display: {{ old('batch_id') == 'new' ? 'none' : 'block' }}">
                                            <select name="batch_id" id="batch_id_select" class="form-select">
                                                <option value="">No Batch (if applicable)</option>
                                                <!-- Batches will be loaded via AJAX based on selected item -->
                                            </select>
                                        </div>

                                        <div id="new_batch_section" style="display: {{ old('batch_id') == 'new' ? 'block' : 'none' }}">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label for="batch_id_new" class="form-label">Batch ID *</label>
                                                    <input type="text" name="batch_id_new" id="batch_id_new" class="form-control" 
                                                           value="{{ old('batch_id_new') }}" placeholder="BATCH-2025-001">
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="manufacturing_date" class="form-label">Manufacturing Date</label>
                                                    <input type="date" name="manufacturing_date" id="manufacturing_date" class="form-control" 
                                                           value="{{ old('manufacturing_date') }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="expiry_date" class="form-label">Expiry Date</label>
                                                    <input type="date" name="expiry_date" id="expiry_date" class="form-control" 
                                                           value="{{ old('expiry_date') }}">
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <label for="production_lot_number" class="form-label">Production Lot Number</label>
                                                <input type="text" name="production_lot_number" id="production_lot_number" class="form-control" 
                                                       value="{{ old('production_lot_number') }}" placeholder="Optional">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line me-1"></i> Place Item
                            </button>
                            <a href="{{ route('tenant.inventory.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const batchExisting = document.getElementById('batch_existing');
    const batchNew = document.getElementById('batch_new');
    const existingSection = document.getElementById('existing_batch_section');
    const newSection = document.getElementById('new_batch_section');

    batchExisting.addEventListener('change', function() {
        if (this.checked) {
            existingSection.style.display = 'block';
            newSection.style.display = 'none';
            document.getElementById('batch_id_new').removeAttribute('required');
        }
    });

    batchNew.addEventListener('change', function() {
        if (this.checked) {
            existingSection.style.display = 'none';
            newSection.style.display = 'block';
            document.getElementById('batch_id_new').setAttribute('required', 'required');
        }
    });

    // Load batches when item is selected
    const itemSelect = document.getElementById('item_id');
    const batchSelect = document.getElementById('batch_id_select');
    
    itemSelect.addEventListener('change', function() {
        const itemId = this.value;
        if (itemId) {
            // AJAX call to load batches for this item
            fetch(`/wms/tenant/items/${itemId}/batches`)
                .then(response => response.json())
                .then(data => {
                    batchSelect.innerHTML = '<option value="">No Batch</option>';
                    if (data.batches && data.batches.length > 0) {
                        data.batches.forEach(batch => {
                            const option = document.createElement('option');
                            option.value = batch._id;
                            option.textContent = batch.batch_id;
                            batchSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => console.error('Error loading batches:', error));
        } else {
            batchSelect.innerHTML = '<option value="">No Batch</option>';
        }
    });
});
</script>
@endsection

