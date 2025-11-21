@extends('layout')

@section('title', 'Create Delivery Order')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Create Delivery Order</h4>
                        <a href="{{ route('tenant.delivery-orders.index') }}" class="btn btn-secondary">
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

                    <form action="{{ route('tenant.delivery-orders.store') }}" method="POST">
                        @csrf
                        
                        <h5 class="mb-3">Customer Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="customer_name" class="form-label">Customer Name *</label>
                                    <input type="text" name="customer_name" id="customer_name" 
                                           class="form-control @error('customer_name') is-invalid @enderror" 
                                           value="{{ old('customer_name') }}" required>
                                    @error('customer_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="customer_code" class="form-label">Customer Code</label>
                                    <input type="text" name="customer_code" id="customer_code" 
                                           class="form-control @error('customer_code') is-invalid @enderror" 
                                           value="{{ old('customer_code') }}">
                                    @error('customer_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <h5 class="mb-3 mt-4">Order Details</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="warehouse_id" class="form-label">Warehouse *</label>
                                    <select name="warehouse_id" id="warehouse_id" class="form-select @error('warehouse_id') is-invalid @enderror" required>
                                        <option value="">Select Warehouse</option>
                                        @foreach($warehouses as $warehouse)
                                            <option value="{{ $warehouse->_id }}" {{ old('warehouse_id') == $warehouse->_id ? 'selected' : '' }}>
                                                {{ $warehouse->warehouse_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('warehouse_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="delivery_date" class="form-label">Delivery Date</label>
                                    <input type="date" name="delivery_date" id="delivery_date" 
                                           class="form-control @error('delivery_date') is-invalid @enderror" 
                                           value="{{ old('delivery_date') }}">
                                    @error('delivery_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
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
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="picking_strategy" class="form-label">Picking Strategy</label>
                                    <select name="picking_strategy" id="picking_strategy" class="form-select">
                                        <option value="fifo" {{ old('picking_strategy') == 'fifo' ? 'selected' : '' }}>FIFO (First In First Out)</option>
                                        <option value="fefo" {{ old('picking_strategy') == 'fefo' ? 'selected' : '' }}>FEFO (First Expiry First Out)</option>
                                        <option value="wave" {{ old('picking_strategy') == 'wave' ? 'selected' : '' }}>Wave Picking</option>
                                        <option value="zone" {{ old('picking_strategy') == 'zone' ? 'selected' : '' }}>Zone Picking</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sap_reference" class="form-label">SAP Reference</label>
                                    <input type="text" name="sap_reference" id="sap_reference" 
                                           class="form-control" 
                                           value="{{ old('sap_reference') }}">
                                </div>
                            </div>
                        </div>

                        <h5 class="mb-3 mt-4">Items</h5>
                        <div id="items-container">
                            <div class="item-row mb-3 p-3 border rounded">
                                <div class="row">
                                    <div class="col-md-5">
                                        <label class="form-label">Item *</label>
                                        <select name="items[0][item_id]" class="form-select item-select" required>
                                            <option value="">Select Item</option>
                                            @foreach($items as $item)
                                                <option value="{{ $item->_id }}">
                                                    {{ $item->item_code }} - {{ $item->description }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Quantity *</label>
                                        <input type="number" step="0.01" name="items[0][quantity]" 
                                               class="form-control" required min="0.01">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="button" class="btn btn-danger w-100 remove-item" style="display:none;">
                                            <i class="ri-delete-bin-line"></i> Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary" id="add-item">
                            <i class="ri-add-line me-1"></i> Add Item
                        </button>

                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('tenant.delivery-orders.index') }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Delivery Order</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let itemIndex = 1;

document.getElementById('add-item').addEventListener('click', function() {
    const container = document.getElementById('items-container');
    const newRow = document.createElement('div');
    newRow.className = 'item-row mb-3 p-3 border rounded';
    newRow.innerHTML = `
        <div class="row">
            <div class="col-md-5">
                <label class="form-label">Item *</label>
                <select name="items[${itemIndex}][item_id]" class="form-select item-select" required>
                    <option value="">Select Item</option>
                    @foreach($items as $item)
                        <option value="{{ $item->_id }}">
                            {{ $item->item_code }} - {{ $item->description }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Quantity *</label>
                <input type="number" step="0.01" name="items[${itemIndex}][quantity]" 
                       class="form-control" required min="0.01">
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <button type="button" class="btn btn-danger w-100 remove-item">
                    <i class="ri-delete-bin-line"></i> Remove
                </button>
            </div>
        </div>
    `;
    container.appendChild(newRow);
    itemIndex++;
    updateRemoveButtons();
});

document.getElementById('items-container').addEventListener('click', function(e) {
    if (e.target.closest('.remove-item')) {
        e.target.closest('.item-row').remove();
        updateRemoveButtons();
    }
});

function updateRemoveButtons() {
    const rows = document.querySelectorAll('.item-row');
    rows.forEach((row, index) => {
        const removeBtn = row.querySelector('.remove-item');
        if (rows.length > 1) {
            removeBtn.style.display = 'block';
        } else {
            removeBtn.style.display = 'none';
        }
    });
}

updateRemoveButtons();
</script>
@endsection

