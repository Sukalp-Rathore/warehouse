<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\TenantService;
use Illuminate\Support\Facades\Auth;

class TenantMiddleware
{
    protected $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // If user has client_id, switch to tenant database
            if ($user->tenant_type === 'client' && isset($user->client_id)) {
                $client = \App\Models\Master\Client::where('client_id', $user->client_id)->first();
                if ($client) {
                    $this->tenantService->setTenantConnection($client->database_name);
                }
            }
        }

        return $next($request);
    }
}
