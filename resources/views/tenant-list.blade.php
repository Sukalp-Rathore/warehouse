<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Management - WMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .card {
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-2px);
        }
        .status-badge {
            font-size: 0.8rem;
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
                            <i class="fas fa-building me-2"></i>Tenant Management
                        </h1>
                        <p class="text-muted">Manage all your warehouse tenants</p>
                    </div>
                    <a href="{{ route('tenant.onboard') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus me-2"></i>Add New Tenant
                    </a>
                </div>

                <!-- Alert Messages -->
                <div id="alertContainer"></div>

                <!-- Tenants Grid -->
                <div class="row">
                    @forelse($tenants as $tenant)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold">{{ $tenant->tenant_name }}</h6>
                                <span class="badge bg-{{ $tenant->status === 'active' ? 'success' : 'secondary' }} status-badge">
                                    {{ ucfirst($tenant->status) }}
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <small class="text-muted">Client ID:</small>
                                    <div class="fw-bold">{{ $tenant->client_id }}</div>
                                </div>
                                
                                <div class="mb-3">
                                    <small class="text-muted">Email:</small>
                                    <div>{{ $tenant->tenant_email }}</div>
                                </div>
                                
                                <div class="mb-3">
                                    <small class="text-muted">Timezone:</small>
                                    <div>{{ $tenant->timezone }}</div>
                                </div>
                                
                                @if($tenant->plant_details)
                                <div class="mb-3">
                                    <small class="text-muted">Plant:</small>
                                    <div>{{ $tenant->plant_details['plant_name'] ?? 'N/A' }}</div>
                                </div>
                                @endif
                                
                                @if($tenant->warehouse_config)
                                <div class="mb-3">
                                    <small class="text-muted">Warehouse:</small>
                                    <div>{{ $tenant->warehouse_config['warehouse_code'] ?? 'N/A' }}</div>
                                </div>
                                @endif
                            </div>
                            <div class="card-footer bg-transparent">
                                <div class="btn-group w-100" role="group">
                                    <a href="{{ route('tenant.show', $tenant->client_id) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i>View
                                    </a>
                                    <a href="{{ route('tenant.edit', $tenant->client_id) }}" class="btn btn-outline-warning btn-sm">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </a>
                                    <button class="btn btn-outline-danger btn-sm" onclick="deleteTenant('{{ $tenant->client_id }}')">
                                        <i class="fas fa-trash me-1"></i>Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-building fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">No Tenants Found</h4>
                            <p class="text-muted">Get started by adding your first tenant.</p>
                            <a href="{{ route('tenant.onboard') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Add First Tenant
                            </a>
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteTenant(clientId) {
            if (confirm('Are you sure you want to delete this tenant? This action cannot be undone.')) {
                fetch(`/tenant/${clientId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        showAlert('danger', data.message);
                    }
                })
                .catch(error => {
                    showAlert('danger', 'An error occurred: ' + error.message);
                });
            }
        }

        function showAlert(type, message) {
            const alertContainer = document.getElementById('alertContainer');
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alertContainer.appendChild(alertDiv);

            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }
    </script>
</body>
</html>
