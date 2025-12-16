<?php

namespace App\Providers;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use App\Models\MultiTenantUser;
use App\Models\Master\AdminUser;
use App\Models\Client\User as ClientUser;
use App\Models\Master\Client;
use App\Services\TenantService;

class MultiTenantUserProvider extends EloquentUserProvider
{
    protected $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
        parent::__construct(app('hash'), MultiTenantUser::class);
    }

    /**
     * Retrieve a user by their unique identifier.
     */
    public function retrieveById($identifier)
    {
        // First check if we have a MultiTenantUser in session
        $user = session('multi_tenant_user');
        if ($user && $user['_id'] == $identifier) {
            return $this->recreateUserFromSession($user);
        }

        // If not in session, we need to recreate from the original user
        // This is a fallback - ideally the user should be in session
        return null;
    }

    /**
     * Recreate MultiTenantUser from session data
     */
    protected function recreateUserFromSession($sessionUser)
    {
        $user = new MultiTenantUser();
        $user->fill($sessionUser);
        $user->_id = $sessionUser['_id'];
        $user->exists = true;
        return $user;
    }
}
