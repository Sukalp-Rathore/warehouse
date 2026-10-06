@extends('layout')

@section('content')
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

<div class="main-content app-content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-10">
                <!-- Header -->
                <div class="text-center mb-4">
                    <h1 class="display-4 fw-bold text-primary">
                        <i class="fas fa-building me-3"></i>Tenant Onboarding
                    </h1>
                    <p class="lead text-muted">Configure your warehouse management system settings</p>
                </div>

                <!-- Alert Messages -->
                <div id="alertContainer"></div>

                <!-- Main Form -->
                <form id="tenantSetupForm" class="needs-validation" novalidate>
                    @csrf

                    {{-- TENANT INFO --}}
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-dark text-white fw-semibold">
                            <i class="fas fa-info-circle me-2"></i>Tenant Information
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Tenant / Company Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="tenant_name" required>
                                    <div class="invalid-feedback">Please provide a valid company name.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Contact Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="tenant_email" required>
                                    <div class="invalid-feedback">Please provide a valid email address.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Timezone <span class="text-danger">*</span></label>
                                    <select class="form-select" name="timezone" required>
                                        <option value="">Select Timezone</option>
                                        <option value="Asia/Kolkata">Asia/Kolkata</option>
                                        <option value="Asia/Dubai">Asia/Dubai</option>
                                        <option value="Europe/London">Europe/London</option>
                                        <option value="America/New_York">America/New_York</option>
                                        <option value="America/Los_Angeles">America/Los_Angeles</option>
                                    </select>
                                    <div class="invalid-feedback">Please select a timezone.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ADMIN USER CREATION --}}
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-success text-white fw-semibold">
                            <i class="fas fa-user-shield me-2"></i>Admin User Account
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Important:</strong> This will be the admin account for this tenant. The admin will use these credentials to log into the system.
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Admin Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="admin_name" placeholder="John Doe" required>
                                    <div class="invalid-feedback">Please provide the admin's full name.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Admin Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="admin_email" placeholder="admin@company.com" required>
                                    <div class="invalid-feedback">Please provide a valid admin email address.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Admin Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" name="admin_password" placeholder="Enter secure password" minlength="8" required>
                                    <div class="invalid-feedback">Password must be at least 8 characters long.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" name="admin_password_confirmation" placeholder="Confirm password" minlength="8" required>
                                    <div class="invalid-feedback">Passwords must match.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-light fw-semibold">
                            <i class="fas fa-rocket me-2"></i>Next Steps
                        </div>
                        <div class="card-body">
                            <p class="mb-2">Once this form is submitted we will:</p>
                            <ul class="mb-3">
                                <li>Provision an isolated database for this tenant</li>
                                <li>Create the admin user with the credentials above</li>
                                <li>Prompt the admin to complete plant, warehouse, bin, and integration setup after login</li>
                            </ul>
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Detailed operational configuration can be completed later from the tenant dashboard. This form only collects the essentials to get started.
                            </div>
                        </div>
                    </div>

                    {{-- SAVE --}}
                    <div class="text-center mb-5">
                        <button type="submit" class="btn btn-lg btn-primary px-5" id="submitBtn">
                            <i class="fas fa-save me-2"></i>Save Configuration
                        </button>
                        <div class="loading mt-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Setting up your tenant...</p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('tenantSetupForm');
            const submitBtn = document.getElementById('submitBtn');
            const loading = document.querySelector('.loading');
            const alertContainer = document.getElementById('alertContainer');

            // Form validation
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                
                // Custom password confirmation validation
                const password = document.querySelector('input[name="admin_password"]').value;
                const confirmPassword = document.querySelector('input[name="admin_password_confirmation"]').value;
                
                if (password !== confirmPassword) {
                    const confirmPasswordField = document.querySelector('input[name="admin_password_confirmation"]');
                    confirmPasswordField.setCustomValidity('Passwords do not match');
                    confirmPasswordField.reportValidity();
                    return;
                } else {
                    document.querySelector('input[name="admin_password_confirmation"]').setCustomValidity('');
                }
                
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

                // Submit form
                fetch('{{ route("tenant.onboard") }}', {
                    method: 'POST',
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
                            window.location.href = data.redirect_url || '/tenant/list';
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

            // Password confirmation real-time validation
            const passwordInput = document.querySelector('input[name="admin_password"]');
            const confirmPasswordInput = document.querySelector('input[name="admin_password_confirmation"]');
            
            confirmPasswordInput.addEventListener('input', function() {
                if (this.value !== passwordInput.value) {
                    this.setCustomValidity('Passwords do not match');
                } else {
                    this.setCustomValidity('');
                }
            });

            // Password strength indicator
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                const strength = getPasswordStrength(password);
                const strengthText = document.getElementById('password-strength') || createPasswordStrengthIndicator();
                
                strengthText.textContent = strength.text;
                strengthText.className = `form-text ${strength.class}`;
            });

            function createPasswordStrengthIndicator() {
                const strengthDiv = document.createElement('div');
                strengthDiv.id = 'password-strength';
                strengthDiv.className = 'form-text';
                passwordInput.parentNode.appendChild(strengthDiv);
                return strengthDiv;
            }

            function getPasswordStrength(password) {
                if (password.length < 8) {
                    return { text: 'Password must be at least 8 characters long', class: 'text-danger' };
                }
                
                let score = 0;
                if (password.length >= 8) score++;
                if (/[a-z]/.test(password)) score++;
                if (/[A-Z]/.test(password)) score++;
                if (/[0-9]/.test(password)) score++;
                if (/[^A-Za-z0-9]/.test(password)) score++;
                
                if (score < 3) {
                    return { text: 'Weak password', class: 'text-danger' };
                } else if (score < 4) {
                    return { text: 'Medium strength password', class: 'text-warning' };
                } else {
                    return { text: 'Strong password', class: 'text-success' };
                }
            }
        });
    </script>
@endsection
