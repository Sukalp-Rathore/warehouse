@extends('layout')

@section('title', 'Create User')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Create New User</h4>
                        <a href="{{ route('tenant.users.index') }}" class="btn btn-outline-secondary">
                            <i class="ri-arrow-left-line me-1"></i> Back to Users
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('tenant.users.store') }}">
                        @csrf
                        
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Basic Information</h5>
                                
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('username') is-invalid @enderror" 
                                           id="username" name="username" value="{{ old('username') }}" required>
                                    @error('username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="mobile" class="form-label">Mobile</label>
                                    <input type="text" class="form-control @error('mobile') is-invalid @enderror" 
                                           id="mobile" name="mobile" value="{{ old('mobile') }}">
                                    @error('mobile')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           id="password" name="password" required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="password_confirmation" 
                                           name="password_confirmation" required>
                                </div>
                            </div>

                            <!-- Role and Permissions -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Role & Permissions</h5>
                                
                                <div class="mb-3">
                                    <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
                                    <select class="form-select @error('role_id') is-invalid @enderror" 
                                            id="role_id" name="role_id" required>
                                        <option value="">Select Role</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->_id }}" {{ old('role_id') == $role->_id ? 'selected' : '' }}>
                                                {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Custom Permissions</label>
                                    <div class="row">
                                        @foreach($availablePermissions as $permission => $label)
                                            <div class="col-md-6 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="permissions[]" value="{{ $permission }}" 
                                                           id="perm_{{ $permission }}"
                                                           {{ in_array($permission, old('permissions', [])) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="perm_{{ $permission }}">
                                                        {{ $label }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Plant Access</label>
                                    <div class="row">
                                        @foreach($plants as $plant)
                                            <div class="col-md-6 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="plant_access[]" value="{{ $plant->_id }}" 
                                                           id="plant_{{ $plant->_id }}"
                                                           {{ in_array($plant->_id, old('plant_access', [])) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="plant_{{ $plant->_id }}">
                                                        {{ $plant->plant_name }} ({{ $plant->plant_code }})
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
                                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('tenant.users.index') }}" class="btn btn-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary">Create User</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role_id');
    const permissionCheckboxes = document.querySelectorAll('input[name="permissions[]"]');
    
    // Function to update permissions based on selected role
    function updatePermissionsForRole(roleId) {
        console.log('updatePermissionsForRole called with roleId:', roleId);
        
        if (!roleId) {
            console.log('No role ID provided, clearing permissions');
            // If no role selected, uncheck all permissions
            permissionCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            return;
        }
        
        console.log('Making AJAX request to:', `{{ url('/tenant/roles') }}/${roleId}/permissions`);
        
        // Show loading state
        showPermissionUpdateMessage('Loading role permissions...', 'info');
        
        // Fetch role permissions via AJAX
        fetch(`{{ url('/tenant/roles') }}/${roleId}/permissions`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Role permissions data:', data);
                if (data.success) {
                    // Uncheck all permissions first
                    permissionCheckboxes.forEach(checkbox => {
                        checkbox.checked = false;
                    });
                    
                    // Check permissions that match the role
                    data.permissions.forEach(permission => {
                        const checkbox = document.querySelector(`input[name="permissions[]"][value="${permission}"]`);
                        if (checkbox) {
                            checkbox.checked = true;
                        }
                    });
                    
                    // Show success message
                    showPermissionUpdateMessage('Role permissions loaded successfully!', 'success');
                } else {
                    console.error('API returned success: false', data);
                    showPermissionUpdateMessage('Failed to load role permissions: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error loading role permissions:', error);
                showPermissionUpdateMessage('Error loading role permissions: ' + error.message, 'error');
            });
    }
    
    // Function to show update message
    function showPermissionUpdateMessage(message, type) {
        // Remove existing message
        const existingMessage = document.getElementById('permission-update-message');
        if (existingMessage) {
            existingMessage.remove();
        }
        
        // Create new message
        const messageDiv = document.createElement('div');
        messageDiv.id = 'permission-update-message';
        messageDiv.className = `alert alert-${type === 'success' ? 'info' : 'warning'} alert-dismissible fade show mt-2`;
        messageDiv.innerHTML = `
            <small>${message}</small>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Insert after permissions container - find by looking for the permissions checkboxes container
        const permissionsContainer = document.querySelector('input[name="permissions[]"]').closest('.mb-3');
        if (permissionsContainer) {
            permissionsContainer.parentNode.insertBefore(messageDiv, permissionsContainer.nextSibling);
        } else {
            // Fallback: append to form
            document.querySelector('form').appendChild(messageDiv);
        }
        
        // Auto-hide after 3 seconds
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.remove();
            }
        }, 3000);
    }
    
    // Listen for role changes
    roleSelect.addEventListener('change', function() {
        console.log('Role changed to:', this.value);
        console.log('Available options:', Array.from(this.options).map(opt => ({value: opt.value, text: opt.text})));
        updatePermissionsForRole(this.value);
    });
    
    // Initialize on page load if role is already selected
    if (roleSelect.value) {
        updatePermissionsForRole(roleSelect.value);
    }
    
    // Add visual feedback for permission changes
    permissionCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            // Add a subtle animation when manually changing permissions
            this.parentElement.style.backgroundColor = '#f8f9fa';
            setTimeout(() => {
                this.parentElement.style.backgroundColor = '';
            }, 200);
        });
    });
});
</script>
@endsection
