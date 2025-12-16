@extends('layout')

@section('title', 'Generate Bins')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Generate Bins</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('tenant.bins.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="warehouse_id" class="form-label">Warehouse <span class="text-danger">*</span></label>
                                    <select class="form-select @error('warehouse_id') is-invalid @enderror" 
                                            id="warehouse_id" name="warehouse_id" required onchange="loadStorageTypes(this.value)">
                                        <option value="">Select Warehouse</option>
                                        @foreach($warehouses as $wh)
                                            <option value="{{ $wh->_id }}" {{ old('warehouse_id', $warehouse?->_id) == $wh->_id ? 'selected' : '' }}>
                                                {{ $wh->warehouse_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('warehouse_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="storage_type_id" class="form-label">Storage Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('storage_type_id') is-invalid @enderror" 
                                            id="storage_type_id" name="storage_type_id" required onchange="setTimeout(() => onStorageTypeChange(), 10)">
                                        <option value="">Select Storage Type</option>
                                        @foreach($storageTypes as $st)
                                            <option value="{{ $st->_id }}" data-name="{{ $st->name }}" data-code="{{ $st->code }}">{{ $st->name }} ({{ $st->code }})</option>
                                        @endforeach
                                    </select>
                                    @error('storage_type_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div id="no-storage-types-hint" class="form-text" style="display:none;">
                                        No storage types found. 
                                        <a href="{{ route('tenant.storage-types.index') }}">Create one now</a>.
                                    </div>
                                </div>

                                <div class="mb-3" id="section-wrapper" style="display:none;">
                                    <label for="section_id" class="form-label">Section (High Rack only)</label>
                                    <select class="form-select @error('section_id') is-invalid @enderror" id="section_id" name="section_id">
                                        <option value="">Select Section</option>
                                    </select>
                                    @error('section_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div id="no-sections-hint" class="form-text" style="display:none;">
                                        No sections found for this storage type.
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="bin_count" class="form-label">Number of Bins <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('bin_count') is-invalid @enderror" 
                                           id="bin_count" name="bin_count" value="{{ old('bin_count', 50) }}" 
                                           min="1" max="1000" required>
                                    @error('bin_count')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="bin_prefix" class="form-label">Bin Prefix</label>
                                    <input type="text" class="form-control" id="bin_prefix" name="bin_prefix" 
                                           value="{{ old('bin_prefix', 'BIN-') }}" maxlength="10">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bin_volume" class="form-label">Bin Volume (m³)</label>
                                    <input type="number" step="0.1" class="form-control" id="bin_volume" name="bin_volume" 
                                           value="{{ old('bin_volume', 1.0) }}" min="0.1">
                                </div>

                                <div class="mb-3">
                                    <label for="bin_weight" class="form-label">Bin Weight (kg)</label>
                                    <input type="number" step="0.1" class="form-control" id="bin_weight" name="bin_weight" 
                                           value="{{ old('bin_weight', 100.0) }}" min="0.1">
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="assign_zones" name="assign_zones" value="1" checked>
                                    <label class="form-check-label" for="assign_zones">
                                        Auto-assign Zones (A, B, C, D)
                                    </label>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="generate_coordinates" name="generate_coordinates" value="1">
                                    <label class="form-check-label" for="generate_coordinates">
                                        Generate Coordinates
                                    </label>
                                </div>

                                <div class="mb-3" id="coordinate-pattern-div" style="display:none;">
                                    <label for="coordinate_pattern" class="form-label">Coordinate Pattern</label>
                                    <select class="form-select" id="coordinate_pattern" name="coordinate_pattern">
                                        <option value="rack">Rack Style</option>
                                        <option value="shelf">Shelf Style</option>
                                        <option value="flat">Flat Style</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="start_number" class="form-label">Start Number</label>
                                    <input type="number" class="form-control" id="start_number" name="start_number" 
                                           value="{{ old('start_number', 1) }}" min="1">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('tenant.bins.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Generate Bins</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Store storage types data globally for reference
let storageTypesData = {};

function loadStorageTypes(warehouseId) {
    if (!warehouseId) {
        // Reset if no warehouse selected
        document.getElementById('storage_type_id').innerHTML = '<option value="">Select Storage Type</option>';
        document.getElementById('section-wrapper').style.display = 'none';
        return;
    }
    const baseUrl = "{{ route('tenant.storage-types.list') }}";
    const url = `${baseUrl}?warehouse_id=${encodeURIComponent(warehouseId)}`;
    fetch(url)
        .then(res => res.json())
        .then(data => {
            const select = document.getElementById('storage_type_id');
            const hint = document.getElementById('no-storage-types-hint');
            select.innerHTML = '<option value="">Select Storage Type</option>';
            const list = Array.isArray(data) ? data : [];
            
            // Store storage types data for later reference
            storageTypesData = {};
            list.forEach(st => {
                storageTypesData[st._id] = { name: st.name, code: st.code };
                // Create option with data attributes
                const option = document.createElement('option');
                option.value = st._id;
                option.textContent = `${st.name} (${st.code})`;
                option.setAttribute('data-name', st.name);
                option.setAttribute('data-code', st.code);
                select.appendChild(option);
            });
            
            if (list.length === 0) {
                if (hint) hint.style.display = 'block';
            } else {
                if (hint) hint.style.display = 'none';
            }
            
            // Reset section dropdown when warehouse changes
            document.getElementById('section-wrapper').style.display = 'none';
            document.getElementById('section_id').innerHTML = '<option value="">Select Section</option>';
        })
        .catch((err) => {
            console.error('Error loading storage types:', err);
            const select = document.getElementById('storage_type_id');
            select.innerHTML = '<option value="">Select Storage Type</option>';
        });
}

function onStorageTypeChange() {
    // Get the value directly from the select element
    const storageTypeSelect = document.getElementById('storage_type_id');
    if (!storageTypeSelect) {
        console.error('Storage type select element not found');
        return;
    }
    
    const storageTypeId = storageTypeSelect.value;
    
    console.log('onStorageTypeChange called, storageTypeId:', storageTypeId);
    
    if (!storageTypeId || storageTypeId === '' || storageTypeId === 'undefined') {
        document.getElementById('section-wrapper').style.display = 'none';
        return;
    }
    
    // Get storage type data from stored data or from option element
    let stData = storageTypesData[storageTypeId];
    
    // If not in stored data, try to get from the option element
    if (!stData && storageTypeSelect) {
        const selectedOption = storageTypeSelect.options[storageTypeSelect.selectedIndex];
        console.log('Selected option:', selectedOption);
        if (selectedOption) {
            if (selectedOption.dataset && selectedOption.dataset.name) {
                stData = {
                    name: selectedOption.dataset.name,
                    code: selectedOption.dataset.code || ''
                };
            } else if (selectedOption.getAttribute('data-name')) {
                stData = {
                    name: selectedOption.getAttribute('data-name'),
                    code: selectedOption.getAttribute('data-code') || ''
                };
            }
            // Store it for future reference
            if (stData) {
                storageTypesData[storageTypeId] = stData;
            }
        }
    }
    
    console.log('Storage type data:', stData);
    
    // Check if storage type is High Rack
    const isHighRack = stData && (stData.name && stData.name.toLowerCase() === 'high rack' || stData.code && stData.code.toUpperCase() === 'HIGH_RACK');
    
    const sectionWrapper = document.getElementById('section-wrapper');
    const sectionSelect = document.getElementById('section_id');
    const noSectionsHint = document.getElementById('no-sections-hint');

    if (isHighRack) {
        sectionWrapper.style.display = 'block';
        // Load sections
        const baseUrl = "{{ route('tenant.storage-types.sections') }}";
        const url = `${baseUrl}?storage_type_id=${encodeURIComponent(storageTypeId)}`;
        
        console.log('Loading sections for storage type:', storageTypeId, 'URL:', url);
        
        fetch(url)
            .then(res => {
                if (!res.ok) {
                    console.error('Failed to fetch sections, status:', res.status);
                    throw new Error('Failed to fetch sections');
                }
                return res.json();
            })
            .then(list => {
                console.log('Sections loaded:', list);
                sectionSelect.innerHTML = '<option value="">Select Section</option>';
                const sections = Array.isArray(list) ? list : [];
                if (sections.length === 0) {
                    if (noSectionsHint) noSectionsHint.style.display = 'block';
                } else {
                    if (noSectionsHint) noSectionsHint.style.display = 'none';
                    sections.forEach(s => {
                        sectionSelect.innerHTML += `<option value="${s._id}">${s.section_code} - ${s.section_name}</option>`;
                    });
                }
            })
            .catch((err) => {
                console.error('Error loading sections:', err);
                sectionSelect.innerHTML = '<option value="">Select Section</option>';
                if (noSectionsHint) noSectionsHint.style.display = 'block';
            });
    } else {
        // Cold Room or other: no sections
        sectionWrapper.style.display = 'none';
        sectionSelect.innerHTML = '<option value="">Select Section</option>';
        if (noSectionsHint) noSectionsHint.style.display = 'none';
    }
}

document.getElementById('generate_coordinates').addEventListener('change', function() {
    document.getElementById('coordinate-pattern-div').style.display = this.checked ? 'block' : 'none';
});

// Initialize: Load storage types if warehouse is pre-selected and populate initial data
document.addEventListener('DOMContentLoaded', function() {
    // Populate storageTypesData from initial options (if any)
    const storageTypeSelect = document.getElementById('storage_type_id');
    if (storageTypeSelect) {
        Array.from(storageTypeSelect.options).forEach(option => {
            if (option.value && option.dataset.name) {
                storageTypesData[option.value] = {
                    name: option.dataset.name,
                    code: option.dataset.code || ''
                };
            }
        });
    }
    
    const warehouseSelect = document.getElementById('warehouse_id');
    if (warehouseSelect && warehouseSelect.value) {
        loadStorageTypes(warehouseSelect.value);
    }
    
    // If storage type is pre-selected, trigger section load
    if (storageTypeSelect && storageTypeSelect.value) {
        // Wait a bit for storage types to load if needed
        setTimeout(() => {
            onStorageTypeChange();
        }, 500);
    }
});
</script>
@endsection

