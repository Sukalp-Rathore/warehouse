# Warehouse Management System (WMS)

A comprehensive multi-tenant Warehouse Management System built with Laravel 12, MongoDB, and modern web technologies.

## 📋 Table of Contents

- [Overview](#overview)
- [Tech Stack](#tech-stack)
- [Architecture](#architecture)
- [Project Structure](#project-structure)
- [Database Schema](#database-schema)
- [Authentication & Multi-Tenancy](#authentication--multi-tenancy)
- [API Routes & Endpoints](#api-routes--endpoints)
- [Frontend Structure](#frontend-structure)
- [Key Features](#key-features)
- [Setup Instructions](#setup-instructions)
- [Development Workflow](#development-workflow)
- [Code Style & Conventions](#code-style--conventions)

---

## 🎯 Overview

This is a **multi-tenant Warehouse Management System** where:
- **Master Admin** manages multiple tenant organizations
- **Tenant Admins** manage their organization's warehouses, inventory, users, and operations
- Each tenant has an **isolated database** with their own data
- Support for **Plants → Warehouses → Storage Types → Sections → Bins** hierarchy

### Key Capabilities

- ✅ Multi-tenant architecture with isolated databases
- ✅ User & Role Management (RBAC with granular permissions)
- ✅ Plant & Warehouse Management
- ✅ Storage Type Management (High Rack, Cold Room)
- ✅ Section Management (for High Rack storage)
- ✅ Bin Management with auto-generation
- ✅ Vendor Management
- ✅ Item Master with Import/Export (CSV)
- ✅ Item Category Management (hierarchical)

---

## 🛠 Tech Stack

### Backend
- **Framework**: Laravel 12
- **PHP Version**: 8.2+
- **Database**: MongoDB (using `mongodb/laravel-mongodb` package)
- **Authentication**: Custom multi-tenant authentication

### Frontend
- **Templating**: Blade Templates
- **CSS Framework**: Bootstrap 5
- **Icons**: Bootstrap Icons, Remix Icons, Feather Icons
- **JavaScript**: Vanilla JS (no framework dependency)
- **AJAX**: Fetch API for dynamic content loading

### Development Tools
- **Package Manager**: Composer (PHP), NPM (frontend assets)
- **Version Control**: Git
- **Server**: Apache/Nginx (XAMPP in development)

---

## 🏗 Architecture

### Multi-Tenancy Model

```
Master Database (MongoDB)
├── clients collection
│   ├── Tenant records (record_type: 'tenant')
│   └── User records (record_type: 'user')
└── tenant_settings collection

Tenant Database (per organization)
├── users collection
├── roles collection
├── plants collection
├── warehouses collection
├── storage_types collection
├── sections collection
├── bins collection
├── vendors collection
├── item_categories collection
└── items collection
```

### Authentication Flow

1. User enters email/password
2. System checks **master database** (`clients` collection):
   - First checks for `record_type: 'user'` (tenant users)
   - Falls back to `record_type: 'tenant'` (legacy tenant admins)
3. If found, retrieves `database_name` and connects to **tenant database**
4. Validates password against tenant `users` collection
5. Sets session with user data, tenant info, and permissions

### Session Structure

```php
session([
    'authenticated' => true,
    'tenant_type' => 'client', // or 'master'
    'client_id' => 'CLT_XXXXX',
    'database_name' => 'wms_company_xxxxx',
    'user' => [
        '_id' => ObjectId,
        'name' => 'John Doe',
        'email' => 'john@company.com',
        'role' => 'admin',
        'permissions' => ['all'],
        // ... other user fields
    ]
])
```

---

## 📁 Project Structure

```
wms/
├── app/
│   ├── Console/Commands/          # Artisan commands
│   ├── Helpers/                   # Helper functions
│   ├── Http/
│   │   ├── Controllers/           # Application controllers
│   │   │   ├── DashboardController.php
│   │   │   ├── WarehouseController.php
│   │   │   ├── BinController.php
│   │   │   ├── VendorController.php
│   │   │   ├── ItemController.php
│   │   │   ├── TenantUserController.php
│   │   │   ├── TenantRoleController.php
│   │   │   ├── TenantPlantController.php
│   │   │   ├── TenantOnboardingController.php
│   │   │   └── ...
│   │   └── Middleware/            # Custom middleware
│   │       ├── CustomAuth.php
│   │       ├── TenantAdminOnly.php
│   │       ├── TenantConfigured.php
│   │       └── MasterAdminOnly.php
│   ├── Mail/                      # Email classes
│   ├── Models/
│   │   ├── Master/                # Master DB models
│   │   │   ├── Client.php
│   │   │   └── TenantSettings.php
│   │   └── Client/                # Tenant DB models
│   │       ├── User.php
│   │       ├── Role.php
│   │       ├── Plant.php
│   │       ├── Warehouse.php
│   │       ├── StorageType.php
│   │       ├── Section.php
│   │       ├── Bin.php
│   │       ├── Vendor.php
│   │       ├── ItemCategory.php
│   │       └── Item.php
│   ├── Providers/                 # Service providers
│   └── Services/                  # Business logic services
│       ├── MultiTenantAuthService.php
│       ├── TenantService.php
│       ├── TenantConfigurationService.php
│       ├── BinGenerationService.php
│       └── ItemImportExportService.php
├── resources/
│   ├── views/
│   │   ├── layout.blade.php       # Main layout template
│   │   ├── dashboard.blade.php
│   │   ├── tenant/
│   │   │   ├── users/             # User management views
│   │   │   ├── roles/             # Role management views
│   │   │   ├── plants/            # Plant management views
│   │   │   ├── warehouses/        # Warehouse management views
│   │   │   ├── storage-types/     # Storage type views
│   │   │   ├── sections/          # Section management views
│   │   │   ├── bins/              # Bin management views
│   │   │   ├── vendors/           # Vendor management views
│   │   │   ├── categories/        # Category management views
│   │   │   ├── items/             # Item master views
│   │   │   ├── configure.blade.php
│   │   │   └── ...
│   │   └── ...
│   ├── css/                       # Custom CSS
│   └── js/                        # Custom JavaScript
├── routes/
│   ├── web.php                    # Web routes
│   └── api.php                    # API routes (if needed)
├── config/
│   ├── database.php               # Database configurations
│   └── ...
├── public/                        # Public assets
└── storage/                       # File storage
```

---

## 🗄 Database Schema

### Master Database Collections

#### `clients`
```javascript
{
  "_id": ObjectId,
  "client_id": "CLT_XXXXX",           // Unique tenant identifier
  "name": "Company Name",
  "email": "admin@company.com",
  "hashed_email": "sha256_hash",
  "database_name": "wms_company_xxxxx",
  "status": "active|inactive",
  "record_type": "tenant|user",       // Distinguishes tenant vs user records
  "user_id": ObjectId,                // Reference to tenant user (if record_type='user')
  "created_at": ISODate,
  "updated_at": ISODate
}
```

#### `tenant_settings`
```javascript
{
  "_id": ObjectId,
  "client_id": "CLT_XXXXX",
  "timezone": "Asia/Kolkata",
  "configuration_completed": true|false,
  "plant_details": { ... },
  "warehouse_config": { ... },
  "bin_config": { ... },
  "barcode_settings": { ... },
  "created_at": ISODate,
  "updated_at": ISODate
}
```

### Tenant Database Collections

#### `users`
```javascript
{
  "_id": ObjectId,
  "username": "johndoe",
  "email": "john@company.com",
  "password": "hashed_password",
  "name": "John Doe",
  "mobile": "+1234567890",
  "role_id": ObjectId,                // Reference to roles collection
  "permissions": ["all"] | [...],     // Custom permissions array
  "plant_access": [ObjectId, ...],   // Array of plant IDs
  "status": "active|inactive",
  "created_by": ObjectId,
  "created_at": ISODate,
  "updated_at": ISODate
}
```

#### `roles`
```javascript
{
  "_id": ObjectId,
  "name": "Manager",
  "description": "Warehouse Manager Role",
  "permissions": ["warehouse.view", "inventory.edit", ...],
  "is_active": true,
  "created_by": ObjectId,
  "created_at": ISODate,
  "updated_at": ISODate
}
```

#### `plants`
```javascript
{
  "_id": ObjectId,
  "plant_code": "PLNT001",
  "plant_name": "Main Plant",
  "location": "City, State",
  "address": "...",
  "contact_person": "...",
  "contact_phone": "...",
  "contact_email": "...",
  "is_active": true,
  "created_at": ISODate,
  "updated_at": ISODate
}
```

#### `warehouses`
```javascript
{
  "_id": ObjectId,
  "warehouse_code": "WH01",
  "warehouse_name": "Main Warehouse",
  "plant_id": ObjectId,              // Reference to plants
  "plant_code": "PLNT001",
  "address": "...",
  "warehouse_type": "FG|RM|Cold Storage|Quarantine|Transit|Distribution",
  "status": "active|inactive",
  "storage_types": [ObjectId, ...],  // Array of storage_type IDs
  "capacity": 10000,
  "current_utilization": 0,
  "contact_person": "...",
  "is_active": true,
  "created_at": ISODate,
  "updated_at": ISODate
}
```

#### `storage_types`
```javascript
{
  "_id": ObjectId,
  "code": "HIGH_RACK|COLD_ROOM",
  "name": "High Rack|Cold Room",
  "description": "...",
  "warehouse_id": ObjectId,          // Per-warehouse storage types
  "status": "active",
  "is_active": true,
  "created_at": ISODate,
  "updated_at": ISODate
}
```

#### `sections` (Only for High Rack storage types)
```javascript
{
  "_id": ObjectId,
  "section_code": "R1",
  "section_name": "Rack Row 1",
  "storage_type_id": ObjectId,       // Must be High Rack
  "warehouse_id": ObjectId,
  "zone_code": "A|B|C|D",
  "description": "...",
  "status": "active",
  "is_active": true,
  "created_at": ISODate,
  "updated_at": ISODate
}
```

#### `bins`
```javascript
{
  "_id": ObjectId,
  "bin_code": "BIN-001",
  "bin_name": "Bin 1",
  "warehouse_id": ObjectId,
  "storage_type_id": ObjectId,
  "section_id": ObjectId,            // null for Cold Room
  "zone_code": "A",
  "coordinates": {                   // Optional
    "row": "A",
    "rack": "1",
    "level": "2",
    "position": "3"
  },
  "max_volume": 1.0,
  "max_weight": 100.0,
  "current_volume": 0.0,
  "current_weight": 0.0,
  "status": "available|occupied|reserved|maintenance",
  "is_active": true,
  "created_at": ISODate,
  "updated_at": ISODate
}
```

#### `vendors`
```javascript
{
  "_id": ObjectId,
  "vendor_code": "VEND-001",
  "vendor_name": "ABC Suppliers",
  "contact_person": "...",
  "contact_phone": "...",
  "contact_email": "...",
  "address": { ... },
  "vendor_type": "Manufacturer|Distributor|Retailer",
  "allow_prebarcode": true|false,
  "vendor_portal_enabled": true|false,
  "portal_login": "...",
  "portal_password": "hashed",
  "rating": 4.5,
  "status": "active",
  "is_active": true,
  "created_at": ISODate,
  "updated_at": ISODate
}
```

#### `item_categories`
```javascript
{
  "_id": ObjectId,
  "category_code": "ELEC",
  "category_name": "Electronics",
  "description": "...",
  "parent_id": ObjectId,            // null for root categories
  "is_active": true,
  "created_at": ISODate,
  "updated_at": ISODate
}
```

#### `items`
```javascript
{
  "_id": ObjectId,
  "item_code": "ITEM-001",
  "description": "Product Name",
  "short_description": "Short Name",
  "category": "Electronics",
  "category_id": ObjectId,
  "subcategory": "Components",
  "uom": "PCS",
  "weight": 0.5,
  "dimensions": {
    "length": 10,
    "width": 5,
    "height": 3,
    "unit": "cm"
  },
  "volume": 0.15,
  "preferred_storage_type_id": ObjectId,
  "preferred_bin_id": ObjectId,
  "vendor_id": ObjectId,
  "alternate_vendors": [ObjectId, ...],
  "minimum_order_quantity": 100,
  "reorder_level": 50,
  "reorder_quantity": 200,
  "unit_price": 10.50,
  "currency": "USD",
  "barcode": "...",
  "image_url": "/storage/items/...",
  "is_hazmat": false,
  "temperature_controlled": false,
  "is_active": true,
  "created_at": ISODate,
  "updated_at": ISODate
}
```

---

## 🔐 Authentication & Multi-Tenancy

### Middleware Stack

Routes are protected by middleware in this order:

1. **`web`** - Session handling, CSRF protection
2. **`custom_auth`** - Custom authentication check
3. **`tenant_admin_only`** - Ensures user is tenant admin (optional for some routes)
4. **`tenant_configured`** - Ensures tenant completed initial configuration

### Access Control

**Master Admin Routes:**
- `/tenant/onboard` - Create new tenants
- `/tenant/list` - List all tenants
- `/tenant/{clientId}` - View/edit tenant

**Tenant Admin Routes (require `tenant_admin_only` middleware):**
- `/tenant/users` - User management
- `/tenant/roles` - Role management
- `/tenant/plants` - Plant management
- `/tenant/warehouses` - Warehouse management
- `/tenant/storage-types` - Storage type management
- `/tenant/sections` - Section management
- `/tenant/bins` - Bin management
- `/tenant/vendors` - Vendor management
- `/tenant/categories` - Category management
- `/tenant/items` - Item master

### Permission Structure

Users can have:
- **Role-based permissions**: Inherited from assigned role
- **Custom permissions**: Override/in addition to role permissions
- **Plant access**: Limited to specific plants (if configured)

Permission check in views:
```blade
@if(in_array('all', session('user')['permissions'] ?? []) || 
    strtolower(session('user')['role'] ?? '') === 'admin')
    {{-- Show admin content --}}
@endif
```

---

## 🌐 API Routes & Endpoints

### Web Routes (routes/web.php)

#### Authentication
- `GET /login` - Login page
- `POST /login` - Handle login
- `POST /logout` - Handle logout

#### Dashboard
- `GET /dashboard` - Main dashboard (requires `tenant_configured`)

#### Tenant Management (Master Admin Only)
- `GET /tenant/onboard` - Onboarding form
- `POST /tenant/onboard` - Create tenant
- `GET /tenant/list` - List tenants
- `GET /tenant/{clientId}` - View tenant
- `GET /tenant/{clientId}/edit` - Edit tenant form
- `PUT /tenant/{clientId}` - Update tenant
- `DELETE /tenant/{clientId}` - Delete tenant

#### Tenant Configuration
- `GET /tenant/configure` - Configuration form
- `POST /tenant/configure` - Save configuration

#### User Management (Tenant Admin)
- `GET /tenant/users` - List users
- `GET /tenant/users/create` - Create user form
- `POST /tenant/users` - Store user
- `GET /tenant/users/{id}` - View user
- `GET /tenant/users/{id}/edit` - Edit user form
- `PUT /tenant/users/{id}` - Update user
- `DELETE /tenant/users/{id}` - Delete user
- `PATCH /tenant/users/{id}/toggle-status` - Toggle status

#### Role Management
- `GET /tenant/roles` - List roles
- `GET /tenant/roles/create` - Create role form
- `POST /tenant/roles` - Store role
- `GET /tenant/roles/{id}/permissions` - Get role permissions (AJAX)
- Full CRUD routes available

#### Plant Management
- Full CRUD routes for plants

#### Warehouse Management
- Full CRUD routes for warehouses
- `PATCH /tenant/warehouses/{id}/toggle-status` - Toggle status

#### Storage Type Management
- `GET /tenant/storage-types` - List storage types
- `GET /tenant/storage-types/list` - Get storage types for warehouse (AJAX)
- `GET /tenant/storage-types/sections` - Get sections for storage type (AJAX)
- `POST /tenant/storage-types` - Create storage type
- `PUT /tenant/storage-types/{id}` - Update storage type
- `DELETE /tenant/storage-types/{id}` - Delete storage type

#### Section Management
- `GET /tenant/sections` - List sections
- `POST /tenant/sections` - Create section
- `PUT /tenant/sections/{id}` - Update section
- `DELETE /tenant/sections/{id}` - Delete section

#### Bin Management
- `GET /tenant/bins` - List bins
- `GET /tenant/bins/create` - Generate bins form
- `POST /tenant/bins` - Generate bins
- `PUT /tenant/bins/{id}` - Update bin
- `DELETE /tenant/bins/{id}` - Delete bin
- `POST /tenant/bins/bulk-operation` - Bulk operations (AJAX)

#### Vendor Management
- Full CRUD routes for vendors
- `PATCH /tenant/vendors/{id}/toggle-status` - Toggle status

#### Category Management
- Full CRUD routes for item categories

#### Item Master
- Full CRUD routes for items
- `GET /tenant/items/import` - Import form
- `POST /tenant/items/import` - Handle import
- `GET /tenant/items/import/template` - Download CSV template
- `GET /tenant/items/export` - Export items to CSV
- `PATCH /tenant/items/{id}/toggle-status` - Toggle status

### AJAX Endpoints

These return JSON responses:

- `GET /tenant/storage-types/list?warehouse_id={id}` - Returns storage types for warehouse
- `GET /tenant/storage-types/sections?storage_type_id={id}` - Returns sections for storage type
- `POST /tenant/bins/bulk-operation` - Bulk bin operations

**Response Format:**
```json
{
  "success": true,
  "data": [...],
  "message": "Operation successful"
}
```

---

## 🎨 Frontend Structure

### Layout System

**Main Layout**: `resources/views/layout.blade.php`

- Includes sidebar navigation
- Header with user info
- Bootstrap 5 CSS framework
- Session-based menu visibility

**Navigation Menu** (for tenant admins):
- Dashboard
- User Management
- Role Management
- Plant Management
- Warehouse Management
- Storage Types
- Sections
- Bin Management
- Vendor Management
- Item Categories
- Item Master

### Blade Templates Structure

```
resources/views/
├── layout.blade.php              # Master layout
├── dashboard.blade.php           # Dashboard page
├── tenant/
│   ├── configure.blade.php      # Tenant configuration
│   ├── users/
│   │   ├── index.blade.php      # User list
│   │   ├── create.blade.php     # Create user form
│   │   ├── edit.blade.php       # Edit user form
│   │   └── show.blade.php       # User details
│   ├── warehouses/
│   │   ├── index.blade.php
│   │   ├── create.blade.php     # Includes bin auto-generation options
│   │   ├── edit.blade.php
│   │   └── show.blade.php
│   └── ...
```

### JavaScript Patterns

#### Dynamic Dropdown Loading

Example: Loading storage types when warehouse is selected

```javascript
function loadStorageTypes(warehouseId) {
    const baseUrl = "{{ route('tenant.storage-types.list') }}";
    const url = `${baseUrl}?warehouse_id=${encodeURIComponent(warehouseId)}`;
    
    fetch(url)
        .then(res => res.json())
        .then(data => {
            const select = document.getElementById('storage_type_id');
            select.innerHTML = '<option value="">Select Storage Type</option>';
            data.forEach(st => {
                const option = document.createElement('option');
                option.value = st._id;
                option.textContent = `${st.name} (${st.code})`;
                option.setAttribute('data-name', st.name);
                option.setAttribute('data-code', st.code);
                select.appendChild(option);
            });
        });
}
```

#### Form Validation Feedback

```blade
@error('field_name')
    <div class="invalid-feedback">{{ $message }}</div>
@enderror
```

#### Success/Error Messages

```blade
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
```

### CSS/Asset Structure

- **Bootstrap 5**: Included via CDN or local files
- **Custom CSS**: `resources/css/app.css`
- **Icons**: Bootstrap Icons, Remix Icons
- **JavaScript**: Vanilla JS, no frameworks

---

## ✨ Key Features

### 1. Multi-Tenancy
- Complete isolation between tenants
- Each tenant has separate MongoDB database
- Master database manages tenant metadata

### 2. User Management
- CRUD operations for users
- Role-based access control
- Granular permissions (can override role permissions)
- Plant access restrictions

### 3. Warehouse Hierarchy
- **Plants** → **Warehouses** → **Storage Types** → **Sections** → **Bins**
- Storage types: High Rack (has sections) and Cold Room (no sections)
- Bin auto-generation with customizable options

### 4. Item Master
- Full item CRUD
- CSV import/export
- Category management (hierarchical)
- Vendor linking
- Reorder level management

### 5. Vendor Management
- Vendor CRUD
- Pre-barcode flag support
- Portal credentials (optional)

### 6. Import/Export
- CSV import for items
- Import template download
- Error reporting during import
- CSV export with filters

---

## 🚀 Setup Instructions

### Prerequisites

- PHP 8.2 or higher
- Composer
- MongoDB (local or remote)
- Apache/Nginx (or XAMPP)
- Node.js & NPM (for frontend assets)

### Installation Steps

1. **Clone the repository**
```bash
cd /path/to/project
```

2. **Install PHP dependencies**
```bash
composer install
```

3. **Install frontend dependencies** (if using npm for assets)
```bash
npm install
```

4. **Configure environment**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Update `.env` file**
```env
APP_NAME="WMS"
APP_URL=http://localhost/wms

DB_CONNECTION=mongodb
MONGODB_DATABASE=wms_master
MONGODB_HOST=127.0.0.1
MONGODB_PORT=27017

SESSION_DRIVER=file
```

6. **Create MongoDB databases**
- Master database: `wms_master`
- Tenant databases: Created automatically during onboarding

7. **Set permissions**
```bash
chmod -R 775 storage bootstrap/cache
```

8. **Run migrations** (if any)
```bash
php artisan migrate
```

9. **Create master admin user** (manually or via seeder)

### Initial Master Admin Setup

Create master admin user in MongoDB `wms_master` database:

```javascript
use wms_master;
db.clients.insertOne({
  client_id: "MASTER",
  name: "Master Admin",
  email: "admin@wms.com",
  hashed_email: "sha256_hash_of_email",
  database_name: "wms_master",
  status: "active",
  record_type: "tenant"
});
```

Then create user in `users` collection with hashed password.

---

## 💻 Development Workflow

### Adding New Features

1. **Create Model** (if needed)
   - Place in `app/Models/Client/` for tenant models
   - Place in `app/Models/Master/` for master models

2. **Create Controller**
   - Place in `app/Http/Controllers/`
   - Follow existing controller patterns

3. **Create Routes**
   - Add to `routes/web.php`
   - Apply appropriate middleware

4. **Create Views**
   - Create in `resources/views/tenant/{module}/`
   - Extend `layout.blade.php`

5. **Add Navigation**
   - Update `resources/views/layout.blade.php`
   - Add menu item in tenant admin section

### MongoDB-Specific Notes

- **ObjectId Handling**: MongoDB ObjectIds need to be converted to strings for JSON responses
- **Validation**: Don't use `exists:` rule, use custom validation with `Model::find()`
- **Queries**: Use MongoDB query builder methods (`where()`, `find()`, etc.)

### Example: Creating New Module

```php
// 1. Model: app/Models/Client/NewModule.php
class NewModule extends Model
{
    protected $connection = 'tenant';
    protected $collection = 'new_modules';
    
    protected $fillable = ['field1', 'field2'];
}

// 2. Controller: app/Http/Controllers/NewModuleController.php
class NewModuleController extends Controller
{
    public function index() {
        $items = NewModule::paginate(15);
        return view('tenant.new-modules.index', compact('items'));
    }
    // ... other methods
}

// 3. Routes: routes/web.php
Route::resource('new-modules', NewModuleController::class)
    ->names(['index' => 'tenant.new-modules.index', ...]);

// 4. View: resources/views/tenant/new-modules/index.blade.php
@extends('layout')
@section('content')
    {{-- Your content --}}
@endsection
```

---

## 📝 Code Style & Conventions

### PHP/Laravel

- Follow PSR-12 coding standards
- Use type hints where possible
- Use Eloquent relationships for data access
- Validate user input in controllers
- Use services for complex business logic

### JavaScript

- Use vanilla JavaScript (no frameworks)
- Use `fetch()` for AJAX calls
- Keep functions modular and reusable
- Add console logging for debugging

### Blade Templates

- Use `@extends('layout')` for all pages
- Use `@section('content')` for main content
- Display validation errors: `@error('field')`
- Use session flash messages: `@if(session('success'))`

### MongoDB Queries

```php
// ✅ Good
$user = User::find($id);
$warehouses = Warehouse::where('plant_id', $plantId)->get();

// ❌ Bad (doesn't work with MongoDB)
$user = User::where('id', $id)->first(); // Use find() instead
```

---

## 🔍 Important Notes for Frontend Developers

### 1. Session-Based Authentication
- No JWT tokens or API keys
- All authentication via session cookies
- Session data available in Blade templates via `session()` helper

### 2. Route Helper Usage
```blade
{{ route('tenant.users.index') }}
```
Always use route names, not hardcoded URLs.

### 3. CSRF Protection
All POST/PUT/DELETE forms must include:
```blade
@csrf
```

For PUT/DELETE:
```blade
@csrf
@method('PUT') // or @method('DELETE')
```

### 4. Dynamic Content Loading
- Use AJAX (`fetch()`) for dynamic dropdowns
- Update DOM directly (no virtual DOM)
- Handle loading states if needed

### 5. Error Handling
- Validation errors shown inline with fields
- Success/error messages shown via session flash
- Check browser console for JavaScript errors

### 6. Form Submission
- Standard form POST (no AJAX submission by default)
- Redirect with success message after creation
- Redirect back with errors on validation failure

---

## 📚 Additional Resources

- **Laravel Documentation**: https://laravel.com/docs
- **MongoDB Laravel Package**: https://github.com/mongodb/laravel-mongodb
- **Bootstrap 5 Docs**: https://getbootstrap.com/docs/5.0
- **Blade Templates**: https://laravel.com/docs/blade

---

## 🤝 Contributing

When working on this project:

1. Follow existing code patterns
2. Test thoroughly before committing
3. Use meaningful commit messages
4. Update documentation if adding features
5. Clear caches after code changes:
   ```bash
   php artisan view:clear
   php artisan route:clear
   php artisan config:clear
   ```

---

## 📧 Support

For questions or issues:
1. Check existing code for patterns
2. Review this README
3. Check Laravel/MongoDB documentation
4. Review log files in `storage/logs/`

---

**Last Updated**: 2025
**Version**: 1.0.0
**License**: Proprietary
