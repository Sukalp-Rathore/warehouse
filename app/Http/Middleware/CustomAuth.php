<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\TenantService;

class CustomAuth
{
    protected $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated via session
        if (!session('authenticated')) {
            return redirect()->route('login');
        }

        // Set up tenant context if it's a client user
        if (session('tenant_type') === 'client' && session('client_id')) {
            $client = \App\Models\Master\Client::where('client_id', session('client_id'))->first();
            if ($client) {
                $this->tenantService->setTenantConnection($client->database_name);
            }
        }

        return $next($request);
    }
}
