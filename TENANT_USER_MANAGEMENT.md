# Tenant User Management System

This document describes the tenant user management functionality implemented for the multi-tenant WMS (Warehouse Management System).

## Overview

The tenant user management system allows tenant administrators to manage users, roles, and plants within their organization. Each tenant admin can create users, assign roles, set permissions, and control plant access for their organization members.

## Features

### 1. User Management
- **Create Users**: Add new users with username, email, password, mobile
- **Role Assignment**: Assign users to predefined roles
- **Custom Permissions**: Set granular permissions beyond role-based permissions
- **Plant Access**: Control which plants users can access
- **User Status**: Activate/deactivate users
- **Search & Filter**: Find users by name, email, role, or status

### 2. Role Management
- **Predefined Roles**: Admin, Manager, Operator, Viewer
- **Custom Permissions**: Define specific permissions for each role
- **Role Status**: Enable/disable roles
- **User Assignment**: See which users are assigned to each role

### 3. Plant Management
- **Plant Creation**: Add new plants with location and contact details
- **Plant Access Control**: Assign users to specific plants
- **Plant Status**: Enable/disable plants

## Database Structure

### Collections

#### Users Collection
```javascript
{
    "_id": ObjectId,
    "username": "john_doe",
    "email": "john@company.com",
    "password": "hashed_password",
    "mobile": "+91-9876543210",
    "role_id": ObjectId("role_id"),
    "permissions": ["warehouse.view", "inventory.create"],
    "plant_access": [ObjectId("plant1"), ObjectId("plant2")],
    "status": "active",
    "name": "John Doe",
    "created_by": ObjectId("admin_user_id"),
    "created_at": ISODate,
    "updated_at": ISODate
}
```

#### Roles Collection
```javascript
{
    "_id": ObjectId,
    "name": "Manager",
    "description": "Warehouse Manager Role",
    "permissions": ["warehouse.view", "inventory.manage", "reports.view"],
    "is_active": true,
    "created_by": ObjectId("admin_user_id"),
    "created_at": ISODate,
    "updated_at": ISODate
}
```

#### Plants Collection
```javascript
{
    "_id": ObjectId,
    "plant_code": "PLT001",
    "plant_name": "Main Warehouse",
    "location": "Mumbai",
    "address": "123 Main Street",
    "contact_person": "John Smith",
    "contact_phone": "+91-9876543210",
    "contact_email": "contact@company.com",
    "is_active": true,
    "created_by": ObjectId("admin_user_id"),
    "created_at": ISODate,
    "updated_at": ISODate
}
```

## Models

### User Model (`App\Models\Client\User`)
- Enhanced with new fields: username, mobile, role_id, plant_access
- Relationships with Role and Plant models
- Permission checking methods
- Plant access management

### Role Model (`App\Models\Client\Role`)
- Role management with permissions
- User relationships
- Permission management methods

### Plant Model (`App\Models\Client\Plant`)
- Plant information management
- User access relationships
- Active/inactive status

## Controllers

### TenantUserController
- `index()` - List users with search and filter
- `create()` - Show create user form
- `store()` - Create new user
- `show()` - Display user details
- `edit()` - Show edit user form
- `update()` - Update user
- `destroy()` - Delete user
- `toggleStatus()` - Activate/deactivate user

### TenantRoleController
- Full CRUD operations for roles
- Permission management
- Role status management

### TenantPlantController
- Full CRUD operations for plants
- Contact information management
- Plant status management

## Middleware

### TenantAdminOnly
- Ensures only tenant admins can access user management
- Checks authentication and tenant type
- Validates admin role

## Permission System

### Available Permissions
- `warehouse.view` - View Warehouse
- `warehouse.create` - Create Warehouse
- `warehouse.edit` - Edit Warehouse
- `warehouse.delete` - Delete Warehouse
- `inventory.view` - View Inventory
- `inventory.create` - Create Inventory
- `inventory.edit` - Edit Inventory
- `inventory.delete` - Delete Inventory
- `reports.view` - View Reports
- `reports.export` - Export Reports
- `users.view` - View Users
- `users.create` - Create Users
- `users.edit` - Edit Users
- `users.delete` - Delete Users
- `settings.view` - View Settings
- `settings.edit` - Edit Settings

### Permission Helper (`App\Helpers\PermissionHelper`)
- `hasPermission($user, $permission)` - Check specific permission
- `hasPlantAccess($user, $plantId)` - Check plant access
- `hasAnyPermission($user, $permissions)` - Check any of multiple permissions
- `hasAllPermissions($user, $permissions)` - Check all permissions
- `getAllPermissions($user)` - Get all user permissions
- `canAccess($user, $resource, $action)` - Check resource access

## Routes

