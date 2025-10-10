<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tenant - {{ $tenant->tenant_name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .card-header {
            border-bottom: 2px solid rgba(0,0,0,0.1);
        }
        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        .btn-primary {
            background: linear-gradient(45deg, #007bff, #0056b3);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: linear-gradient(45deg, #0056b3, #004085);
            transform: translateY(-1px);
        }
        .loading {
            display: none;
        }
        .alert {
            border-radius: 10px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-10">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="display-5 fw-bold text-primary">
                            <i class="fas fa-edit me-2"></i>Edit Tenant
                        </h1>
                        <p class="text-muted">Update configuration for {{ $tenant->tenant_name }}</p>
                    </div>
                    <a href="{{ route('tenant.show', $tenant->client_id) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Details
                    </a>
                </div>

                <!-- Alert Messages -->
                <div id="alertContainer"></div>

                <!-- Main Form -->
                <form id="tenantEditForm" class="needs-validation" novalidate>
                    @csrf
                    @method('PUT')

                    {{-- TENANT INFO --}}
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-dark text-white fw-semibold">
                            <i class="fas fa-info-circle me-2"></i>Tenant Information
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Tenant / Company Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="tenant_name" value="{{ $tenant->tenant_name }}" required>
                                    <div class="invalid-feedback">Please provide a valid company name.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Contact Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="tenant_email" value="{{ $tenant->tenant_email }}" required>
                                    <div class="invalid-feedback">Please provide a valid email address.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Timezone <span class="text-danger">*</span></label>
                                    <select class="form-select" name="timezone" required>
                                        <option value="">Select Timezone</option>
                                        <option value="Asia/Kolkata" {{ $tenant->timezone === 'Asia/Kolkata' ? 'selected' : '' }}>Asia/Kolkata</option>
                                        <option value="Asia/Dubai" {{ $tenant->timezone === 'Asia/Dubai' ? 'selected' : '' }}>Asia/Dubai</option>
                                        <option value="Europe/London" {{ $tenant->timezone === 'Europe/London' ? 'selected' : '' }}>Europe/London</option>
                                        <option value="America/New_York" {{ $tenant->timezone === 'America/New_York' ? 'selected' : '' }}>America/New_York</option>
                                        <option value="America/Los_Angeles" {{ $tenant->timezone === 'America/Los_Angeles' ? 'selected' : '' }}>America/Los_Angeles</option>
                                    </select>
                                    <div class="invalid-feedback">Please select a timezone.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- PLANT DETAILS --}}
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-primary text-white fw-semibold">
                            <i class="fas fa-industry me-2"></i>Plant Details
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Plant Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="plant_code" value="{{ $tenant->plant_details['plant_code'] ?? '' }}" required>
                                    <div class="invalid-feedback">Please provide a plant code.</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Plant Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="plant_name" value="{{ $tenant->plant_details['plant_name'] ?? '' }}" required>
                                    <div class="invalid-feedback">Please provide a plant name.</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Location</label>
                                    <input type="text" class="form-control" name="plant_location" value="{{ $tenant->plant_details['plant_location'] ?? '' }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Contact Person</label>
                                    <input type="text" class="form-control" name="plant_contact_name" value="{{ $tenant->plant_details['plant_contact_name'] ?? '' }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Contact Email</label>
                                    <input type="email" class="form-control" name="plant_contact_email" value="{{ $tenant->plant_details['plant_contact_email'] ?? '' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- WAREHOUSE CONFIG --}}
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-success text-white fw-semibold">
                            <i class="fas fa-warehouse me-2"></i>Warehouse & Storage Configuration
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Warehouse Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="warehouse_code" value="{{ $tenant->warehouse_config['warehouse_code'] ?? '' }}" required>
                                    <div class="invalid-feedback">Please provide a warehouse code.</div>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Warehouse Description</label>
                                    <input type="text" class="form-control" name="warehouse_name" value="{{ $tenant->warehouse_config['warehouse_name'] ?? '' }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Storage Type</label>
                                    <select class="form-select" name="storage_type">
                                        <option value="High Rack" {{ ($tenant->warehouse_config['storage_type'] ?? '') === 'High Rack' ? 'selected' : '' }}>High Rack</option>
                                        <option value="Cold Room" {{ ($tenant->warehouse_config['storage_type'] ?? '') === 'Cold Room' ? 'selected' : '' }}>Cold Room</option>
                                        <option value="Hazmat" {{ ($tenant->warehouse_config['storage_type'] ?? '') === 'Hazmat' ? 'selected' : '' }}>Hazmat</option>
                                        <option value="Floor Storage" {{ ($tenant->warehouse_config['storage_type'] ?? '') === 'Floor Storage' ? 'selected' : '' }}>Floor Storage</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Default Putaway Strategy</label>
                                    <select class="form-select" name="default_strategy">
                                        <option value="FIFO" {{ ($tenant->warehouse_config['default_strategy'] ?? '') === 'FIFO' ? 'selected' : '' }}>FIFO (First In, First Out)</option>
                                        <option value="FEFO" {{ ($tenant->warehouse_config['default_strategy'] ?? '') === 'FEFO' ? 'selected' : '' }}>FEFO (First Expiry, First Out)</option>
                                        <option value="WEIGHT" {{ ($tenant->warehouse_config['default_strategy'] ?? '') === 'WEIGHT' ? 'selected' : '' }}>Weight-Based</option>
                                        <option value="VOLUME" {{ ($tenant->warehouse_config['default_strategy'] ?? '') === 'VOLUME' ? 'selected' : '' }}>Volume-Based</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- BIN CONFIG --}}
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-warning fw-semibold">
                            <i class="fas fa-boxes me-2"></i>Bin & Section Setup
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Total Bins</label>
                                    <input type="number" class="form-control" name="bin_count" value="{{ $tenant->bin_config['bin_count'] ?? '' }}" min="1" max="1000">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Max Volume per Bin (m³)</label>
                                    <input type="number" class="form-control" step="0.01" name="bin_volume" value="{{ $tenant->bin_config['bin_volume'] ?? '' }}" min="0.1">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Reorder Level (L1 / L2)</label>
                                    <input type="text" class="form-control" name="reorder_levels" value="{{ $tenant->bin_config['reorder_levels'] ?? '' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ITEM / VENDOR RULES --}}
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-secondary text-white fw-semibold">
                            <i class="fas fa-handshake me-2"></i>Item & Vendor Rules
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Enable Vendor Portal?</label>
                                    <select class="form-select" name="vendor_portal">
                                        <option value="1" {{ ($tenant->item_vendor_rules['vendor_portal'] ?? '') == '1' ? 'selected' : '' }}>Yes</option>
                                        <option value="0" {{ ($tenant->item_vendor_rules['vendor_portal'] ?? '') == '0' ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Allow Vendor Pre-Barcoding?</label>
                                    <select class="form-select" name="vendor_prebarcode">
                                        <option value="1" {{ ($tenant->item_vendor_rules['vendor_prebarcode'] ?? '') == '1' ? 'selected' : '' }}>Yes</option>
                                        <option value="0" {{ ($tenant->item_vendor_rules['vendor_prebarcode'] ?? '') == '0' ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- BARCODE SETTINGS --}}
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-info text-white fw-semibold">
                            <i class="fas fa-barcode me-2"></i>Barcode Format
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Bin Prefix</label>
                                    <input type="text" class="form-control" name="barcode_bin_prefix" value="{{ $tenant->barcode_settings['barcode_bin_prefix'] ?? '' }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Shipment Prefix</label>
                                    <input type="text" class="form-control" name="barcode_shipment_prefix" value="{{ $tenant->barcode_settings['barcode_shipment_prefix'] ?? '' }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Barcode Length</label>
                                    <input type="number" class="form-control" name="barcode_length" value="{{ $tenant->barcode_settings['barcode_length'] ?? '' }}" min="4" max="20">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ROLES --}}
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-danger text-white fw-semibold">
                            <i class="fas fa-users-cog me-2"></i>Roles & Permissions
                        </div>
                        <div class="card-body">
                            <label class="form-label">Role Configuration (JSON Format)</label>
                            <textarea class="form-control" rows="6" name="roles_json">{{ $tenant->roles_permissions['roles_json'] ?? '' }}</textarea>
                            <div class="form-text">Define roles and their permissions in JSON format.</div>
                        </div>
                    </div>

                    {{-- REORDER / ALERT RULES --}}
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-dark text-white fw-semibold">
                            <i class="fas fa-bell me-2"></i>Reorder & Alert Rules
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Global Reorder L1</label>
                                    <input type="number" class="form-control" name="global_rl1" value="{{ $tenant->reorder_alert_rules['global_rl1'] ?? '20' }}" min="1">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Global Reorder L2</label>
                                    <input type="number" class="form-control" name="global_rl2" value="{{ $tenant->reorder_alert_rules['global_rl2'] ?? '10' }}" min="1">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Alert Channels</label>
                                    <select multiple class="form-select" name="alert_channels[]">
                                        <option value="email" {{ in_array('email', $tenant->reorder_alert_rules['alert_channels'] ?? []) ? 'selected' : '' }}>Email</option>
                                        <option value="sms" {{ in_array('sms', $tenant->reorder_alert_rules['alert_channels'] ?? []) ? 'selected' : '' }}>SMS</option>
                                        <option value="whatsapp" {{ in_array('whatsapp', $tenant->reorder_alert_rules['alert_channels'] ?? []) ? 'selected' : '' }}>WhatsApp</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- INTEGRATIONS --}}
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-secondary text-white fw-semibold">
                            <i class="fas fa-plug me-2"></i>Integration Settings
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">SAP Endpoint</label>
                                    <input type="url" class="form-control" name="sap_endpoint" value="{{ $tenant->integration_settings['sap_endpoint'] ?? '' }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">API Token</label>
                                    <input type="text" class="form-control" name="sap_token" value="{{ $tenant->integration_settings['sap_token'] ?? '' }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Enable WhatsApp Integration</label>
                                    <select class="form-select" name="whatsapp_enabled">
                                        <option value="1" {{ ($tenant->integration_settings['whatsapp_enabled'] ?? '') == '1' ? 'selected' : '' }}>Yes</option>
                                        <option value="0" {{ ($tenant->integration_settings['whatsapp_enabled'] ?? '') == '0' ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Voice/AI Provider</label>
                                    <select class="form-select" name="ai_provider">
                                        <option value="mistral" {{ ($tenant->integration_settings['ai_provider'] ?? '') === 'mistral' ? 'selected' : '' }}>Mistral</option>
                                        <option value="openai" {{ ($tenant->integration_settings['ai_provider'] ?? '') === 'openai' ? 'selected' : '' }}>OpenAI</option>
                                        <option value="none" {{ ($tenant->integration_settings['ai_provider'] ?? '') === 'none' ? 'selected' : '' }}>None</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SAVE --}}
                    <div class="text-center mb-5">
                        <button type="submit" class="btn btn-lg btn-primary px-5" id="submitBtn">
                            <i class="fas fa-save me-2"></i>Update Configuration
                        </button>
                        <div class="loading mt-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Updating tenant configuration...</p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('tenantEditForm');
            const submitBtn = document.getElementById('submitBtn');
            const loading = document.querySelector('.loading');
            const alertContainer = document.getElementById('alertContainer');

            // Form validation
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                
                if (!form.checkValidity()) {
                    event.stopPropagation();
                    form.classList.add('was-validated');
                    return;
                }

                // Show loading state
                submitBtn.disabled = true;
                loading.style.display = 'block';

                // Prepare form data
                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());

                // Handle multi-select values
                data.alert_channels = formData.getAll('alert_channels[]');

                // Submit form
                fetch('{{ route("tenant.update", $tenant->client_id) }}', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    loading.style.display = 'none';
                    submitBtn.disabled = false;

                    if (data.success) {
                        showAlert('success', data.message);
                        // Redirect after 2 seconds
                        setTimeout(() => {
                            window.location.href = '{{ route("tenant.show", $tenant->client_id) }}';
                        }, 2000);
                    } else {
                        showAlert('danger', data.message);
                    }
                })
                .catch(error => {
                    loading.style.display = 'none';
                    submitBtn.disabled = false;
                    showAlert('danger', 'An error occurred: ' + error.message);
                });
            });

            function showAlert(type, message) {
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
                alertDiv.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                alertContainer.appendChild(alertDiv);

                // Auto-dismiss after 5 seconds
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.remove();
                    }
                }, 5000);
            }
        });
    </script>
</body>
</html>
