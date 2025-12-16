<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Master\TenantSettings;
use Illuminate\Support\Facades\Log;

class TenantConfigured
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for master admin users
        if (session('tenant_type') === 'master') {
            return $next($request);
        }

        // Skip for configure routes themselves (to avoid redirect loop)
        if ($request->is('tenant/configure*')) {
            return $next($request);
        }

        // Only check for tenant users
        if (session('tenant_type') !== 'client') {
            return $next($request);
        }

        $clientId = session('client_id');
        
        if (!$clientId) {
            Log::warning('TenantConfigured: No client_id in session', [
                'path' => $request->path(),
                'session' => session()->all()
            ]);
            return redirect()->route('login')->with('error', 'Please login to continue.');
        }

        // Check if tenant has completed configuration
        $tenantSettings = TenantSettings::where('client_id', $clientId)->first();
        
        if (!$tenantSettings || !$tenantSettings->configuration_completed) {
            Log::info('TenantConfigured: Redirecting to configuration', [
                'client_id' => $clientId,
                'has_settings' => $tenantSettings ? 'yes' : 'no',
                'configuration_completed' => $tenantSettings ? $tenantSettings->configuration_completed : 'n/a'
            ]);
            
            return redirect()->route('tenant.configure')
                ->with('info', 'Please complete your tenant configuration to continue.');
        }

        return $next($request);
    }
}

