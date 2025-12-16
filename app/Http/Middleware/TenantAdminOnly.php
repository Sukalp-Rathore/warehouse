<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantAdminOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        \Log::info('TenantAdminOnly middleware called', [
            'path' => $request->path(),
            'user' => session('user'),
            'authenticated' => session('authenticated'),
            'tenant_type' => session('tenant_type')
        ]);
        
        $user = session('user');
        $tenantType = session('tenant_type');
        
        // Check if user is authenticated
        if (!session('authenticated', false) || !$user) {
            \Log::warning('TenantAdminOnly: Not authenticated', ['user' => $user, 'authenticated' => session('authenticated')]);
            return redirect()->route('login')->with('error', 'Please login to access this page.');
        }
        
        // Only allow tenant admins (not master admins)
        if ($tenantType !== 'client') {
            \Log::warning('TenantAdminOnly: Not a client tenant', ['tenant_type' => $tenantType]);
            abort(403, 'Access denied. This area is only accessible to tenant administrators.');
        }
        
        // Check if user has admin role or 'all' permissions
        $userRole = $user['role'] ?? null;
        $userPermissions = $user['permissions'] ?? [];
        
        $isAdmin = false;
        
        // Check for admin role
        if ($userRole && (strtolower($userRole) === 'admin' || strtolower($userRole) === 'administrator')) {
            $isAdmin = true;
            \Log::info('TenantAdminOnly: User has admin role', ['role' => $userRole]);
        }
        
        // Check for 'all' permissions (indicates admin access)
        if (in_array('all', $userPermissions)) {
            $isAdmin = true;
            \Log::info('TenantAdminOnly: User has all permissions', ['permissions' => $userPermissions]);
        }
        
        \Log::info('TenantAdminOnly: Admin check result', [
            'isAdmin' => $isAdmin,
            'role' => $userRole,
            'permissions' => $userPermissions
        ]);
        
        if (!$isAdmin) {
            \Log::warning('TenantAdminOnly: Not an admin', ['role' => $userRole, 'permissions' => $userPermissions]);
            abort(403, 'Access denied. Tenant administrator access required.');
        }
        
        return $next($request);
    }
}
