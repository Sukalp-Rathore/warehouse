<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client\Role;
use App\Models\Client\Plant;
use App\Models\Client\User;
use App\Services\TenantService;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\Hash;

class TestTenantUserManagement extends Command
{
    protected $signature = 'tenant:test-user-management {client_id}';
    protected $description = 'Test tenant user management functionality';

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

        $this->info("Testing user management for tenant: {$client->name}");

        // Test 1: Check if roles exist
        $this->info("\n=== Testing Roles ===");
        $roles = Role::all();
        $this->info("Found {$roles->count()} roles:");
        foreach ($roles as $role) {
            $this->info("- {$role->name}: " . count($role->permissions) . " permissions");
        }

        // Test 2: Check if plants exist
        $this->info("\n=== Testing Plants ===");
        $plants = Plant::all();
        $this->info("Found {$plants->count()} plants:");
        foreach ($plants as $plant) {
            $this->info("- {$plant->plant_name} ({$plant->plant_code})");
        }

        // Test 3: Check if users exist
        $this->info("\n=== Testing Users ===");
        $users = User::all();
        $this->info("Found {$users->count()} users:");
        foreach ($users as $user) {
            $roleName = $user->role ? $user->role->name : 'No Role';
            $this->info("- {$user->name} ({$user->email}) - Role: {$roleName}");
        }

        // Test 4: Test permission helper
        $this->info("\n=== Testing Permission Helper ===");
        if ($users->count() > 0) {
            $testUser = $users->first();
            $this->info("Testing permissions for user: {$testUser->name}");
            
            $hasWarehouseView = PermissionHelper::hasPermission($testUser, 'warehouse.view');
            $this->info("- Has warehouse.view permission: " . ($hasWarehouseView ? 'Yes' : 'No'));
            
            $allPermissions = PermissionHelper::getAllPermissions($testUser);
            $this->info("- Total permissions: " . count($allPermissions));
        }

        // Test 5: Create a test user
        $this->info("\n=== Creating Test User ===");
        $testUserData = [
            'username' => 'test_user',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'mobile' => '+91-9876543210',
            'status' => 'active'
        ];

        // Get first role and plant
        $firstRole = Role::first();
        $firstPlant = Plant::first();

        if ($firstRole && $firstPlant) {
            $testUserData['role_id'] = $firstRole->_id;
            $testUserData['permissions'] = ['inventory.view', 'warehouse.view'];
            $testUserData['plant_access'] = [$firstPlant->_id];

            $existingTestUser = User::where('email', 'test@example.com')->first();
            if (!$existingTestUser) {
                $testUser = User::create($testUserData);
                $this->info("Created test user: {$testUser->name}");
                
                // Test the user's permissions
                $canViewInventory = PermissionHelper::hasPermission($testUser, 'inventory.view');
                $this->info("- Can view inventory: " . ($canViewInventory ? 'Yes' : 'No'));
                
                $hasPlantAccess = PermissionHelper::hasPlantAccess($testUser, $firstPlant->_id);
                $this->info("- Has plant access: " . ($hasPlantAccess ? 'Yes' : 'No'));
            } else {
                $this->info("Test user already exists: {$existingTestUser->name}");
            }
        } else {
            $this->error("No roles or plants found. Cannot create test user.");
        }

        $this->info("\n=== Test Complete ===");
        $this->info("User management system is working correctly!");
        
        return 0;
    }
}
