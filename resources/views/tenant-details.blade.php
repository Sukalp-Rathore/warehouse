<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Details - {{ $tenant->tenant_name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .info-card {
            border-left: 4px solid #007bff;
        }
        .config-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="display-5 fw-bold text-primary">
                            <i class="fas fa-building me-2"></i>{{ $tenant->tenant_name }}
                        </h1>
                        <p class="text-muted">Tenant Configuration Details</p>
                    </div>
                    <div>
                        <a href="{{ route('tenant.edit', $tenant->client_id) }}" class="btn btn-warning me-2">
                            <i class="fas fa-edit me-1"></i>Edit
                        </a>
                        <a href="{{ route('tenant.list') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to List
                        </a>
                    </div>
                </div>

                <div class="row">
                    <!-- Basic Information -->
                    <div class="col-md-6 mb-4">
                        <div class="card info-card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Basic Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-4"><strong>Client ID:</strong></div>
                                    <div class="col-8">{{ $tenant->client_id }}</div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-4"><strong>Email:</strong></div>
                                    <div class="col-8">{{ $tenant->tenant_email }}</div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-4"><strong>Timezone:</strong></div>
                                    <div class="col-8">{{ $tenant->timezone }}</div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-4"><strong>Status:</strong></div>
                                    <div class="col-8">
                                        <span class="badge bg-{{ $tenant->status === 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($tenant->status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Plant Details -->
                    <div class="col-md-6 mb-4">
                        <div class="card info-card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-industry me-2"></i>Plant Details</h5>
                            </div>
                            <div class="card-body">
                                @if($tenant->plant_details)
                                    <div class="row mb-3">
                                        <div class="col-4"><strong>Plant Code:</strong></div>
                                        <div class="col-8">{{ $tenant->plant_details['plant_code'] ?? 'N/A' }}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-4"><strong>Plant Name:</strong></div>
                                        <div class="col-8">{{ $tenant->plant_details['plant_name'] ?? 'N/A' }}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-4"><strong>Location:</strong></div>
                                        <div class="col-8">{{ $tenant->plant_details['plant_location'] ?? 'N/A' }}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-4"><strong>Contact:</strong></div>
                                        <div class="col-8">{{ $tenant->plant_details['plant_contact_name'] ?? 'N/A' }}</div>
                                    </div>
                                @else
                                    <p class="text-muted">No plant details configured.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Warehouse Configuration -->
                    <div class="col-md-6 mb-4">
                        <div class="card info-card">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0"><i class="fas fa-warehouse me-2"></i>Warehouse Configuration</h5>
                            </div>
                            <div class="card-body">
                                @if($tenant->warehouse_config)
                                    <div class="row mb-3">
                                        <div class="col-4"><strong>Warehouse Code:</strong></div>
                                        <div class="col-8">{{ $tenant->warehouse_config['warehouse_code'] ?? 'N/A' }}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-4"><strong>Description:</strong></div>
                                        <div class="col-8">{{ $tenant->warehouse_config['warehouse_name'] ?? 'N/A' }}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-4"><strong>Storage Type:</strong></div>
                                        <div class="col-8">{{ $tenant->warehouse_config['storage_type'] ?? 'N/A' }}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-4"><strong>Strategy:</strong></div>
                                        <div class="col-8">{{ $tenant->warehouse_config['default_strategy'] ?? 'N/A' }}</div>
                                    </div>
                                @else
                                    <p class="text-muted">No warehouse configuration found.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Bin Configuration -->
                    <div class="col-md-6 mb-4">
                        <div class="card info-card">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fas fa-boxes me-2"></i>Bin Configuration</h5>
                            </div>
                            <div class="card-body">
                                @if($tenant->bin_config)
                                    <div class="row mb-3">
                                        <div class="col-4"><strong>Total Bins:</strong></div>
                                        <div class="col-8">{{ $tenant->bin_config['bin_count'] ?? 'N/A' }}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-4"><strong>Max Volume:</strong></div>
                                        <div class="col-8">{{ $tenant->bin_config['bin_volume'] ?? 'N/A' }} m³</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-4"><strong>Reorder Levels:</strong></div>
                                        <div class="col-8">{{ $tenant->bin_config['reorder_levels'] ?? 'N/A' }}</div>
                                    </div>
                                @else
                                    <p class="text-muted">No bin configuration found.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Barcode Settings -->
                    <div class="col-md-6 mb-4">
                        <div class="card info-card">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0"><i class="fas fa-barcode me-2"></i>Barcode Settings</h5>
                            </div>
                            <div class="card-body">
                                @if($tenant->barcode_settings)
                                    <div class="row mb-3">
                                        <div class="col-4"><strong>Bin Prefix:</strong></div>
                                        <div class="col-8">{{ $tenant->barcode_settings['barcode_bin_prefix'] ?? 'N/A' }}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-4"><strong>Shipment Prefix:</strong></div>
                                        <div class="col-8">{{ $tenant->barcode_settings['barcode_shipment_prefix'] ?? 'N/A' }}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-4"><strong>Barcode Length:</strong></div>
                                        <div class="col-8">{{ $tenant->barcode_settings['barcode_length'] ?? 'N/A' }}</div>
                                    </div>
                                @else
                                    <p class="text-muted">No barcode settings found.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Integration Settings -->
                    <div class="col-md-6 mb-4">
                        <div class="card info-card">
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0"><i class="fas fa-plug me-2"></i>Integration Settings</h5>
                            </div>
                            <div class="card-body">
                                @if($tenant->integration_settings)
                                    <div class="row mb-3">
                                        <div class="col-4"><strong>SAP Endpoint:</strong></div>
                                        <div class="col-8">{{ $tenant->integration_settings['sap_endpoint'] ?? 'N/A' }}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-4"><strong>WhatsApp:</strong></div>
                                        <div class="col-8">
                                            <span class="badge bg-{{ $tenant->integration_settings['whatsapp_enabled'] ? 'success' : 'secondary' }}">
                                                {{ $tenant->integration_settings['whatsapp_enabled'] ? 'Enabled' : 'Disabled' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-4"><strong>AI Provider:</strong></div>
                                        <div class="col-8">{{ ucfirst($tenant->integration_settings['ai_provider'] ?? 'None') }}</div>
                                    </div>
                                @else
                                    <p class="text-muted">No integration settings found.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Roles & Permissions -->
                    @if($tenant->roles_permissions && !empty($tenant->roles_permissions['roles_json']))
                    <div class="col-12 mb-4">
                        <div class="card info-card">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0"><i class="fas fa-users-cog me-2"></i>Roles & Permissions</h5>
                            </div>
                            <div class="card-body">
                                <pre class="bg-light p-3 rounded"><code>{{ $tenant->roles_permissions['roles_json'] }}</code></pre>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
