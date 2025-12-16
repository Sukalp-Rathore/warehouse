<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Client\GRN;
use App\Models\Client\Item;
use App\Models\Client\Vendor;
use App\Models\Client\Warehouse;
use App\Models\Master\TenantSettings;

class SAPIntegrationService
{
    /**
     * Fetch GRNs from SAP
     * 
     * @param string|null $status Filter by status (pending, approved)
     * @return array
     */
    public function fetchGRNs($status = null)
    {
        try {
            // Get tenant SAP configuration from TenantSettings
            $clientId = session('client_id');
            if (!$clientId) {
                return [
                    'success' => false,
                    'message' => 'Client ID not found in session'
                ];
            }

            $tenantSettings = TenantSettings::where('client_id', $clientId)->first();
            if (!$tenantSettings) {
                return [
                    'success' => false,
                    'message' => 'Tenant settings not found'
                ];
            }

            $integrationSettings = $tenantSettings->integration_settings ?? [];
            $sapEndpoint = $integrationSettings['sap_endpoint'] ?? null;
            $sapToken = $integrationSettings['sap_token'] ?? null;

            if (!$sapEndpoint || !$sapToken) {
                return [
                    'success' => false,
                    'message' => 'SAP integration not configured. Please configure SAP endpoint and token in tenant settings.'
                ];
            }

            // Build request parameters
            $params = [];
            if ($status) {
                $params['status'] = $status;
            }

            // Make API call to SAP
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $sapToken,
                'Accept' => 'application/json',
            ])->timeout(30)->get($sapEndpoint . '/api/grns', $params);

            if (!$response->successful()) {
                Log::error('SAP GRN fetch failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return [
                    'success' => false,
                    'message' => 'Failed to fetch GRNs from SAP: ' . $response->body()
                ];
            }

            $sapGRNs = $response->json();

            if (!isset($sapGRNs['data']) || !is_array($sapGRNs['data'])) {
                return [
                    'success' => false,
                    'message' => 'Invalid response format from SAP'
                ];
            }

            $syncedCount = 0;
            $errors = [];

