<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //
    public function index()
    {
        \Log::info('Dashboard access attempt', [
            'session_authenticated' => session('authenticated'),
            'user_id' => session('user_id'),
            'session_id' => session()->getId()
        ]);

        $user = session('user');
        
        $tenantInfo = [
            'type' => session('tenant_type'),
            'client_id' => session('client_id'),
            'database' => session('database_name'),
            'user' => $user,
            'auth_check' => session('authenticated', false),
            'session_data' => session()->all()
        ];

        \Log::info('Dashboard data being passed to view', [
            'tenant_info' => $tenantInfo,
            'user_data' => $user
        ]);
        
        return view('dashboard', compact('tenantInfo'));
    }
}
