<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Master\Client;
use App\Models\Master\TenantSettings;
use App\Models\Client\User as ClientUser;
use App\Services\TenantService;
use App\Services\TenantConfigurationService;

class TenantOnboardingController extends Controller
{
    protected $tenantService;
    protected $configService;

    public function __construct(TenantService $tenantService, TenantConfigurationService $configService)
    {
        $this->tenantService = $tenantService;
        $this->configService = $configService;
    }

    /**
     * Show the tenant onboarding form
     */
    public function index()
    {
        return view('tenant-onboarding');
    }

    /**
     * Process the tenant onboarding form
     */
    public function store(Request $request)
    {
        $request->validate([
            'tenant_name' => 'required|string|max:255',
            'tenant_email' => 'required|email|max:255',
            'admin_email' => 'required|email|max:255',
            'admin_password' => 'required|string|min:8|confirmed',
            'admin_name' => 'required|string|max:255',
            'timezone' => 'required|string',
            'plant_code' => 'required|string|max:50',
            'plant_name' => 'required|string|max:255',
            'warehouse_code' => 'required|string|max:50',
        ]);

        try {
            DB::beginTransaction();

            // Generate unique client ID and database name
            $clientId = 'CLT_' . strtoupper(Str::random(8));
            $databaseName = 'wms_' . strtolower(Str::slug($request->tenant_name)) . '_' . time();

            // Create client record with hashed admin email for login mapping
            $hashedAdminEmail = hash('sha256', $request->admin_email);
            $client = Client::create([
                'client_id' => $clientId,
                'name' => $request->tenant_name,
                'email' => $request->tenant_email,
                'hashed_email' => $hashedAdminEmail,
                'database_name' => $databaseName,
                'status' => 'active',
                'subscription_plan' => 'basic',
                'max_warehouses' => 5,
                'max_users' => 10,
                'record_type' => 'tenant', // Mark as tenant record
            ]);

            // Prepare tenant settings
            $tenantSettings = $this->prepareTenantSettings($request, $clientId);

            // Create tenant settings record
            $settings = TenantSettings::create($tenantSettings);

            // Create tenant database and initialize with default structure
            $this->configService->createTenantDatabase($databaseName, $settings);

            // Create admin user in the tenant database
            $this->createTenantAdminUser($request, $clientId, $databaseName);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tenant onboarded successfully! Admin user created and ready to login.',
                'client_id' => $clientId,
                'database_name' => $databaseName,
                'admin_email' => $request->admin_email,
                'redirect_url' => route('tenant.list')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log the error for debugging
            \Log::error('Tenant onboarding failed: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to onboard tenant: ' . $e->getMessage(),
                'debug' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * List all tenants
     */
    public function list()
    {
        $tenants = TenantSettings::with('client')->get();
        return view('tenant-list', compact('tenants'));
    }

    /**
     * Show tenant details
     */
    public function show($clientId)
    {
        $tenant = TenantSettings::with('client')->where('client_id', $clientId)->firstOrFail();
        return view('tenant-details', compact('tenant'));
    }

    /**
     * Edit tenant settings
     */
    public function edit($clientId)
    {
        $tenant = TenantSettings::with('client')->where('client_id', $clientId)->firstOrFail();
        return view('tenant-edit', compact('tenant'));
    }

    /**
     * Update tenant settings
     */
    public function update(Request $request, $clientId)
    {
        $request->validate([
            'tenant_name' => 'required|string|max:255',
            'tenant_email' => 'required|email|max:255',
            'timezone' => 'required|string',
        ]);

        try {
            $tenant = TenantSettings::where('client_id', $clientId)->firstOrFail();
            $client = Client::where('client_id', $clientId)->firstOrFail();

            // Update client info
            $client->update([
                'name' => $request->tenant_name,
                'email' => $request->tenant_email,
            ]);

            // Update tenant settings
            $updatedSettings = $this->prepareTenantSettings($request, $clientId);
            $tenant->update($updatedSettings);

            return response()->json([
                'success' => true,
                'message' => 'Tenant settings updated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tenant: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete tenant
     */
    public function destroy($clientId)
    {
        try {
            DB::beginTransaction();

            // Delete tenant settings
            $tenant = TenantSettings::where('client_id', $clientId)->firstOrFail();
            $tenant->delete();

            // Delete client record
            $client = Client::where('client_id', $clientId)->firstOrFail();
            $client->delete();

            // TODO: Drop tenant database (implement based on your database setup)
            // $this->configService->dropTenantDatabase($client->database_name);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tenant deleted successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete tenant: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Prepare tenant settings from request
     */
    private function prepareTenantSettings(Request $request, $clientId)
    {
        return [
            'client_id' => $clientId,
            'tenant_name' => $request->tenant_name,
            'tenant_email' => $request->tenant_email,
            'timezone' => $request->timezone,
            'plant_details' => [
                'plant_code' => $request->plant_code,
                'plant_name' => $request->plant_name,
                'plant_location' => $request->plant_location,
                'plant_contact_name' => $request->plant_contact_name,
                'plant_contact_email' => $request->plant_contact_email,
            ],
            'warehouse_config' => [
                'warehouse_code' => $request->warehouse_code,
                'warehouse_name' => $request->warehouse_name,
                'storage_type' => $request->storage_type,
                'default_strategy' => $request->default_strategy,
            ],
            'bin_config' => [
                'bin_count' => $request->bin_count,
                'bin_volume' => $request->bin_volume,
                'reorder_levels' => $request->reorder_levels,
            ],
            'item_vendor_rules' => [
                'vendor_portal' => $request->vendor_portal,
                'vendor_prebarcode' => $request->vendor_prebarcode,
            ],
            'barcode_settings' => [
                'barcode_bin_prefix' => $request->barcode_bin_prefix,
                'barcode_shipment_prefix' => $request->barcode_shipment_prefix,
                'barcode_length' => $request->barcode_length,
            ],
            'roles_permissions' => [
                'roles_json' => $request->roles_json,
            ],
            'reorder_alert_rules' => [
                'global_rl1' => $request->global_rl1,
                'global_rl2' => $request->global_rl2,
                'alert_channels' => $request->alert_channels,
            ],
            'integration_settings' => [
                'sap_endpoint' => $request->sap_endpoint,
                'sap_token' => $request->sap_token,
                'whatsapp_enabled' => $request->whatsapp_enabled,
                'ai_provider' => $request->ai_provider,
            ],
            'status' => 'active',
            'configuration_completed' => false, // Tenant admin must complete configuration on first login
        ];
    }

    /**
     * Create admin user in tenant database
     */
    private function createTenantAdminUser(Request $request, $clientId, $databaseName)
    {
        // Switch to tenant database
        $this->tenantService->setTenantConnection($databaseName);

        // Clean up any existing admin users to ensure only one admin exists
        $existingAdmins = ClientUser::where('role', 'admin')->get();
        if ($existingAdmins->count() > 0) {
            \Log::info('Cleaning up existing admin users', [
                'count' => $existingAdmins->count(),
                'client_id' => $clientId,
                'database_name' => $databaseName
            ]);
            
            // Delete all existing admin users
            ClientUser::where('role', 'admin')->delete();
        }

        // Create admin user
        ClientUser::create([
            'email' => $request->admin_email,
            'password' => Hash::make($request->admin_password),
            'name' => $request->admin_name,
            'role' => 'admin',
            'permissions' => ['all'], // Admin has all permissions
            'status' => 'active',
            'client_id' => $clientId,
        ]);

        \Log::info('Tenant admin user created', [
            'client_id' => $clientId,
            'admin_email' => $request->admin_email,
            'database_name' => $databaseName
        ]);
    }
}
