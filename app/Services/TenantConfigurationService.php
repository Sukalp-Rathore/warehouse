<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use App\Models\Master\TenantSettings;

class TenantConfigurationService
{
    protected $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    /**
     * Create a new tenant database with initial structure
     */
    public function createTenantDatabase($databaseName, TenantSettings $settings)
    {
        try {
            // Set the tenant connection
            $this->tenantService->setTenantConnection($databaseName);
            
            // Initialize tenant database with default collections and data
            $this->initializeTenantDatabase($databaseName, $settings);
            
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to create tenant database: " . $e->getMessage());
        }
    }

    /**
     * Initialize tenant database with default structure
     */
    private function initializeTenantDatabase($databaseName, TenantSettings $settings)
    {
        // Create default collections and initial data
        $this->createDefaultCollections($settings);
        $this->createDefaultUsers($settings);
        $this->createDefaultWarehouseData($settings);
        $this->createDefaultBinData($settings);
    }

    /**
     * Create default collections for the tenant
     */
    private function createDefaultCollections(TenantSettings $settings)
    {
        try {
            // Get the tenant connection
            $connection = DB::connection('tenant');
            
            // Test the connection
            if (!$connection) {
                throw new \Exception('Failed to establish tenant database connection');
            }
            
            // Get the MongoDB database instance
            $database = $connection->getMongoDB();
            
            // Users collection - will be populated by createTenantAdminUser()
            // No default admin user created here to avoid duplicates

            // Warehouses collection
            $database->warehouses->insertOne([
                'warehouse_code' => $settings->warehouse_config['warehouse_code'],
                'warehouse_name' => $settings->warehouse_config['warehouse_name'],
                'storage_type' => $settings->warehouse_config['storage_type'],
                'default_strategy' => $settings->warehouse_config['default_strategy'],
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Plants collection
            $database->plants->insertOne([
                'plant_code' => $settings->plant_details['plant_code'],
                'plant_name' => $settings->plant_details['plant_name'],
                'plant_location' => $settings->plant_details['plant_location'],
                'contact_name' => $settings->plant_details['plant_contact_name'],
                'contact_email' => $settings->plant_details['plant_contact_email'],
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Settings collection
            $database->settings->insertOne([
                'timezone' => $settings->timezone,
                'barcode_settings' => $settings->barcode_settings,
                'reorder_alert_rules' => $settings->reorder_alert_rules,
                'integration_settings' => $settings->integration_settings,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Create default roles using our new Role model
            $this->createDefaultRoles($database);
        } catch (\Exception $e) {
            \Log::error('Failed to create default collections: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create default users for the tenant
     */
    private function createDefaultUsers(TenantSettings $settings)
    {
        // Admin user will be created by TenantOnboardingController::createTenantAdminUser()
        // This method is kept for potential future use but doesn't create admin users
        
        // Create warehouse manager if specified in roles
        if (!empty($settings->roles_permissions['roles_json'])) {
            $connection = DB::connection('tenant');
            $database = $connection->getMongoDB();
            
            $roles = json_decode($settings->roles_permissions['roles_json'], true);
            if (is_array($roles)) {
                foreach ($roles as $role) {
                    if ($role['role'] === 'Warehouse Manager') {
                        $database->users->insertOne([
                            'name' => 'Warehouse Manager',
                            'email' => 'warehouse@' . explode('@', $settings->tenant_email)[1],
                            'password' => bcrypt('warehouse123'),
                            'role' => 'warehouse_manager',
                            'permissions' => $role['permissions'],
                            'status' => 'active',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        break;
                    }
                }
            }
        }
    }

    /**
     * Create default warehouse data
     */
    private function createDefaultWarehouseData(TenantSettings $settings)
    {
        $connection = DB::connection('tenant');
        $database = $connection->getMongoDB();
        
        // Create warehouse zones
        $zones = ['A', 'B', 'C', 'D'];
        foreach ($zones as $zone) {
            $database->zones->insertOne([
                'zone_code' => $zone,
                'zone_name' => 'Zone ' . $zone,
                'warehouse_code' => $settings->warehouse_config['warehouse_code'],
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    /**
     * Create default bin data
     */
    private function createDefaultBinData(TenantSettings $settings)
    {
        $connection = DB::connection('tenant');
        $database = $connection->getMongoDB();
        $binCount = $settings->bin_config['bin_count'] ?? 25;
        $binVolume = $settings->bin_config['bin_volume'] ?? 1.0;
        $binPrefix = $settings->barcode_settings['barcode_bin_prefix'] ?? 'BIN-';

        for ($i = 1; $i <= $binCount; $i++) {
            $binCode = $binPrefix . str_pad($i, 3, '0', STR_PAD_LEFT);
            
            $database->bins->insertOne([
                'bin_code' => $binCode,
                'bin_name' => 'Bin ' . $i,
                'zone_code' => chr(64 + (($i - 1) % 4) + 1), // A, B, C, D rotation
                'max_volume' => $binVolume,
                'current_volume' => 0,
                'status' => 'available',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    /**
     * Get tenant configuration
     */
    public function getTenantConfiguration($clientId)
    {
        return TenantSettings::where('client_id', $clientId)->first();
    }

    /**
     * Create default roles for the tenant
     */
    private function createDefaultRoles($database)
    {
        $defaultRoles = [
            [
                'name' => 'Admin',
                'description' => 'Full system access',
                'permissions' => [
                    'warehouse.view', 'warehouse.create', 'warehouse.edit', 'warehouse.delete',
                    'inventory.view', 'inventory.create', 'inventory.edit', 'inventory.delete',
                    'reports.view', 'reports.export',
                    'users.view', 'users.create', 'users.edit', 'users.delete',
                    'settings.view', 'settings.edit'
                ],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Manager',
                'description' => 'Warehouse management access',
                'permissions' => [
                    'warehouse.view', 'warehouse.create', 'warehouse.edit',
                    'inventory.view', 'inventory.create', 'inventory.edit',
                    'reports.view', 'reports.export'
                ],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Operator',
                'description' => 'Basic warehouse operations',
                'permissions' => [
                    'warehouse.view',
                    'inventory.view', 'inventory.create', 'inventory.edit'
                ],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Viewer',
                'description' => 'Read-only access',
                'permissions' => [
                    'warehouse.view',
                    'inventory.view',
                    'reports.view'
                ],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($defaultRoles as $roleData) {
            $database->roles->insertOne($roleData);
        }
    }

    /**
     * Update tenant configuration
     */
    public function updateTenantConfiguration($clientId, array $settings)
    {
        $tenant = TenantSettings::where('client_id', $clientId)->first();
        if ($tenant) {
            $tenant->update($settings);
            return $tenant;
        }
        return null;
    }
}