            // Sync each GRN from SAP
            foreach ($sapGRNs['data'] as $sapGRN) {
                try {
                    $this->syncGRNFromSAP($sapGRN);
                    $syncedCount++;
                } catch (\Exception $e) {
                    $errors[] = 'GRN ' . ($sapGRN['grn_number'] ?? 'unknown') . ': ' . $e->getMessage();
                    Log::error('Failed to sync GRN from SAP', [
                        'sap_grn' => $sapGRN,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return [
                'success' => true,
                'message' => "Synced {$syncedCount} GRN(s) from SAP",
                'synced_count' => $syncedCount,
                'errors' => $errors
            ];

        } catch (\Exception $e) {
            Log::error('SAP GRN fetch exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Error fetching GRNs from SAP: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Sync a single GRN from SAP data
     * 
     * @param array $sapGRN SAP GRN data
     * @return GRN
     */
    public function syncGRNFromSAP($sapGRN)
    {
        // Map SAP status to WMS status
        $statusMap = [
            'PENDING' => 'pending',
            'APPROVED' => 'approved',
            'IN_PROGRESS' => 'in_progress',
            'COMPLETED' => 'completed',
            'CANCELLED' => 'cancelled',
        ];

        $sapStatus = strtoupper($sapGRN['status'] ?? 'PENDING');
        $wmsStatus = $statusMap[$sapStatus] ?? 'pending';

        // Find or create GRN by SAP GRN ID
        $grn = GRN::where('sap_grn_id', $sapGRN['sap_grn_id'] ?? $sapGRN['id'])->first();

        $grnData = [
            'sap_grn_id' => $sapGRN['sap_grn_id'] ?? $sapGRN['id'],
            'grn_number' => $sapGRN['grn_number'],
            'vendor_id' => $this->getOrCreateVendor($sapGRN['vendor']),
            'warehouse_id' => $this->getWarehouse($sapGRN['warehouse_code']),
            'po_reference' => $sapGRN['po_reference'] ?? $sapGRN['po_number'] ?? null,
            'status' => $wmsStatus,
            'putaway_status' => $wmsStatus === 'approved' ? 'pending' : ($grn->putaway_status ?? 'pending'),
            'items' => $this->mapSAPItems($sapGRN['items'] ?? []),
            'sap_data' => $sapGRN, // Store full SAP data for reference
            'sap_synced_at' => now(),
        ];

        // Preserve approval info if already approved in WMS
        if ($grn && $grn->approved_by) {
            $grnData['approved_by'] = $grn->approved_by;
            $grnData['approved_at'] = $grn->approved_at;
        }

        if ($grn) {
            $grn->update($grnData);
        } else {
            $grnData['created_by'] = session('user._id');
            $grn = GRN::create($grnData);
        }

        return $grn;
    }

    /**
     * Get or create vendor from SAP data
     * 
     * @param array|string $vendorData SAP vendor data or vendor code
     * @return string Vendor ID
     */
    private function getOrCreateVendor($vendorData)
    {
        if (is_string($vendorData)) {
            $vendorCode = $vendorData;
            $vendorName = $vendorData;
        } else {
            $vendorCode = $vendorData['code'] ?? $vendorData['vendor_code'] ?? null;
            $vendorName = $vendorData['name'] ?? $vendorData['vendor_name'] ?? $vendorCode;
        }

        if (!$vendorCode) {
            throw new \Exception('Vendor code is required');
        }

        $vendor = Vendor::where('vendor_code', $vendorCode)->first();

        if (!$vendor) {
            $vendor = Vendor::create([
                'vendor_code' => $vendorCode,
                'vendor_name' => $vendorName,
                'status' => 'active',
                'created_by' => session('user._id'),
            ]);
        }

        return (string) $vendor->_id;
    }

    /**
     * Get warehouse by SAP warehouse code
     * 
     * @param string $warehouseCode SAP warehouse code
     * @return string Warehouse ID
     */
    private function getWarehouse($warehouseCode)
    {
        if (!$warehouseCode) {
            throw new \Exception('Warehouse code is required');
        }

        $warehouse = Warehouse::where('warehouse_code', $warehouseCode)
            ->orWhere('warehouse_name', 'like', "%{$warehouseCode}%")
            ->first();

        if (!$warehouse) {
            throw new \Exception("Warehouse not found for code: {$warehouseCode}");
        }

        return (string) $warehouse->_id;
    }

    /**
     * Map SAP items to WMS format
     * 
     * @param array $sapItems SAP items array
     * @return array WMS items array
     */
    private function mapSAPItems($sapItems)
    {
        $items = [];

        foreach ($sapItems as $sapItem) {
            $itemCode = $sapItem['item_code'] ?? $sapItem['material_number'] ?? null;
            
            if (!$itemCode) {
                continue;
            }

            $item = Item::where('item_code', $itemCode)->first();

            if (!$item) {
                // Log warning but continue - item might be created later
                Log::warning('Item not found for SAP GRN', [
                    'item_code' => $itemCode,
                    'sap_item' => $sapItem
                ]);
                continue;
            }

            $items[] = [
                'item_id' => (string) $item->_id,
                'item_code' => $itemCode,
                'quantity' => $sapItem['quantity'] ?? $sapItem['qty'] ?? 0,
                'unit_price' => $sapItem['unit_price'] ?? $sapItem['price'] ?? null,
                'uom' => $sapItem['uom'] ?? $sapItem['unit'] ?? $item->uom ?? 'PCS',
                'batch_number' => $sapItem['batch_number'] ?? null,
                'expiry_date' => $sapItem['expiry_date'] ?? null,
            ];
        }

        return $items;
    }
}

