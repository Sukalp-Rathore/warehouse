@extends('layout')

@section('title', 'Tenant Configuration')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="ri-settings-3-line me-2"></i>
                        Complete Your Tenant Configuration
                    </h4>
                    <p class="text-muted mb-0">Please configure your warehouse settings to continue</p>
                </div>
                <div class="card-body">
                    @if(session('info'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            {{ session('info') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('tenant.configure.store') }}" id="configForm">
                        @csrf
                        
                        <!-- Tenant Information -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="ri-building-line me-2"></i>
                                Tenant Information
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tenant_name" class="form-label">Tenant Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('tenant_name') is-invalid @enderror" 
                                               id="tenant_name" name="tenant_name" 
                                               value="{{ old('tenant_name', $settings->tenant_name ?? '') }}" required>
                                        @error('tenant_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tenant_email" class="form-label">Tenant Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('tenant_email') is-invalid @enderror" 
                                               id="tenant_email" name="tenant_email" 
                                               value="{{ old('tenant_email', $settings->tenant_email ?? '') }}" required>
                                        @error('tenant_email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="timezone" class="form-label">Timezone <span class="text-danger">*</span></label>
                                        <select class="form-select @error('timezone') is-invalid @enderror" 
                                                id="timezone" name="timezone" required>
                                            <option value="">Select Timezone</option>
                                            <option value="UTC" {{ old('timezone', $settings->timezone ?? '') == 'UTC' ? 'selected' : '' }}>UTC</option>
                                            <option value="America/New_York" {{ old('timezone', $settings->timezone ?? '') == 'America/New_York' ? 'selected' : '' }}>America/New_York</option>
                                            <option value="America/Chicago" {{ old('timezone', $settings->timezone ?? '') == 'America/Chicago' ? 'selected' : '' }}>America/Chicago</option>
                                            <option value="America/Los_Angeles" {{ old('timezone', $settings->timezone ?? '') == 'America/Los_Angeles' ? 'selected' : '' }}>America/Los_Angeles</option>
                                            <option value="Europe/London" {{ old('timezone', $settings->timezone ?? '') == 'Europe/London' ? 'selected' : '' }}>Europe/London</option>
                                            <option value="Asia/Kolkata" {{ old('timezone', $settings->timezone ?? '') == 'Asia/Kolkata' ? 'selected' : '' }}>Asia/Kolkata</option>
                                        </select>
                                        @error('timezone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Plant Details -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="ri-community-line me-2"></i>
                                Plant Details
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="plant_code" class="form-label">Plant Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('plant_code') is-invalid @enderror" 
                                               id="plant_code" name="plant_code" 
                                               value="{{ old('plant_code', $settings->plant_details['plant_code'] ?? 'PLT-001') }}" required>
                                        @error('plant_code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="plant_name" class="form-label">Plant Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('plant_name') is-invalid @enderror" 
                                               id="plant_name" name="plant_name" 
                                               value="{{ old('plant_name', $settings->plant_details['plant_name'] ?? 'Default Plant') }}" required>
                                        @error('plant_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="plant_location" class="form-label">Location <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('plant_location') is-invalid @enderror" 
                                               id="plant_location" name="plant_location" 
                                               value="{{ old('plant_location', $settings->plant_details['plant_location'] ?? '') }}" required>
                                        @error('plant_location')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="plant_address" class="form-label">Address</label>
                                        <textarea class="form-control @error('plant_address') is-invalid @enderror" 
                                                  id="plant_address" name="plant_address" rows="2">{{ old('plant_address', $settings->plant_details['plant_address'] ?? '') }}</textarea>
                                        @error('plant_address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="plant_contact_name" class="form-label">Contact Person</label>
                                        <input type="text" class="form-control @error('plant_contact_name') is-invalid @enderror" 
                                               id="plant_contact_name" name="plant_contact_name" 
                                               value="{{ old('plant_contact_name', $settings->plant_details['plant_contact_name'] ?? '') }}">
                                        @error('plant_contact_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="plant_contact_phone" class="form-label">Contact Phone</label>
                                        <input type="text" class="form-control @error('plant_contact_phone') is-invalid @enderror" 
                                               id="plant_contact_phone" name="plant_contact_phone" 
                                               value="{{ old('plant_contact_phone', $settings->plant_details['plant_contact_phone'] ?? '') }}">
                                        @error('plant_contact_phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="plant_contact_email" class="form-label">Contact Email</label>
                                        <input type="email" class="form-control @error('plant_contact_email') is-invalid @enderror" 
                                               id="plant_contact_email" name="plant_contact_email" 
                                               value="{{ old('plant_contact_email', $settings->plant_details['plant_contact_email'] ?? '') }}">
                                        @error('plant_contact_email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Warehouse Configuration -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="ri-store-3-line me-2"></i>
                                Warehouse Configuration
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="warehouse_code" class="form-label">Warehouse Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('warehouse_code') is-invalid @enderror" 
                                               id="warehouse_code" name="warehouse_code" 
                                               value="{{ old('warehouse_code', $settings->warehouse_config['warehouse_code'] ?? 'WH-001') }}" required>
                                        @error('warehouse_code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="warehouse_name" class="form-label">Warehouse Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('warehouse_name') is-invalid @enderror" 
                                               id="warehouse_name" name="warehouse_name" 
                                               value="{{ old('warehouse_name', $settings->warehouse_config['warehouse_name'] ?? 'Main WH') }}" required>
                                        @error('warehouse_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="storage_type" class="form-label">Storage Type <span class="text-danger">*</span></label>
                                        <select class="form-select @error('storage_type') is-invalid @enderror" 
                                                id="storage_type" name="storage_type" required>
                                            <option value="">Select Storage Type</option>
                                            <option value="Rack" {{ old('storage_type', $settings->warehouse_config['storage_type'] ?? 'Rack') == 'Rack' ? 'selected' : '' }}>Rack</option>
                                            <option value="Bulk" {{ old('storage_type', $settings->warehouse_config['storage_type'] ?? '') == 'Bulk' ? 'selected' : '' }}>Bulk</option>
                                            <option value="Pallet" {{ old('storage_type', $settings->warehouse_config['storage_type'] ?? '') == 'Pallet' ? 'selected' : '' }}>Pallet</option>
                                            <option value="Shelf" {{ old('storage_type', $settings->warehouse_config['storage_type'] ?? '') == 'Shelf' ? 'selected' : '' }}>Shelf</option>
                                            <option value="Flat" {{ old('storage_type', $settings->warehouse_config['storage_type'] ?? '') == 'Flat' ? 'selected' : '' }}>Flat</option>
                                        </select>
                                        @error('storage_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="default_strategy" class="form-label">Default Strategy</label>
                                        <select class="form-select @error('default_strategy') is-invalid @enderror" 
                                                id="default_strategy" name="default_strategy">
                                            <option value="FIFO" {{ old('default_strategy', $settings->warehouse_config['default_strategy'] ?? 'FIFO') == 'FIFO' ? 'selected' : '' }}>FIFO</option>
                                            <option value="LIFO" {{ old('default_strategy', $settings->warehouse_config['default_strategy'] ?? '') == 'LIFO' ? 'selected' : '' }}>LIFO</option>
                                            <option value="FEFO" {{ old('default_strategy', $settings->warehouse_config['default_strategy'] ?? '') == 'FEFO' ? 'selected' : '' }}>FEFO</option>
                                        </select>
                                        @error('default_strategy')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="capacity" class="form-label">Capacity</label>
                                        <input type="number" class="form-control @error('capacity') is-invalid @enderror" 
                                               id="capacity" name="capacity" min="1" 
                                               value="{{ old('capacity', $settings->warehouse_config['capacity'] ?? 1000) }}">
                                        @error('capacity')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bin Configuration -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="ri-stack-line me-2"></i>
                                Bin Configuration
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="bin_count" class="form-label">Number of Bins <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('bin_count') is-invalid @enderror" 
                                               id="bin_count" name="bin_count" min="1" max="1000" 
                                               value="{{ old('bin_count', $settings->bin_config['bin_count'] ?? 50) }}" required>
                                        <small class="form-text text-muted">Total number of bins to create (1-1000)</small>
                                        @error('bin_count')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="bin_volume" class="form-label">Bin Volume (m³) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('bin_volume') is-invalid @enderror" 
                                               id="bin_volume" name="bin_volume" step="0.1" min="0.1" 
                                               value="{{ old('bin_volume', $settings->bin_config['bin_volume'] ?? 1.0) }}" required>
                                        <small class="form-text text-muted">Maximum volume per bin</small>
                                        @error('bin_volume')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Barcode Settings -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="ri-barcode-line me-2"></i>
                                Barcode Settings
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="barcode_bin_prefix" class="form-label">Bin Prefix</label>
                                        <input type="text" class="form-control @error('barcode_bin_prefix') is-invalid @enderror" 
                                               id="barcode_bin_prefix" name="barcode_bin_prefix" maxlength="10" 
                                               value="{{ old('barcode_bin_prefix', $settings->barcode_settings['barcode_bin_prefix'] ?? 'BIN-') }}">
                                        <small class="form-text text-muted">Prefix for bin codes (e.g., BIN-001, BIN-002)</small>
                                        @error('barcode_bin_prefix')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="barcode_format" class="form-label">Barcode Format</label>
                                        <select class="form-select @error('barcode_format') is-invalid @enderror" 
                                                id="barcode_format" name="barcode_format">
                                            <option value="CODE128" {{ old('barcode_format', $settings->barcode_settings['barcode_format'] ?? 'CODE128') == 'CODE128' ? 'selected' : '' }}>CODE128</option>
                                            <option value="CODE39" {{ old('barcode_format', $settings->barcode_settings['barcode_format'] ?? '') == 'CODE39' ? 'selected' : '' }}>CODE39</option>
                                            <option value="EAN13" {{ old('barcode_format', $settings->barcode_settings['barcode_format'] ?? '') == 'EAN13' ? 'selected' : '' }}>EAN13</option>
                                            <option value="QRCODE" {{ old('barcode_format', $settings->barcode_settings['barcode_format'] ?? '') == 'QRCODE' ? 'selected' : '' }}>QR Code</option>
                                        </select>
                                        @error('barcode_format')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="ri-save-line me-2"></i>
                                Save Configuration & Initialize Warehouse
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('configForm');
    const submitBtn = document.getElementById('submitBtn');
    
    form.addEventListener('submit', function(e) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Setting up your warehouse...';
        
        // Show loading overlay
        const overlay = document.createElement('div');
        overlay.className = 'overlay1';
        overlay.innerHTML = '<div class="loader"><div class="line"></div><div class="line"></div><div class="line"></div><div class="line"></div></div>';
        document.body.appendChild(overlay);
    });
});
</script>
@endsection

