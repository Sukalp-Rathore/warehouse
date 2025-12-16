@extends('layout')

@section('title', 'Edit Warehouse')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Edit Warehouse: {{ $warehouse->warehouse_name }}</h4>
                        <a href="{{ route('tenant.warehouses.index') }}" class="btn btn-outline-secondary">
                            <i class="ri-arrow-left-line me-1"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('tenant.warehouses.update', $warehouse->_id) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="warehouse_code" class="form-label">Warehouse Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('warehouse_code') is-invalid @enderror" 
                                           id="warehouse_code" name="warehouse_code" value="{{ old('warehouse_code', $warehouse->warehouse_code) }}" required>
                                    @error('warehouse_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="warehouse_name" class="form-label">Warehouse Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('warehouse_name') is-invalid @enderror" 
                                           id="warehouse_name" name="warehouse_name" value="{{ old('warehouse_name', $warehouse->warehouse_name) }}" required>
                                    @error('warehouse_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="plant_id" class="form-label">Plant <span class="text-danger">*</span></label>
                                    <select class="form-select @error('plant_id') is-invalid @enderror" 
                                            id="plant_id" name="plant_id" required>
                                        @foreach($plants as $plant)
                                            <option value="{{ $plant->_id }}" {{ old('plant_id', $warehouse->plant_id) == $plant->_id ? 'selected' : '' }}>
                                                {{ $plant->plant_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="warehouse_type" class="form-label">Warehouse Type <span class="text-danger">*</span></label>
                                    <select class="form-select" id="warehouse_type" name="warehouse_type" required>
                                        <option value="FG" {{ old('warehouse_type', $warehouse->warehouse_type) == 'FG' ? 'selected' : '' }}>FG</option>
                                        <option value="RM" {{ old('warehouse_type', $warehouse->warehouse_type) == 'RM' ? 'selected' : '' }}>RM</option>
                                        <option value="Cold Storage" {{ old('warehouse_type', $warehouse->warehouse_type) == 'Cold Storage' ? 'selected' : '' }}>Cold Storage</option>
                                        <option value="Quarantine" {{ old('warehouse_type', $warehouse->warehouse_type) == 'Quarantine' ? 'selected' : '' }}>Quarantine</option>
                                        <option value="Transit" {{ old('warehouse_type', $warehouse->warehouse_type) == 'Transit' ? 'selected' : '' }}>Transit</option>
                                        <option value="Distribution" {{ old('warehouse_type', $warehouse->warehouse_type) == 'Distribution' ? 'selected' : '' }}>Distribution</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="address" name="address" rows="3" required>{{ old('address', $warehouse->address) }}</textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Storage Types</label>
                                    @php
                                        $assignedTypes = $warehouse->storage_types ?? [];
                                    @endphp
                                    @foreach($storageTypes as $st)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="storage_types[]" 
                                                   value="{{ $st->_id }}" id="st_{{ $st->_id }}"
                                                   {{ in_array($st->_id, old('storage_types', $assignedTypes)) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="st_{{ $st->_id }}">
                                                {{ $st->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mb-3">
                                    <label for="capacity" class="form-label">Capacity</label>
                                    <input type="number" class="form-control" id="capacity" name="capacity" 
                                           value="{{ old('capacity', $warehouse->capacity) }}" min="1">
                                </div>

                                <div class="mb-3">
                                    <label for="contact_person" class="form-label">Contact Person</label>
                                    <input type="text" class="form-control" id="contact_person" name="contact_person" 
                                           value="{{ old('contact_person', $warehouse->contact_person) }}">
                                </div>

                                <div class="mb-3">
                                    <label for="contact_phone" class="form-label">Contact Phone</label>
                                    <input type="text" class="form-control" id="contact_phone" name="contact_phone" 
                                           value="{{ old('contact_phone', $warehouse->contact_phone) }}">
                                </div>

                                <div class="mb-3">
                                    <label for="contact_email" class="form-label">Contact Email</label>
                                    <input type="email" class="form-control" id="contact_email" name="contact_email" 
                                           value="{{ old('contact_email', $warehouse->contact_email) }}">
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="2">{{ old('description', $warehouse->description) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('tenant.warehouses.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Warehouse</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

