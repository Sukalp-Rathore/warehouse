<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Master\TenantSettings;
use App\Services\InitializeTenantData;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class TenantConfigurationController extends Controller
{
    protected $initService;

    public function __construct(InitializeTenantData $initService)
    {
        $this->initService = $initService;
    }

    /**
     * Show the configuration form
     */
    public function show()
    {
        $clientId = session('client_id');
        
        if (!$clientId) {
            return redirect()->route('login')->with('error', 'Please login to continue.');
        }

        // Get existing settings or create empty instance
        $settings = TenantSettings::where('client_id', $clientId)->first();
        
        // If already configured, redirect to dashboard
        if ($settings && $settings->configuration_completed) {
            return redirect()->route('dashboard')
                ->with('info', 'Your tenant is already configured.');
        }

        return view('tenant.configure', compact('settings'));
    }

    /**
     * Store configuration and initialize tenant data
     */
    public function store(Request $request)
    {
        $clientId = session('client_id');
        
        if (!$clientId) {
            return redirect()->route('login')->with('error', 'Please login to continue.');
        }

        $validator = Validator::make($request->all(), [
            'tenant_name' => 'required|string|max:255',
            'tenant_email' => 'required|email|max:255',
            'timezone' => 'required|string',
            
            // Plant details
            'plant_code' => 'required|string|max:50',
            'plant_name' => 'required|string|max:255',
            'plant_location' => 'required|string|max:255',
            'plant_address' => 'nullable|string|max:500',
            'plant_contact_name' => 'nullable|string|max:255',
            'plant_contact_phone' => 'nullable|string|max:20',
            'plant_contact_email' => 'nullable|email|max:255',
            
            // Warehouse config
            'warehouse_code' => 'required|string|max:50',
            'warehouse_name' => 'required|string|max:255',
            'storage_type' => 'required|string|max:50',
            'default_strategy' => 'nullable|string|max:50',
            'capacity' => 'nullable|integer|min:1',
            
            // Bin config
            'bin_count' => 'required|integer|min:1|max:1000',
            'bin_volume' => 'required|numeric|min:0.1',
            
            // Barcode settings
            'barcode_bin_prefix' => 'nullable|string|max:10',
            'barcode_format' => 'nullable|string|max:50',
            
            // Optional settings
            'reorder_alert_rules' => 'nullable|array',
            'integration_settings' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            Log::info('TenantConfiguration: Starting configuration save', ['client_id' => $clientId]);

            // Prepare settings data
            $settingsData = [
                'client_id' => $clientId,
                'tenant_name' => $request->tenant_name,
                'tenant_email' => $request->tenant_email,
                'timezone' => $request->timezone,
                'plant_details' => [
                    'plant_code' => $request->plant_code,
                    'plant_name' => $request->plant_name,
                    'plant_location' => $request->plant_location,
                    'plant_address' => $request->plant_address,
                    'plant_contact_name' => $request->plant_contact_name,
                    'plant_contact_phone' => $request->plant_contact_phone,
                    'plant_contact_email' => $request->plant_contact_email,
                ],
                'warehouse_config' => [
                    'warehouse_code' => $request->warehouse_code,
                    'warehouse_name' => $request->warehouse_name,
                    'storage_type' => $request->storage_type,
                    'default_strategy' => $request->default_strategy ?? 'FIFO',
                    'capacity' => $request->capacity ?? 1000,
                ],
                'bin_config' => [
                    'bin_count' => $request->bin_count,
                    'bin_volume' => $request->bin_volume,
                ],
                'barcode_settings' => [
                    'barcode_bin_prefix' => $request->barcode_bin_prefix ?? 'BIN-',
                    'barcode_format' => $request->barcode_format ?? 'CODE128',
                ],
                'reorder_alert_rules' => $request->reorder_alert_rules ?? [],
                'integration_settings' => $request->integration_settings ?? [],
                'status' => 'active',
                'configuration_completed' => false, // Will be set to true after initialization
            ];

            // Update or create tenant settings
            $settings = TenantSettings::updateOrCreate(
                ['client_id' => $clientId],
                $settingsData
            );

            Log::info('TenantConfiguration: Settings saved, starting initialization', [
                'client_id' => $clientId,
                'settings_id' => $settings->_id
            ]);

            // Trigger synchronous initialization
            $this->initService->execute($settings);

            // Mark configuration as completed
            $settings->update(['configuration_completed' => true]);

            Log::info('TenantConfiguration: Configuration completed successfully', [
                'client_id' => $clientId
            ]);

            return redirect()->route('dashboard')
                ->with('success', 'Welcome! Your warehouse has been set up successfully. You can now start using the system.');

        } catch (\Exception $e) {
            Log::error('TenantConfiguration: Configuration failed', [
                'client_id' => $clientId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Configuration failed: ' . $e->getMessage())
                ->withInput();
        }
    }
}

