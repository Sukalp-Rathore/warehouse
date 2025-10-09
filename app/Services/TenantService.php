<?php

namespace App\Services;

use App\Models\Master\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class TenantService
{
    public function setTenantConnection($databaseName)
    {
        Config::set('database.connections.tenant.database', $databaseName);
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
