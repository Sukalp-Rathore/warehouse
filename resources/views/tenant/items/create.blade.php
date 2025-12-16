@extends('layout')

@section('title', 'Create Item')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Create New Item</h4>
                        <a href="{{ route('tenant.items.index') }}" class="btn btn-secondary">
                            <i class="ri-arrow-left-line me-1"></i> Back to Items
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Please correct the errors below:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('tenant.items.store') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Basic Information</h5>
                                
                                <div class="mb-3">
                                    <label for="item_code" class="form-label">Item Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('item_code') is-invalid @enderror" 
                                           id="item_code" name="item_code" value="{{ old('item_code') }}" required>
                                    @error('item_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="sku" class="form-label">SKU</label>
                                    <input type="text" class="form-control @error('sku') is-invalid @enderror" 
                                           id="sku" name="sku" value="{{ old('sku') }}">
                                    @error('sku')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('description') is-invalid @enderror" 
                                           id="description" name="description" value="{{ old('description') }}" required>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="short_description" class="form-label">Short Description</label>
                                    <input type="text" class="form-control @error('short_description') is-invalid @enderror" 
                                           id="short_description" name="short_description" value="{{ old('short_description') }}" maxlength="100">
                                    @error('short_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="type" class="form-label">Type</label>
                                    <select class="form-select @error('type') is-invalid @enderror" id="type" name="type">
                                        <option value="">Select Type</option>
                                        <option value="RM" {{ old('type') == 'RM' ? 'selected' : '' }}>RM (Raw Material)</option>
                                        <option value="FG" {{ old('type') == 'FG' ? 'selected' : '' }}>FG (Finished Goods)</option>
                                        <option value="WIP" {{ old('type') == 'WIP' ? 'selected' : '' }}>WIP (Work in Progress)</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="uom" class="form-label">Unit of Measure (UOM) <span class="text-danger">*</span></label>
                                    <select class="form-select @error('uom') is-invalid @enderror" id="uom" name="uom" required>
                                        <option value="">Select UOM</option>
                                        <option value="PCS" {{ old('uom') == 'PCS' ? 'selected' : '' }}>PCS (Pieces)</option>
                                        <option value="KG" {{ old('uom') == 'KG' ? 'selected' : '' }}>KG (Kilograms)</option>
                                        <option value="L" {{ old('uom') == 'L' ? 'selected' : '' }}>L (Liters)</option>
                                        <option value="M" {{ old('uom') == 'M' ? 'selected' : '' }}>M (Meters)</option>
                                        <option value="BOX" {{ old('uom') == 'BOX' ? 'selected' : '' }}>BOX</option>
                                        <option value="CARTON" {{ old('uom') == 'CARTON' ? 'selected' : '' }}>CARTON</option>
                                    </select>
                                    @error('uom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="category_id" class="form-label">Category</label>
                                    <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id">
                                        <option value="">Select Category</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->_id }}" {{ old('category_id') == $cat->_id ? 'selected' : '' }}>
                                                {{ $cat->category_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Dimensions & Weights -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Physical Properties</h5>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="weight" class="form-label">Weight (kg)</label>
                                            <input type="number" step="0.01" class="form-control @error('weight') is-invalid @enderror" 
                                                   id="weight" name="weight" value="{{ old('weight') }}">
                                            @error('weight')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="weight_per_unit" class="form-label">Weight/Unit (kg)</label>
                                            <input type="number" step="0.001" class="form-control @error('weight_per_unit') is-invalid @enderror" 
                                                   id="weight_per_unit" name="weight_per_unit" value="{{ old('weight_per_unit') }}">
                                            @error('weight_per_unit')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="volume" class="form-label">Volume (m³)</label>
                                            <input type="number" step="0.001" class="form-control @error('volume') is-invalid @enderror" 
                                                   id="volume" name="volume" value="{{ old('volume') }}">
                                            @error('volume')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="length" class="form-label">Length (cm)</label>
                                            <input type="number" step="0.01" class="form-control @error('length') is-invalid @enderror" 
                                                   id="length" name="length" value="{{ old('length') }}">
                                            @error('length')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="width" class="form-label">Width (cm)</label>
                                            <input type="number" step="0.01" class="form-control @error('width') is-invalid @enderror" 
                                                   id="width" name="width" value="{{ old('width') }}">
                                            @error('width')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="height" class="form-label">Height (cm)</label>
                                            <input type="number" step="0.01" class="form-control @error('height') is-invalid @enderror" 
                                                   id="height" name="height" value="{{ old('height') }}">
                                            @error('height')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="volume_per_unit" class="form-label">Volume per Unit (m³)</label>
                                    <input type="number" step="0.0001" class="form-control @error('volume_per_unit') is-invalid @enderror" 
                                           id="volume_per_unit" name="volume_per_unit" value="{{ old('volume_per_unit') }}">
                                    @error('volume_per_unit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="preferred_storage_type_id" class="form-label">Preferred Storage Type</label>
                                    <select class="form-select @error('preferred_storage_type_id') is-invalid @enderror" 
                                            id="preferred_storage_type_id" name="preferred_storage_type_id">
                                        <option value="">None</option>
                                        @foreach($storageTypes as $st)
                                            <option value="{{ $st->_id }}" {{ old('preferred_storage_type_id') == $st->_id ? 'selected' : '' }}>
                                                {{ $st->name }} ({{ $st->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('preferred_storage_type_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <!-- Vendors -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Vendors</h5>
                                
                                <div class="mb-3">
                                    <label for="vendor_ids" class="form-label">Select Vendors</label>
                                    <select class="form-select @error('vendor_ids') is-invalid @enderror" id="vendor_ids" name="vendor_ids[]" multiple size="5">
                                        @foreach($vendors as $vendor)
                                            <option value="{{ $vendor->_id }}" {{ in_array($vendor->_id, old('vendor_ids', [])) ? 'selected' : '' }}>
                                                {{ $vendor->vendor_name }} ({{ $vendor->vendor_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple vendors</small>
                                    @error('vendor_ids')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Reorder Settings -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Reorder Settings</h5>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="reorder_level" class="form-label">Reorder Level</label>
                                            <input type="number" class="form-control @error('reorder_level') is-invalid @enderror" 
                                                   id="reorder_level" name="reorder_level" value="{{ old('reorder_level') }}">
                                            @error('reorder_level')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="reorder_quantity" class="form-label">Reorder Quantity</label>
                                            <input type="number" class="form-control @error('reorder_quantity') is-invalid @enderror" 
                                                   id="reorder_quantity" name="reorder_quantity" value="{{ old('reorder_quantity') }}">
                                            @error('reorder_quantity')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="minimum_order_quantity" class="form-label">Min Order Qty</label>
                                            <input type="number" class="form-control @error('minimum_order_quantity') is-invalid @enderror" 
                                                   id="minimum_order_quantity" name="minimum_order_quantity" value="{{ old('minimum_order_quantity') }}">
                                            @error('minimum_order_quantity')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="barcode_format" class="form-label">Barcode Format</label>
                                    <select class="form-select @error('barcode_format') is-invalid @enderror" id="barcode_format" name="barcode_format">
                                        <option value="AUTO" {{ old('barcode_format') == 'AUTO' ? 'selected' : '' }}>AUTO (System Generated)</option>
                                        <option value="VENDOR" {{ old('barcode_format') == 'VENDOR' ? 'selected' : '' }}>VENDOR (Vendor Provided)</option>
                                    </select>
                                    @error('barcode_format')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="expire_life_days" class="form-label">Expiry Life (Days)</label>
                                    <input type="number" class="form-control @error('expire_life_days') is-invalid @enderror" 
                                           id="expire_life_days" name="expire_life_days" value="{{ old('expire_life_days') }}" 
                                           placeholder="For FEFO tracking (e.g., 365)">
                                    @error('expire_life_days')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-3">Additional Properties</h5>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_hazmat" name="is_hazmat" value="1" {{ old('is_hazmat') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_hazmat">
                                            Hazmat Item
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3" id="hazmat_class_section" style="display: {{ old('is_hazmat') ? 'block' : 'none' }}">
                                    <label for="hazmat_class" class="form-label">Hazmat Class</label>
                                    <input type="text" class="form-control @error('hazmat_class') is-invalid @enderror" 
                                           id="hazmat_class" name="hazmat_class" value="{{ old('hazmat_class') }}">
                                    @error('hazmat_class')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="temperature_controlled" name="temperature_controlled" value="1" {{ old('temperature_controlled') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="temperature_controlled">
                                            Temperature Controlled
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="image" class="form-label">Item Image</label>
                                    <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                           id="image" name="image" accept="image/*">
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="barcode" class="form-label">Barcode (if vendor provided)</label>
                                    <input type="text" class="form-control @error('barcode') is-invalid @enderror" 
                                           id="barcode" name="barcode" value="{{ old('barcode') }}">
                                    @error('barcode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Leave empty if using AUTO barcode format</small>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line me-1"></i> Create Item
                            </button>
                            <a href="{{ route('tenant.items.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const hazmatCheck = document.getElementById('is_hazmat');
    const hazmatSection = document.getElementById('hazmat_class_section');
    
    hazmatCheck.addEventListener('change', function() {
        hazmatSection.style.display = this.checked ? 'block' : 'none';
        if (!this.checked) {
            document.getElementById('hazmat_class').value = '';
        }
    });
});
</script>
@endsection

