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

        // 2. Check client users in each client database
        $clients = Client::where('status', 'active')->get();
        \Log::info('Client check', ['clients_count' => $clients->count()]);
        
        foreach ($clients as $client) {
            // Switch to client database
            $this->tenantService->setTenantConnection($client->database_name);
            
            $clientUser = ClientUser::where('email', $email)->first();
            if ($clientUser && Hash::check($password, $clientUser->password)) {
                \Log::info('Client user authentication successful', ['client_id' => $client->client_id]);
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