### User Management Routes
```php
Route::middleware(['custom_auth', 'tenant_admin_only'])->prefix('tenant')->group(function () {
    // User Management
    Route::get('/users', [TenantUserController::class, 'index'])->name('tenant.users.index');
    Route::get('/users/create', [TenantUserController::class, 'create'])->name('tenant.users.create');
    Route::post('/users', [TenantUserController::class, 'store'])->name('tenant.users.store');
    Route::get('/users/{id}', [TenantUserController::class, 'show'])->name('tenant.users.show');
    Route::get('/users/{id}/edit', [TenantUserController::class, 'edit'])->name('tenant.users.edit');
    Route::put('/users/{id}', [TenantUserController::class, 'update'])->name('tenant.users.update');
    Route::delete('/users/{id}', [TenantUserController::class, 'destroy'])->name('tenant.users.destroy');
    Route::patch('/users/{id}/toggle-status', [TenantUserController::class, 'toggleStatus'])->name('tenant.users.toggle-status');
    
    // Role Management
    Route::resource('roles', TenantRoleController::class);
    
    // Plant Management  
    Route::resource('plants', TenantPlantController::class);
});
```

## Views

### User Management Views
- `tenant/users/index.blade.php` - User list with search and filters
- `tenant/users/create.blade.php` - Create user form
- `tenant/users/edit.blade.php` - Edit user form
- `tenant/users/show.blade.php` - User details view

### Role Management Views
- `tenant/roles/index.blade.php` - Role list
- `tenant/roles/create.blade.php` - Create role form
- `tenant/roles/edit.blade.php` - Edit role form
- `tenant/roles/show.blade.php` - Role details view

### Plant Management Views
- `tenant/plants/index.blade.php` - Plant list
- `tenant/plants/create.blade.php` - Create plant form
- `tenant/plants/edit.blade.php` - Edit plant form
- `tenant/plants/show.blade.php` - Plant details view

## Navigation

The system automatically adds navigation menu items for tenant admins:
- User Management
- Role Management
- Plant Management

## Security Features

- **Access Control**: Only tenant admins can access user management
- **CSRF Protection**: All forms are protected with CSRF tokens
- **Input Validation**: Comprehensive validation for all inputs
- **Password Security**: Passwords are hashed using Laravel's Hash facade
- **Permission Checks**: Granular permission system
- **Self-Protection**: Users cannot delete or deactivate themselves

## Default Data

When a new tenant is created, the system automatically creates:
- **Default Roles**: Admin, Manager, Operator, Viewer
- **Default Plant**: Main warehouse plant
- **Admin User**: System administrator with full access

## Commands

### Seed Tenant Defaults
```bash
php artisan tenant:seed-defaults {client_id}
```
Creates default roles, plants, and admin user for a tenant.

### Test User Management
```bash
php artisan tenant:test-user-management {client_id}
```
Tests the user management functionality for a specific tenant.

## Usage Examples

### Creating a User
1. Navigate to User Management
2. Click "Add New User"
3. Fill in user details
4. Select role and permissions
5. Choose plant access
6. Set status and save

### Managing Permissions
1. Go to Role Management
2. Create or edit a role
3. Select permissions for the role
4. Users assigned to this role will inherit these permissions

### Assigning Plant Access
1. Create or edit a user
2. Select which plants the user can access
3. Users can only access data from their assigned plants

## Future Enhancements

- **Bulk User Operations**: Import/export users
- **Advanced Permissions**: Resource-specific permissions
- **Audit Logging**: Track user management actions
- **User Groups**: Group users for easier management
- **API Access**: REST API for user management
- **Advanced Search**: More sophisticated search and filtering
- **User Templates**: Predefined user configurations

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/tenant/users` | List users |
| GET | `/tenant/users/create` | Show create form |
| POST | `/tenant/users` | Create user |
| GET | `/tenant/users/{id}` | Show user details |
| GET | `/tenant/users/{id}/edit` | Show edit form |
| PUT | `/tenant/users/{id}` | Update user |
| DELETE | `/tenant/users/{id}` | Delete user |
| PATCH | `/tenant/users/{id}/toggle-status` | Toggle user status |
| GET | `/tenant/roles` | List roles |
| POST | `/tenant/roles` | Create role |
| GET | `/tenant/roles/{id}` | Show role details |
| PUT | `/tenant/roles/{id}` | Update role |
| DELETE | `/tenant/roles/{id}` | Delete role |
| GET | `/tenant/plants` | List plants |
| POST | `/tenant/plants` | Create plant |
| GET | `/tenant/plants/{id}` | Show plant details |
| PUT | `/tenant/plants/{id}` | Update plant |
| DELETE | `/tenant/plants/{id}` | Delete plant |

## Configuration

The system uses the existing multi-tenant architecture with MongoDB. Ensure proper configuration in your `.env` file:

```env
DB_CONNECTION=mongodb
DB_HOST=127.0.0.1
DB_PORT=27017
DB_DATABASE=wms_master
```

## Dependencies

- Laravel Framework
- MongoDB Laravel Package
- Bootstrap 5 (for UI)
- Remix Icons (for icons)
