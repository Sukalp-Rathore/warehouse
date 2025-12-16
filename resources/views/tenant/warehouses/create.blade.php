@extends('layout')

@section('title', 'Create Warehouse')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Create New Warehouse</h4>
                        <a href="{{ route('tenant.warehouses.index') }}" class="btn btn-outline-secondary">
                            <i class="ri-arrow-left-line me-1"></i> Back to Warehouses
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
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <strong>Please correct the errors below.</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('tenant.warehouses.store') }}">
                        @csrf
                        
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Basic Information</h5>
                                
                                <div class="mb-3">
                                    <label for="warehouse_code" class="form-label">Warehouse Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('warehouse_code') is-invalid @enderror" 
                                           id="warehouse_code" name="warehouse_code" value="{{ old('warehouse_code') }}" required>
                                    @error('warehouse_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="warehouse_name" class="form-label">Warehouse Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('warehouse_name') is-invalid @enderror" 
                                           id="warehouse_name" name="warehouse_name" value="{{ old('warehouse_name') }}" required>
                                    @error('warehouse_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="plant_id" class="form-label">Plant <span class="text-danger">*</span></label>
                                    <select class="form-select @error('plant_id') is-invalid @enderror" 
                                            id="plant_id" name="plant_id" required>
                                        <option value="">Select Plant</option>
                                        @foreach($plants as $plant)
                                            <option value="{{ $plant->_id }}" {{ old('plant_id') == $plant->_id ? 'selected' : '' }}>
                                                {{ $plant->plant_name }} ({{ $plant->plant_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('plant_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="warehouse_type" class="form-label">Warehouse Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('warehouse_type') is-invalid @enderror" 
                                            id="warehouse_type" name="warehouse_type" required>
                                        <option value="">Select Type</option>
                                        <option value="FG" {{ old('warehouse_type') == 'FG' ? 'selected' : '' }}>Finished Goods (FG)</option>
                                        <option value="RM" {{ old('warehouse_type') == 'RM' ? 'selected' : '' }}>Raw Materials (RM)</option>
                                        <option value="Cold Storage" {{ old('warehouse_type') == 'Cold Storage' ? 'selected' : '' }}>Cold Storage</option>
                                        <option value="Quarantine" {{ old('warehouse_type') == 'Quarantine' ? 'selected' : '' }}>Quarantine</option>
                                        <option value="Transit" {{ old('warehouse_type') == 'Transit' ? 'selected' : '' }}>Transit</option>
                                        <option value="Distribution" {{ old('warehouse_type') == 'Distribution' ? 'selected' : '' }}>Distribution</option>
                                    </select>
                                    @error('warehouse_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" 
                                              id="address" name="address" rows="3" required>{{ old('address') }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Storage Types & Configuration -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Storage Types</h5>
                                
                                <div class="mb-3">
                                    <label class="form-label">Assign Storage Types</label>
                                    <div class="form-check">
                                        @foreach($storageTypes as $st)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="storage_types[]" 
                                                       value="{{ $st->_id }}" id="st_{{ $st->_id }}"
                                                       {{ in_array($st->_id, old('storage_types', [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="st_{{ $st->_id }}">
                                                    {{ $st->name }} ({{ $st->code }})
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="capacity" class="form-label">Capacity</label>
                                    <input type="number" class="form-control @error('capacity') is-invalid @enderror" 
                                           id="capacity" name="capacity" value="{{ old('capacity', 1000) }}" min="1">
                                    @error('capacity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <h5 class="mb-3 mt-4">Contact Information</h5>
                                
                                <div class="mb-3">
                                    <label for="contact_person" class="form-label">Contact Person</label>
                                    <input type="text" class="form-control @error('contact_person') is-invalid @enderror" 
                                           id="contact_person" name="contact_person" value="{{ old('contact_person') }}">
                                    @error('contact_person')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="contact_phone" class="form-label">Contact Phone</label>
                                    <input type="text" class="form-control @error('contact_phone') is-invalid @enderror" 
                                           id="contact_phone" name="contact_phone" value="{{ old('contact_phone') }}">
                                    @error('contact_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="contact_email" class="form-label">Contact Email</label>
                                    <input type="email" class="form-control @error('contact_email') is-invalid @enderror" 
                                           id="contact_email" name="contact_email" value="{{ old('contact_email') }}">
                                    @error('contact_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="2">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Bin Generation Options -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-header">
                                        <h6 class="mb-0">Bin Auto-Generation (Optional)</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="generate_bins" name="generate_bins" value="1" {{ old('generate_bins') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="generate_bins">
                                                Generate bins automatically for this warehouse
                                            </label>
                                        </div>

                                        <div id="bin-generation-options" style="{{ old('generate_bins') ? '' : 'display:none;' }}">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label for="bin_storage_type" class="form-label">Storage Type <span class="text-danger">*</span></label>
                                                    <select class="form-select" id="bin_storage_type" name="bin_storage_type">
                                                        <option value="">Select Storage Type</option>
                                                        @foreach($storageTypes as $st)
                                                            <option value="{{ $st->_id }}">{{ $st->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <label for="bin_count" class="form-label">Bin Count <span class="text-danger">*</span></label>
                                                    <input type="number" class="form-control" id="bin_count" name="bin_count" 
                                                           value="{{ old('bin_count', 50) }}" min="1" max="1000">
                                                </div>
                                                <div class="col-md-2">
                                                    <label for="bin_prefix" class="form-label">Bin Prefix</label>
                                                    <input type="text" class="form-control" id="bin_prefix" name="bin_prefix" 
                                                           value="{{ old('bin_prefix', 'BIN-') }}" maxlength="10">
                                                </div>
                                                <div class="col-md-2">
                                                    <label for="bin_volume" class="form-label">Bin Volume (m³)</label>
                                                    <input type="number" step="0.1" class="form-control" id="bin_volume" name="bin_volume" 
                                                           value="{{ old('bin_volume', 1.0) }}" min="0.1">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('tenant.warehouses.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Warehouse</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('generate_bins').addEventListener('change', function() {
    document.getElementById('bin-generation-options').style.display = this.checked ? 'block' : 'none';
});
</script>
@endsection

