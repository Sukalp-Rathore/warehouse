@extends('layout')

@section('title', 'Adjust Stock')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Stock Adjustment</h4>
                        <a href="{{ route('tenant.inventory.show', $inventory->_id) }}" class="btn btn-secondary">
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

                    <div class="alert alert-info">
                        <strong>Item:</strong> {{ $inventory->item_code }} - {{ $inventory->item->description ?? '' }}<br>
                        <strong>Location:</strong> {{ $inventory->getLocationPath() }}<br>
                        <strong>Current Quantity:</strong> {{ number_format($inventory->quantity, 2) }} {{ $inventory->item->uom ?? 'PCS' }}<br>
                        <strong>Available Quantity:</strong> {{ number_format($inventory->available_qty, 2) }} {{ $inventory->item->uom ?? 'PCS' }}
                    </div>

                    <form action="{{ route('tenant.inventory.adjust.store', $inventory->_id) }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="adjustment_type" class="form-label">Adjustment Type *</label>
                                    <select name="adjustment_type" id="adjustment_type" class="form-select @error('adjustment_type') is-invalid @enderror" required>
                                        <option value="">Select Type</option>
                                        <option value="increase" {{ old('adjustment_type') == 'increase' ? 'selected' : '' }}>Increase Stock</option>
                                        <option value="decrease" {{ old('adjustment_type') == 'decrease' ? 'selected' : '' }}>Decrease Stock</option>
                                    </select>
                                    @error('adjustment_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
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
                                    <small class="text-muted" id="max_quantity_hint"></small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="reason" class="form-label">Reason for Adjustment *</label>
                                    <textarea name="reason" id="reason" class="form-control @error('reason') is-invalid @enderror" rows="3" required placeholder="Explain the reason for this stock adjustment...">{{ old('reason') }}</textarea>
                                    @error('reason')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-edit-line me-1"></i> Apply Adjustment
                            </button>
                            <a href="{{ route('tenant.inventory.show', $inventory->_id) }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const adjustmentType = document.getElementById('adjustment_type');
    const quantityInput = document.getElementById('quantity');
    const maxHint = document.getElementById('max_quantity_hint');
    const maxAvailable = {{ $inventory->available_qty }};

    adjustmentType.addEventListener('change', function() {
        if (this.value === 'decrease') {
            quantityInput.setAttribute('max', maxAvailable);
            maxHint.textContent = `Maximum: ${maxAvailable} (current available quantity)`;
            maxHint.style.display = 'block';
        } else {
            quantityInput.removeAttribute('max');
            maxHint.textContent = '';
            maxHint.style.display = 'none';
        }
    });
});
</script>
@endsection

