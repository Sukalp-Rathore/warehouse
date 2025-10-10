<?php

namespace App\Services;

use App\Models\Master\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class TenantService
{
    public function setTenantConnection($databaseName)
    {
        // Set the tenant database name
        Config::set('database.connections.tenant.database', $databaseName);
        
        // If DSN is not set, use individual connection parameters
        if (!env('TENANT_DB_DSN')) {
            Config::set('database.connections.tenant.host', env('DB_HOST', '127.0.0.1'));
            Config::set('database.connections.tenant.port', env('DB_PORT', 27017));
            Config::set('database.connections.tenant.username', env('DB_USERNAME', ''));
            Config::set('database.connections.tenant.password', env('DB_PASSWORD', ''));
            Config::set('database.connections.tenant.options', [
                'database' => env('DB_AUTHENTICATION_DATABASE', 'admin'),
            ]);
        }
        
        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    public function getClientByDatabaseName($databaseName)
    {
        return Client::where('database_name', $databaseName)->first();
    }

    public function createClientDatabase($clientId, $databaseName)
    {
        // This would typically be handled by MongoDB Atlas API
        // For now, we'll just return true
        return true;
    }
}
