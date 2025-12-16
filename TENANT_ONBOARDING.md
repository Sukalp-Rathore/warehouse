# Tenant Onboarding System

This document describes the tenant onboarding functionality implemented for the multi-tenant WMS (Warehouse Management System).

## Overview

The tenant onboarding system allows system administrators to create and configure new tenants with their specific warehouse management settings. Each tenant gets their own database and configuration stored in the `tenant_settings` collection.

## Features

### 1. Tenant Onboarding Form
- **Location**: `/tenant/onboard`
- **Purpose**: Quickly collect the minimum data required to spin up a new tenant
- **Form Sections**:
  - Tenant Information (Company name, contact email, timezone)
  - Admin User Account (Name, email, password)
- All advanced configuration (plants, warehouses, bins, integrations, roles, etc.) is deferred to the tenant admin after their first login.

### 2. Tenant Management
- **List View**: `/tenant/list` - View all tenants
- **Details View**: `/tenant/{clientId}` - View tenant configuration
- **Edit View**: `/tenant/{clientId}/edit` - Update tenant settings
- **Delete**: Remove tenant and associated data

### 3. Database Structure

#### TenantSettings Model
```php
// Collection: tenant_settings
{
    client_id: "CLT_XXXXXXXX",
    tenant_name: "Company Name",
    tenant_email: "admin@company.com",
    timezone: "Asia/Kolkata",
    plant_details: { ... },
    warehouse_config: { ... },
    bin_config: { ... },
    item_vendor_rules: { ... },
    barcode_settings: { ... },
    roles_permissions: { ... },
    reorder_alert_rules: { ... },
    integration_settings: { ... },
    status: "active"
}
```

#### Client Model
```php
// Collection: clients
{
    client_id: "CLT_XXXXXXXX",
    name: "Company Name",
    email: "admin@company.com",
    database_name: "wms_company_name_timestamp",
    status: "active",
    subscription_plan: "basic",
    max_warehouses: 5,
    max_users: 10
}
```

## Implementation Details

### Controllers
- `TenantOnboardingController`: Handles all tenant management operations
- Methods: `index()`, `store()`, `list()`, `show()`, `edit()`, `update()`, `destroy()`

### Services
- `TenantConfigurationService`: Manages tenant database creation and configuration
- `TenantService`: Handles tenant connection management

### Models
- `TenantSettings`: Stores tenant configuration
- `Client`: Stores basic client information

### Views
- `tenant-onboarding.blade.php`: Main onboarding form
- `tenant-list.blade.php`: Tenant listing page
- `tenant-details.blade.php`: Tenant details view
- `tenant-edit.blade.php`: Tenant editing form

## Database Creation Process

When a new tenant is created:

1. **Generate unique identifiers**:
   - Client ID: `CLT_` + 8 random characters
   - Database name: `wms_` + slugified company name + timestamp

2. **Create master records**:
   - Insert into `clients` collection
   - Insert into `tenant_settings` collection

3. **Initialize tenant database**:
   - Create new MongoDB database
   - Set up default collections (users, warehouses, plants, settings, roles, bins, zones)
   - Create default admin user
   - Create default warehouse and bin data

## Usage

### Creating a New Tenant
1. Navigate to `/tenant/onboard`
2. Fill out the comprehensive form
3. Submit to create tenant and initialize database
4. Redirect to tenant list

### Managing Existing Tenants
1. Navigate to `/tenant/list`
2. View, edit, or delete tenants as needed
3. Each tenant maintains its own isolated database

## Security Considerations

- All routes are protected by `custom_auth` middleware
- CSRF protection on all forms
- Input validation and sanitization
- Database isolation per tenant

## Future Enhancements

- Tenant-specific user management
- Advanced role-based permissions
- Tenant resource monitoring
- Automated tenant provisioning
- Tenant data export/import
- Multi-tenant analytics dashboard

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/tenant/onboard` | Show onboarding form |
| POST | `/tenant/onboard` | Create new tenant |
| GET | `/tenant/list` | List all tenants |
| GET | `/tenant/{clientId}` | Show tenant details |
| GET | `/tenant/{clientId}/edit` | Show edit form |
| PUT | `/tenant/{clientId}` | Update tenant |
| DELETE | `/tenant/{clientId}` | Delete tenant |

## Configuration

The system uses MongoDB for both master and tenant databases. Ensure proper MongoDB configuration in your `.env` file:

```env
DB_CONNECTION=mongodb
DB_HOST=127.0.0.1
DB_PORT=27017
DB_DATABASE=wms_master
DB_USERNAME=
DB_PASSWORD=
```
