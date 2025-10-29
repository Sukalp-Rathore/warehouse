<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client\Role;
use App\Models\Client\Plant;
use App\Models\Client\User;
use App\Services\TenantService;
use Illuminate\Support\Facades\Hash;

class SeedTenantDefaults extends Command
{
    protected $signature = 'tenant:seed-defaults {client_id}';
    protected $description = 'Seed default roles, plants, and admin user for a tenant';

    protected $tenantService;

    public function __construct(TenantService $tenantService)
    {
        parent::__construct();
        $this->tenantService = $tenantService;
    }

    public function handle()
    {
        $clientId = $this->argument('client_id');
        
        // Get client information
        $client = \App\Models\Master\Client::where('client_id', $clientId)->first();
        
        if (!$client) {
            $this->error("Client with ID {$clientId} not found.");
            return 1;
        }

        // Switch to tenant database
        $this->tenantService->setTenantConnection($client->database_name);

        $this->info("Seeding defaults for tenant: {$client->name}");

        // Create default roles
        $this->createDefaultRoles();
        
        // Create default plant
        $this->createDefaultPlant();
        
        // Create admin user if not exists
        $this->createAdminUser($client);

        $this->info("Default seeding completed for tenant: {$client->name}");
        return 0;
    }

    private function createDefaultRoles()
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
                'is_active' => true
            ],
            [
                'name' => 'Manager',
                'description' => 'Warehouse management access',
                'permissions' => [
                    'warehouse.view', 'warehouse.create', 'warehouse.edit',
                    'inventory.view', 'inventory.create', 'inventory.edit',
                    'reports.view', 'reports.export'
                ],
                'is_active' => true
            ],
            [
                'name' => 'Operator',
                'description' => 'Basic warehouse operations',
                'permissions' => [
                    'warehouse.view',
                    'inventory.view', 'inventory.create', 'inventory.edit'
                ],
                'is_active' => true
            ],
            [
                'name' => 'Viewer',
                'description' => 'Read-only access',
                'permissions' => [
                    'warehouse.view',
                    'inventory.view',
                    'reports.view'
                ],
                'is_active' => true
            ]
        ];

        foreach ($defaultRoles as $roleData) {
            $existingRole = Role::where('name', $roleData['name'])->first();
            if (!$existingRole) {
                Role::create($roleData);
                $this->info("Created role: {$roleData['name']}");
            } else {
                $this->info("Role already exists: {$roleData['name']}");
            }
        }
    }

    private function createDefaultPlant()
    {
        $plantData = [
            'plant_code' => 'PLT001',
            'plant_name' => 'Main Warehouse',
            'location' => 'Head Office',
            'address' => 'Main Warehouse Location',
            'contact_person' => 'Warehouse Manager',
            'contact_phone' => '+91-0000000000',
            'contact_email' => 'warehouse@company.com',
            'is_active' => true
        ];

        $existingPlant = Plant::where('plant_code', $plantData['plant_code'])->first();
        if (!$existingPlant) {
            Plant::create($plantData);
            $this->info("Created default plant: {$plantData['plant_name']}");
        } else {
            $this->info("Plant already exists: {$plantData['plant_name']}");
        }
    }

    private function createAdminUser($client)
    {
        $adminUserData = [
            'username' => 'admin',
            'name' => 'System Administrator',
            'email' => $client->email,
            'password' => Hash::make('admin123'), // Default password
            'mobile' => '+91-0000000000',
            'status' => 'active'
        ];

        $existingUser = User::where('email', $adminUserData['email'])->first();
        if (!$existingUser) {
            // Get admin role
            $adminRole = Role::where('name', 'Admin')->first();
            if ($adminRole) {
                $adminUserData['role_id'] = $adminRole->_id;
                $adminUserData['permissions'] = [];
                $adminUserData['plant_access'] = [Plant::first()->_id]; // Access to first plant
                
                User::create($adminUserData);
                $this->info("Created admin user: {$adminUserData['email']}");
            } else {
                $this->error("Admin role not found. Please create roles first.");
            }
        } else {
            $this->info("Admin user already exists: {$adminUserData['email']}");
        }
    }
}
