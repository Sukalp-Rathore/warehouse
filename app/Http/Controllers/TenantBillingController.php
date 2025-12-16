<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\Master\TenantUsage;
use App\Services\TenantBillingService;
use Illuminate\Http\Request;

class TenantBillingController extends Controller
{
    /**
     * Show billing overview for the current tenant (tenant admin).
     */
    public function index(TenantBillingService $billingService)
    {
        $tenant = PermissionHelper::getCurrentTenant();

        if (!$tenant) {
            abort(404, 'Tenant not found for current session.');
        }

        $plan = $billingService->getPlanDefinition($tenant);

        // Simple current-month usage summary
        $clientId = $tenant->client_id;
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $usage = TenantUsage::where('client_id', $clientId)
            ->whereBetween('occurred_at', [$startOfMonth, $endOfMonth])
            ->get()
            ->groupBy('metric')
            ->map(function ($records) {
                return $records->sum('quantity');
            });

        return view('tenant.billing.index', [
            'tenant' => $tenant,
            'plan' => $plan,
            'usage' => $usage,
        ]);
    }
}


