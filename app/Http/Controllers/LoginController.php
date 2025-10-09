<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\MultiTenantAuthService;
use App\Models\Master\AdminUser;
use App\Models\Client\User as ClientUser;
use App\Models\MultiTenantUser;

class LoginController extends Controller
{
    protected $multiTenantAuth;

    public function __construct(MultiTenantAuthService $multiTenantAuth)
    {
        $this->multiTenantAuth = $multiTenantAuth;
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
                session([
                    'authenticated' => true,
                    'user' => $multiTenantUser->toArray(),
                    'tenant_type' => 'master',
                    'user_id' => $multiTenantUser->getKey()
                ]);
            } else {
                $multiTenantUser = MultiTenantUser::fromClientUser(
                    $authResult['user'], 
                    $authResult['client_id'], 
                    $authResult['database_name']
                );
                session([
                    'authenticated' => true,
                    'user' => $multiTenantUser->toArray(),
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
