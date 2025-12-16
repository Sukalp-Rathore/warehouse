<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\MultiTenantAuthService;
use App\Services\TenantService;
use App\Helpers\PermissionHelper;
use App\Models\Master\AdminUser;
use App\Models\Client\User as ClientUser;
use App\Models\Client\Plant;
use App\Models\Client\Warehouse;
use App\Models\MultiTenantUser;

class LoginController extends Controller
{
    protected $multiTenantAuth;
    protected $tenantService;

    public function __construct(MultiTenantAuthService $multiTenantAuth, TenantService $tenantService)
    {
        $this->multiTenantAuth = $multiTenantAuth;
        $this->tenantService = $tenantService;
    }

    public function showLoginForm()
    {
        return view('Auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $authResult = $this->multiTenantAuth->authenticate(
            $request->email,
            $request->password
        );

        \Log::info('Authentication attempt', [
            'email' => $request->email,
            'auth_result' => $authResult ? 'success' : 'failed'
        ]);

        if ($authResult) {
            // Store user data directly in session instead of using Auth::login()
            if ($authResult['tenant_type'] === 'master') {
                $multiTenantUser = MultiTenantUser::fromAdminUser($authResult['user']);
                $sessionUserData = $multiTenantUser->toArray();
                $sessionUserData['_id'] = $multiTenantUser->getKey();
                
                session([
                    'authenticated' => true,
                    'user' => $sessionUserData,
                    'tenant_type' => 'master',
                    'user_id' => $multiTenantUser->getKey()
                ]);
            } else {
                $clientUser = $authResult['user'];
                
                // Ensure role is loaded
                if (!$clientUser->relationLoaded('role') && $clientUser->role_id) {
                    $clientUser->load('role');
                }
                
                $multiTenantUser = MultiTenantUser::fromClientUser(
                    $clientUser, 
                    $authResult['client_id'], 
                    $authResult['database_name']
                );
                
                // Debug log to check role
                \Log::info('MultiTenantUser created', [
                    'role' => $multiTenantUser->role,
                    'user_array' => $multiTenantUser->toArray()
                ]);
                
                $sessionUserData = $multiTenantUser->toArray();
                $sessionUserData['_id'] = $multiTenantUser->getKey();

                session([
                    'authenticated' => true,
                    'user' => $sessionUserData,
                    'tenant_type' => 'client',
                    'client_id' => $authResult['client_id'],
                    'database_name' => $authResult['database_name'],
                    'user_id' => $multiTenantUser->getKey()
                ]);
            }

            // Debug: Log the authentication
            \Log::info('Login successful', [
                'user_id' => $multiTenantUser->getKey(),
                'tenant_type' => $authResult['tenant_type'],
                'session_authenticated' => session('authenticated'),
                'session_id' => session()->getId(),
                'session_data' => session()->all()
            ]);

            // Force session save
            session()->save();

            // For client users, handle plant/warehouse selection
            if ($authResult['tenant_type'] === 'client') {
                // Get role from session user array (more reliable)
                $userSession = session('user');
                $userRole = strtolower(trim($userSession['role'] ?? $multiTenantUser->role ?? ''));
                $isTenantAdmin = in_array($userRole, ['admin', 'administrator']);
                
                \Log::info('Checking tenant admin status', [
                    'role_from_multiTenantUser' => $multiTenantUser->role,
                    'role_from_session' => $userSession['role'] ?? 'not_set',
                    'userRole_lowercase' => $userRole,
                    'isTenantAdmin' => $isTenantAdmin,
                    'full_user_session' => $userSession
                ]);

                if ($isTenantAdmin) {
                    \Log::info('Tenant admin detected - bypassing plant/warehouse selection');
                    // Tenant admins bypass plant/warehouse selection and access directly
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Login successful',
                            'redirect' => route('dashboard')
                        ]);
                    }

                    return redirect()->intended('/dashboard');
                }

                // Set tenant connection to get user's access
                $this->tenantService->setTenantConnection($authResult['database_name']);
                
                // Get user's accessible plants and warehouses
                $user = ClientUser::find($multiTenantUser->getKey());
                if ($user) {
                    $plants = PermissionHelper::getUserAccessiblePlants($user);
                    $warehouses = PermissionHelper::getUserAccessibleWarehouses($user);
                    
                    // If user has only one plant and one warehouse, auto-select
                    if ($plants->count() === 1 && $warehouses->count() === 1) {
                        $plant = $plants->first();
                        $warehouse = $warehouses->first();
                        
                        // Verify warehouse belongs to plant
                        if ($warehouse->plant_id === $plant->_id) {
                            session([
                                'selected_plant_id' => $plant->_id,
                                'selected_plant_name' => $plant->plant_name,
                                'selected_warehouse_id' => $warehouse->_id,
                                'selected_warehouse_name' => $warehouse->warehouse_name,
                            ]);
                            session()->save();
                            
                            // Check if request is AJAX
                            if ($request->ajax()) {
                                return response()->json([
                                    'success' => true,
                                    'message' => 'Login successful',
                                    'redirect' => route('dashboard')
                                ]);
                            }
                            
                            return redirect()->intended('/dashboard');
                        }
                    }
                    
                    // Multiple plants/warehouses or mismatch - redirect to selection
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Login successful',
                            'redirect' => route('plant-warehouse.select')
                        ]);
                    }
                    
                    return redirect()->route('plant-warehouse.select');
                }
            }

            // For master admin or if something went wrong, go to dashboard
            // Check if request is AJAX
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'redirect' => route('dashboard')
                ]);
            }

            return redirect()->intended('/dashboard');
        }

        // Check if request is AJAX
        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'The provided credentials do not match our records.',
                'errors' => [
                    'email' => ['The provided credentials do not match our records.']
                ]
            ], 422);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login');
    }
}
