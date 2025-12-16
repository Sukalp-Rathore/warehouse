<?php

namespace App\Http\Controllers;

use App\Models\Master\Client;
use App\Services\TenantBillingService;
use Illuminate\Http\Request;

class TenantBillingAdminController extends Controller
{
    /**
     * List tenants with basic billing information for platform admins.
     */
    public function index()
    {
        $tenants = Client::tenants()->orderBy('created_at', 'desc')->get();

        return view('master.billing.tenants', compact('tenants'));
    }

    /**
     * Show a single tenant's billing details.
     */
    public function show($clientId, TenantBillingService $billingService)
    {
        $tenant = Client::tenants()->where('client_id', $clientId)->firstOrFail();
        $plan = $billingService->getPlanDefinition($tenant);

        return view('master.billing.show-tenant', compact('tenant', 'plan'));
    }
}


