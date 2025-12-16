<?php

namespace App\Services;

use App\Models\Master\AdminUser;
use App\Models\Master\Client;
use App\Models\Client\User as ClientUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class MultiTenantAuthService
{
    protected $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    public function authenticate($email, $password)
    {
        \Log::info('MultiTenantAuthService authenticate', ['email' => $email]);
        
        // 1. Check master admin users first
        $adminUser = AdminUser::where('email', $email)->first();
        \Log::info('Admin user check', ['found' => $adminUser ? 'yes' : 'no']);
        
        if ($adminUser && Hash::check($password, $adminUser->password)) {
            \Log::info('Master admin authentication successful');
            return [
                'user' => $adminUser,
                'tenant_type' => 'master',
                'client_id' => null,
                'database_name' => null
            ];
        }

        // 2. Check client users using separate user records
        $hashedEmail = hash('sha256', $email);
        
        // First, try to find a user record (record_type='user')
        $userMapping = Client::where('hashed_email', $hashedEmail)
                            ->where('record_type', 'user')
                            ->where('status', 'active')
                            ->first();
        
        \Log::info('User mapping check', [
            'hashed_email' => $hashedEmail,
            'user_mapping_found' => $userMapping ? 'yes' : 'no',
            'client_id' => $userMapping ? $userMapping->client_id : null
        ]);
        
        if ($userMapping) {
            // Switch to the specific client database
            $this->tenantService->setTenantConnection($userMapping->database_name);
            
            $clientUser = ClientUser::with('role')->where('email', $email)->first();
            if ($clientUser && Hash::check($password, $clientUser->password)) {
                \Log::info('Client user authentication successful', [
                    'client_id' => $userMapping->client_id,
                    'database_name' => $userMapping->database_name,
                    'user_id' => $userMapping->user_id,
                    'role' => $clientUser->role ? $clientUser->role->name : null
                ]);
                return [
                    'user' => $clientUser,
                    'tenant_type' => 'client',
                    'client_id' => $userMapping->client_id,
                    'database_name' => $userMapping->database_name
                ];
            }
        }
        
        // 3. Fallback: Check legacy tenant-level mapping (for existing admin users)
        $client = Client::where('hashed_email', $hashedEmail)
                       ->where(function($q) {
                           $q->whereNull('record_type')->orWhere('record_type', 'tenant');
                       })
                       ->where('status', 'active')
                       ->first();
        
        \Log::info('Legacy tenant mapping check', [
            'hashed_email' => $hashedEmail,
            'tenant_found' => $client ? 'yes' : 'no',
            'client_id' => $client ? $client->client_id : null
        ]);
        
        if ($client) {
            // Switch to the specific client database
            $this->tenantService->setTenantConnection($client->database_name);
            
            $clientUser = ClientUser::with('role')->where('email', $email)->first();
            if ($clientUser && Hash::check($password, $clientUser->password)) {
                \Log::info('Legacy tenant user authentication successful', [
                    'client_id' => $client->client_id,
                    'database_name' => $client->database_name,
                    'role' => $clientUser->role ? $clientUser->role->name : null
                ]);
                return [
                    'user' => $clientUser,
                    'tenant_type' => 'client',
                    'client_id' => $client->client_id,
                    'database_name' => $client->database_name
                ];
            }
        }

        \Log::info('Authentication failed for email', ['email' => $email]);
        return null;
    }
}
