<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class MasterAdminOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Debug logging
        \Log::info('MasterAdminOnly middleware check', [
            'authenticated' => session('authenticated'),
            'tenant_type' => session('tenant_type'),
            'user' => session('user'),
            'url' => $request->url()
        ]);

        // Check if user is authenticated
        if (!session('authenticated')) {
            \Log::warning('MasterAdminOnly: User not authenticated');
            return redirect()->route('login')->with('error', 'Please login to access this page.');
        }

        // Get user data from session
        $user = session('user');
        $tenantType = session('tenant_type');

        // Check if user data exists
        if (!$user || !is_array($user)) {
            \Log::warning('MasterAdminOnly: User data not found or invalid', [
                'user' => $user
            ]);
            return redirect()->route('dashboard')->with('error', 'Access denied. User data not found.');
        }

        // Check if user is a master admin
        if ($tenantType !== 'master') {
            \Log::warning('MasterAdminOnly: User is not master admin', [
                'tenant_type' => $tenantType,
                'user' => $user
            ]);
            return redirect()->route('dashboard')->with('error', 'Access denied. Only master administrators can manage tenants.');
        }

        // Check if user has admin or super_admin role
        if (!isset($user['role']) || !in_array($user['role'], ['admin', 'super_admin'])) {
            \Log::warning('MasterAdminOnly: User does not have admin role', [
                'user' => $user,
                'role' => $user['role'] ?? 'not_set'
            ]);
            return redirect()->route('dashboard')->with('error', 'Access denied. Insufficient permissions to manage tenants.');
        }

        \Log::info('MasterAdminOnly: Access granted', [
            'user' => $user
        ]);

        return $next($request);
    }
}
