@extends('layout')

@section('title', 'Transfer Inventory')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Transfer Inventory</h4>
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
                        <strong>Current Location:</strong> {{ $inventory->getLocationPath() }}<br>
                        <strong>Available Quantity:</strong> {{ number_format($inventory->available_qty, 2) }} {{ $inventory->item->uom ?? 'PCS' }}
                    </div>

                    <form action="{{ route('tenant.inventory.transfer.store', $inventory->_id) }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Item</label>
                                    <input type="text" class="form-control" value="{{ $inventory->item_code }} - {{ $inventory->item->description ?? '' }}" disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Current Bin</label>
                                    <input type="text" class="form-control" value="{{ $inventory->bin->bin_code ?? 'N/A' }}" disabled>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="to_bin_id" class="form-label">Destination Bin *</label>
                                    <select name="to_bin_id" id="to_bin_id" class="form-select @error('to_bin_id') is-invalid @enderror" required>
                                        <option value="">Select Destination Bin</option>
                                        @foreach($bins as $bin)
                                            <option value="{{ $bin->_id }}" {{ old('to_bin_id') == $bin->_id ? 'selected' : '' }}>
                                                {{ $bin->bin_code }} - {{ $bin->bin_name }}
                                                @if($bin->storageType)
                                                    ({{ $bin->storageType->name }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('to_bin_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Quantity to Transfer *</label>
                                    <input type="number" name="quantity" id="quantity" class="form-control @error('quantity') is-invalid @enderror" 
                                           value="{{ old('quantity') }}" step="0.01" min="0.01" max="{{ $inventory->available_qty }}" required>
                                    @error('quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Maximum: {{ number_format($inventory->available_qty, 2) }} {{ $inventory->item->uom ?? 'PCS' }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Notes (Optional)</label>
                                    <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Reason for transfer...">{{ old('notes') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-arrow-left-right-line me-1"></i> Transfer Items
                            </button>
                            <a href="{{ route('tenant.inventory.show', $inventory->_id) }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

