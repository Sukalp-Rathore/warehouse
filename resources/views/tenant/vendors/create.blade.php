@extends('layout')

@section('title', 'Create Vendor')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Create New Vendor</h4>
                        <a href="{{ route('tenant.vendors.index') }}" class="btn btn-secondary">
                            <i class="ri-arrow-left-line me-1"></i> Back to Vendors
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

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('tenant.vendors.store') }}">
                        @csrf
                        
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Basic Information</h5>
                                
                                <div class="mb-3">
                                    <label for="vendor_code" class="form-label">Vendor Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('vendor_code') is-invalid @enderror" 
                                           id="vendor_code" name="vendor_code" value="{{ old('vendor_code') }}" required>
                                    @error('vendor_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="vendor_name" class="form-label">Vendor Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('vendor_name') is-invalid @enderror" 
                                           id="vendor_name" name="vendor_name" value="{{ old('vendor_name') }}" required>
                                    @error('vendor_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="vendor_type" class="form-label">Vendor Type</label>
                                    <select class="form-select @error('vendor_type') is-invalid @enderror" id="vendor_type" name="vendor_type">
                                        <option value="">Select Type</option>
                                        <option value="Manufacturer" {{ old('vendor_type') == 'Manufacturer' ? 'selected' : '' }}>Manufacturer</option>
                                        <option value="Distributor" {{ old('vendor_type') == 'Distributor' ? 'selected' : '' }}>Distributor</option>
                                        <option value="Retailer" {{ old('vendor_type') == 'Retailer' ? 'selected' : '' }}>Retailer</option>
                                    </select>
                                    @error('vendor_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="rating" class="form-label">Rating (0-5)</label>
                                    <input type="number" step="0.1" min="0" max="5" class="form-control @error('rating') is-invalid @enderror" 
                                           id="rating" name="rating" value="{{ old('rating', 0) }}">
                                    @error('rating')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Contact Information</h5>
                                
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
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <!-- Address -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Address</h5>
                                
                                <div class="mb-3">
                                    <label for="address_street" class="form-label">Street</label>
                                    <input type="text" class="form-control" id="address_street" name="address[street]" 
                                           value="{{ old('address.street') }}">
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="address_city" class="form-label">City</label>
                                            <input type="text" class="form-control" id="address_city" name="address[city]" 
                                                   value="{{ old('address.city') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="address_state" class="form-label">State</label>
                                            <input type="text" class="form-control" id="address_state" name="address[state]" 
                                                   value="{{ old('address.state') }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="address_country" class="form-label">Country</label>
                                            <input type="text" class="form-control" id="address_country" name="address[country]" 
                                                   value="{{ old('address.country') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="address_zipcode" class="form-label">Zipcode</label>
                                            <input type="text" class="form-control" id="address_zipcode" name="address[zipcode]" 
                                                   value="{{ old('address.zipcode') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Business Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Business Information</h5>
                                
                                <div class="mb-3">
                                    <label for="payment_terms" class="form-label">Payment Terms</label>
                                    <input type="text" class="form-control @error('payment_terms') is-invalid @enderror" 
                                           id="payment_terms" name="payment_terms" value="{{ old('payment_terms') }}" 
                                           placeholder="e.g., Net 30, Net 60">
                                    @error('payment_terms')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="tax_id" class="form-label">Tax ID</label>
                                    <input type="text" class="form-control @error('tax_id') is-invalid @enderror" 
                                           id="tax_id" name="tax_id" value="{{ old('tax_id') }}">
                                    @error('tax_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="allow_prebarcode" name="allow_prebarcode" value="1" 
                                               {{ old('allow_prebarcode') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="allow_prebarcode">
                                            Allow Pre-barcode
                                        </label>
                                    </div>
                                    <small class="text-muted">Vendor provides pre-printed barcodes</small>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <!-- Vendor Portal -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Vendor Portal (Optional)</h5>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="vendor_portal_enabled" name="vendor_portal_enabled" value="1" 
                                               {{ old('vendor_portal_enabled') ? 'checked' : '' }} onchange="togglePortalFields()">
                                        <label class="form-check-label" for="vendor_portal_enabled">
                                            Enable Vendor Portal Access
                                        </label>
                                    </div>
                                </div>

                                <div id="portal_fields" style="display: {{ old('vendor_portal_enabled') ? 'block' : 'none' }}">
                                    <div class="mb-3">
                                        <label for="portal_login" class="form-label">Portal Login <span class="text-danger portal-required" style="display: none;">*</span></label>
                                        <input type="text" class="form-control @error('portal_login') is-invalid @enderror" 
                                               id="portal_login" name="portal_login" value="{{ old('portal_login') }}">
                                        @error('portal_login')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="portal_password" class="form-label">Portal Password <span class="text-danger portal-required" style="display: none;">*</span></label>
                                        <input type="password" class="form-control @error('portal_password') is-invalid @enderror" 
                                               id="portal_password" name="portal_password">
                                        @error('portal_password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Minimum 6 characters</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Notes -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Additional Information</h5>
                                
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                                              id="notes" name="notes" rows="5" maxlength="1000">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Additional notes about the vendor</small>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line me-1"></i> Create Vendor
                            </button>
                            <a href="{{ route('tenant.vendors.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePortalFields() {
    const enabled = document.getElementById('vendor_portal_enabled').checked;
    const portalFields = document.getElementById('portal_fields');
    const portalLogin = document.getElementById('portal_login');
    const portalPassword = document.getElementById('portal_password');
    const requiredSpans = document.querySelectorAll('.portal-required');
    
    if (enabled) {
        portalFields.style.display = 'block';
        portalLogin.setAttribute('required', 'required');
        portalPassword.setAttribute('required', 'required');
        portalLogin.disabled = false;
        portalPassword.disabled = false;
        requiredSpans.forEach(span => span.style.display = 'inline');
    } else {
        portalFields.style.display = 'none';
        portalLogin.removeAttribute('required');
        portalPassword.removeAttribute('required');
        portalLogin.value = '';
        portalPassword.value = '';
        // Disable fields to prevent them from being submitted
        portalLogin.disabled = true;
        portalPassword.disabled = true;
        requiredSpans.forEach(span => span.style.display = 'none');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    togglePortalFields();
});
</script>
@endsection

