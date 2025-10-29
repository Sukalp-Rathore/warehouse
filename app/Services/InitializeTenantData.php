<?php

namespace App\Services;

use App\Models\Master\TenantSettings;
use App\Models\Master\Client;
use App\Models\Client\Plant;
use App\Models\Client\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InitializeTenantData
{
    protected $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    /**
     * Execute initialization for tenant data
     */
    public function execute(TenantSettings $settings)
    {
        try {
            Log::info('InitializeTenantData: Starting initialization', [
                'client_id' => $settings->client_id
            ]);

            // Set the tenant connection - Get database name from Client model
            $client = Client::where('client_id', $settings->client_id)
                           ->where(function($q) {
                               $q->whereNull('record_type')->orWhere('record_type', 'tenant');
                           })
                           ->first();
            
            if (!$client) {
                throw new \Exception('Client not found for tenant settings');
            }
            
            $databaseName = $client->database_name;
            if (!$databaseName) {
                throw new \Exception('Database name not found for client');
            }
            
            $this->tenantService->setTenantConnection($databaseName);
            $connection = DB::connection('tenant');
            $database = $connection->getMongoDB();

            // 1. Create Default Plant
            $this->createDefaultPlant($settings, $database);

            // 2. Create Main Warehouse
            $this->createMainWarehouse($settings, $database);

            // 3. Create Storage Types
            $this->createStorageTypes($database);

            // 4. Create Warehouse Zones
            $this->createWarehouseZones($settings, $database);

            // 5. Create Bins
            $this->createBins($settings, $database);

            // 6. Create Base Configuration in settings collection
            $this->createBaseSettings($settings, $database);

            Log::info('InitializeTenantData: Initialization completed successfully', [
                'client_id' => $settings->client_id
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('InitializeTenantData: Initialization failed', [
                'client_id' => $settings->client_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Create default plant
     */
    private function createDefaultPlant(TenantSettings $settings, $database)
    {
        $plantDetails = $settings->plant_details ?? [];
        $plantCode = $plantDetails['plant_code'] ?? 'PLT-001';
        $plantName = $plantDetails['plant_name'] ?? 'Default Plant';
        $location = $plantDetails['plant_location'] ?? 'Main Location';

        // Check if plant already exists
        $existingPlant = $database->plants->findOne(['plant_code' => $plantCode]);
        if ($existingPlant) {
            Log::info('InitializeTenantData: Plant already exists, skipping', ['plant_code' => $plantCode]);
            return;
        }

        Plant::create([
            'plant_code' => $plantCode,
            'plant_name' => $plantName,
            'location' => $location,
            'address' => $plantDetails['plant_address'] ?? null,
            'contact_person' => $plantDetails['plant_contact_name'] ?? null,
            'contact_phone' => $plantDetails['plant_contact_phone'] ?? null,
            'contact_email' => $plantDetails['plant_contact_email'] ?? null,
            'is_active' => true,
        ]);

        Log::info('InitializeTenantData: Default plant created', ['plant_code' => $plantCode]);
    }

    /**
     * Create main warehouse
     */
    private function createMainWarehouse(TenantSettings $settings, $database)
    {
        $warehouseConfig = $settings->warehouse_config ?? [];
        $warehouseCode = $warehouseConfig['warehouse_code'] ?? 'WH-001';
        $warehouseName = $warehouseConfig['warehouse_name'] ?? 'Main WH';
        $storageType = $warehouseConfig['storage_type'] ?? 'Rack';

        // Check if warehouse already exists
        $existingWarehouse = $database->warehouses->findOne(['warehouse_code' => $warehouseCode]);
        if ($existingWarehouse) {
            Log::info('InitializeTenantData: Warehouse already exists, skipping', ['warehouse_code' => $warehouseCode]);
            return;
        }

        // Use direct insert as Warehouse model might have different structure
        $database->warehouses->insertOne([
            'warehouse_code' => $warehouseCode,
            'warehouse_name' => $warehouseName,
            'name' => $warehouseName,
            'location' => $settings->plant_details['plant_location'] ?? 'Main Location',
            'storage_type' => $storageType,
            'default_strategy' => $warehouseConfig['default_strategy'] ?? 'FIFO',
            'capacity' => $warehouseConfig['capacity'] ?? 1000,
            'status' => 'active',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        Log::info('InitializeTenantData: Main warehouse created', ['warehouse_code' => $warehouseCode]);
    }

    /**
     * Create storage types
     */
    private function createStorageTypes($database)
    {
        $storageTypes = [
            ['code' => 'BULK', 'name' => 'Bulk Storage', 'description' => 'Bulk material storage'],
            ['code' => 'RACK', 'name' => 'Rack Storage', 'description' => 'Pallet rack storage'],
            ['code' => 'PALLET', 'name' => 'Pallet Storage', 'description' => 'Pallet-based storage'],
            ['code' => 'SHELF', 'name' => 'Shelf Storage', 'description' => 'Shelf storage system'],
            ['code' => 'FLAT', 'name' => 'Flat Storage', 'description' => 'Flat floor storage'],
        ];

        foreach ($storageTypes as $type) {
            // Check if storage type already exists
            $existing = $database->storage_types->findOne(['code' => $type['code']]);
            if (!$existing) {
                $database->storage_types->insertOne([
                    'code' => $type['code'],
                    'name' => $type['name'],
                    'description' => $type['description'],
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        Log::info('InitializeTenantData: Storage types created');
    }

    /**
     * Create warehouse zones
     */
    private function createWarehouseZones(TenantSettings $settings, $database)
    {
        $warehouseCode = $settings->warehouse_config['warehouse_code'] ?? 'WH-001';
        $zones = ['A', 'B', 'C', 'D'];

        foreach ($zones as $zone) {
            // Check if zone already exists
            $existingZone = $database->zones->findOne([
                'warehouse_code' => $warehouseCode,
                'zone_code' => $zone
            ]);
            
            if (!$existingZone) {
                $database->zones->insertOne([
                    'zone_code' => $zone,
                    'zone_name' => 'Zone ' . $zone,
                    'warehouse_code' => $warehouseCode,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        Log::info('InitializeTenantData: Warehouse zones created', ['warehouse_code' => $warehouseCode]);
    }

    /**
     * Create bins
     */
    private function createBins(TenantSettings $settings, $database)
    {
        $binConfig = $settings->bin_config ?? [];
        $binCount = $binConfig['bin_count'] ?? 50;
        $binVolume = $binConfig['bin_volume'] ?? 1.0;
        $barcodeSettings = $settings->barcode_settings ?? [];
        $binPrefix = $barcodeSettings['barcode_bin_prefix'] ?? 'BIN-';
        $warehouseCode = $settings->warehouse_config['warehouse_code'] ?? 'WH-001';

        // Check how many bins already exist for this warehouse
        $existingBinCount = $database->bins->countDocuments(['warehouse_code' => $warehouseCode]);
        
        if ($existingBinCount > 0) {
            Log::info('InitializeTenantData: Bins already exist, skipping', [
                'warehouse_code' => $warehouseCode,
                'existing_count' => $existingBinCount
            ]);
            return;
        }

        $zones = ['A', 'B', 'C', 'D'];
        
        for ($i = 1; $i <= $binCount; $i++) {
            $binCode = $binPrefix . str_pad($i, 3, '0', STR_PAD_LEFT);
            $zoneCode = $zones[($i - 1) % 4]; // Rotate A, B, C, D
            
            $database->bins->insertOne([
                'bin_code' => $binCode,
                'bin_name' => 'Bin ' . $i,
                'warehouse_code' => $warehouseCode,
                'zone_code' => $zoneCode,
                'max_volume' => $binVolume,
                'current_volume' => 0,
                'current_weight' => 0,
                'status' => 'available',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        Log::info('InitializeTenantData: Bins created', [
            'warehouse_code' => $warehouseCode,
            'bin_count' => $binCount
        ]);
    }

    /**
     * Create base configuration in settings collection
     */
    private function createBaseSettings(TenantSettings $settings, $database)
    {
        // Check if settings already exist
        $existingSettings = $database->settings->findOne([]);
        if ($existingSettings) {
            Log::info('InitializeTenantData: Settings already exist, updating');
            
            $database->settings->updateOne(
                [],
                [
                    '$set' => [
                        'timezone' => $settings->timezone ?? 'UTC',
                        'barcode_settings' => $settings->barcode_settings ?? [],
                        'reorder_alert_rules' => $settings->reorder_alert_rules ?? [],
                        'integration_settings' => $settings->integration_settings ?? [],
                        'updated_at' => now()
                    ]
                ]
            );
        } else {
            $database->settings->insertOne([
                'timezone' => $settings->timezone ?? 'UTC',
                'barcode_settings' => $settings->barcode_settings ?? [],
                'reorder_alert_rules' => $settings->reorder_alert_rules ?? [],
                'integration_settings' => $settings->integration_settings ?? [],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        Log::info('InitializeTenantData: Base settings created/updated');
    }
}

