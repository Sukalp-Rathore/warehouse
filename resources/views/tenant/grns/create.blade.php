@extends('layout')

@section('title', 'Create GRN')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Create GRN</h4>
                        <a href="{{ route('tenant.grns.index') }}" class="btn btn-secondary">
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

                    <form action="{{ route('tenant.grns.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="vendor_id" class="form-label">Vendor *</label>
                                    <select name="vendor_id" id="vendor_id" class="form-select @error('vendor_id') is-invalid @enderror" required>
                                        <option value="">Select Vendor</option>
                                        @foreach($vendors as $vendor)
                                            <option value="{{ $vendor->_id }}" {{ old('vendor_id') == $vendor->_id ? 'selected' : '' }}>
                                                {{ $vendor->vendor_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('vendor_id')
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

                        <div class="mb-3">
                            <label for="po_reference" class="form-label">PO Reference (Optional)</label>
                            <input type="text" name="po_reference" id="po_reference" class="form-control" 
                                   value="{{ old('po_reference') }}" placeholder="PO-2025-001">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Items *</label>
                            <div id="items-container">
                                <div class="item-row card mb-3 p-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label">Item</label>
                                            <select name="items[0][item_id]" class="form-select item-select" required>
                                                <option value="">Select Item</option>
                                                @foreach($items as $item)
                                                    <option value="{{ $item->_id }}">{{ $item->item_code }} - {{ $item->description }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Quantity</label>
                                            <input type="number" name="items[0][quantity]" class="form-control" step="0.01" min="0.01" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">&nbsp;</label>
                                            <button type="button" class="btn btn-danger w-100 remove-item" style="display: none;">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="add-item-row">
                                <i class="ri-add-line me-1"></i> Add Item
                            </button>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line me-1"></i> Create GRN
                            </button>
                            <a href="{{ route('tenant.grns.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let itemRowCount = 1;
document.getElementById('add-item-row').addEventListener('click', function() {
    const container = document.getElementById('items-container');
    const newRow = container.firstElementChild.cloneNode(true);
    newRow.querySelectorAll('select, input').forEach(el => {
        el.name = el.name.replace('[0]', `[${itemRowCount}]`);
        el.value = '';
    });
    newRow.querySelector('.remove-item').style.display = 'block';
    container.appendChild(newRow);
    itemRowCount++;
});

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-item') || e.target.closest('.remove-item')) {
        const btn = e.target.classList.contains('remove-item') ? e.target : e.target.closest('.remove-item');
        if (document.getElementById('items-container').children.length > 1) {
            btn.closest('.item-row').remove();
        }
    }
});
</script>
@endsection

