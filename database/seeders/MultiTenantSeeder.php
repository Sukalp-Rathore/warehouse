<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Master\AdminUser;
use App\Models\Master\Client;
use App\Models\Client\User as ClientUser;
use App\Models\Client\Warehouse;
use App\Models\Client\Inventory;
use App\Services\TenantService;
use Illuminate\Support\Facades\Hash;

class MultiTenantSeeder extends Seeder
{
    protected $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    public function run()
    {
        // Create master admin user
        AdminUser::create([
            'email' => 'admin@wms.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'name' => 'System Administrator'
        ]);

        // Create first client
        $client1 = Client::create([
            'client_id' => 'client_001',
            'name' => 'ABC Corporation',
            'email' => 'contact@abc.com',
            'database_name' => 'wms_client_001',
            'status' => 'active',
            'subscription_plan' => 'premium',
            'max_warehouses' => 10,
            'max_users' => 50
        ]);

        // Create second client
        $client2 = Client::create([
            'client_id' => 'client_002',
            'name' => 'XYZ Industries',
            'email' => 'contact@xyz.com',
            'database_name' => 'wms_client_002',
            'status' => 'active',
            'subscription_plan' => 'basic',
            'max_warehouses' => 5,
            'max_users' => 25
        ]);

        // Seed data for Client 1
        $this->seedClientData($client1);

        // Seed data for Client 2
        $this->seedClientData($client2);

        $this->command->info('Multi-tenant data seeded successfully!');
    }

    private function seedClientData($client)
    {
        // Switch to client database
        $this->tenantService->setTenantConnection($client->database_name);
        
        // Create client admin user
        ClientUser::create([
            'email' => 'admin@' . strtolower(str_replace(' ', '', $client->name)) . '.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'permissions' => ['inventory_read', 'inventory_write', 'warehouse_manage', 'user_manage'],
            'status' => 'active',
            'client_id' => $client->client_id,
            'name' => $client->name . ' Admin'
        ]);

        // Create warehouse manager user
        ClientUser::create([
            'email' => 'manager@' . strtolower(str_replace(' ', '', $client->name)) . '.com',
            'password' => Hash::make('password'),
            'role' => 'warehouse_manager',
            'permissions' => ['inventory_read', 'inventory_write', 'warehouse_read'],
            'status' => 'active',
            'client_id' => $client->client_id,
            'name' => $client->name . ' Manager'
        ]);

        // Create warehouse operator user
        ClientUser::create([
            'email' => 'operator@' . strtolower(str_replace(' ', '', $client->name)) . '.com',
            'password' => Hash::make('password'),
            'role' => 'operator',
            'permissions' => ['inventory_read', 'inventory_write'],
            'status' => 'active',
            'client_id' => $client->client_id,
            'name' => $client->name . ' Operator'
        ]);

        // Create warehouses
        $warehouse1 = Warehouse::create([
            'name' => $client->name . ' Main Warehouse',
            'location' => 'New York',
            'capacity' => 10000,
            'status' => 'active',
            'description' => 'Primary warehouse facility for ' . $client->name
        ]);

        $warehouse2 = Warehouse::create([
            'name' => $client->name . ' Secondary Warehouse',
            'location' => 'Los Angeles',
            'capacity' => 5000,
            'status' => 'active',
            'description' => 'Secondary warehouse facility for ' . $client->name
        ]);

        // Create inventory items
        $inventoryItems = [
            [
                'warehouse_id' => $warehouse1->_id,
                'sku' => 'SKU001',
                'name' => 'Premium Widget A',
                'quantity' => 100,
                'location' => 'A1-B2-C3',
                'description' => 'High-quality widget for industrial use',
                'unit_price' => 29.99
            ],
            [
                'warehouse_id' => $warehouse1->_id,
                'sku' => 'SKU002',
                'name' => 'Standard Widget B',
                'quantity' => 250,
                'location' => 'A1-B2-C4',
                'description' => 'Standard widget for general use',
                'unit_price' => 19.99
            ],
            [
                'warehouse_id' => $warehouse2->_id,
                'sku' => 'SKU003',
                'name' => 'Deluxe Component C',
                'quantity' => 75,
                'location' => 'B1-C2-D3',
                'description' => 'Deluxe component with advanced features',
                'unit_price' => 49.99
            ],
            [
                'warehouse_id' => $warehouse2->_id,
                'sku' => 'SKU004',
                'name' => 'Basic Component D',
                'quantity' => 300,
                'location' => 'B1-C2-D4',
                'description' => 'Basic component for standard applications',
                'unit_price' => 12.99
            ]
        ];

        foreach ($inventoryItems as $item) {
            Inventory::create($item);
        }

        $this->command->info("Seeded data for client: {$client->name}");
    }
}
