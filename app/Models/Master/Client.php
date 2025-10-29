<?php

namespace App\Models\Master;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;

    protected $connection = 'master';
    protected $collection = 'clients';

    protected $fillable = [
        'client_id',
        'name',
        'email',
        'hashed_email',
        'database_name',
        'status',
        'subscription_plan',
        'max_warehouses',
        'max_users',
        'users',
        'record_type', // 'tenant' or 'user'
        'user_id',     // reference to tenant users._id when record_type='user'
    ];

    protected $casts = [
        'max_warehouses' => 'integer',
        'max_users' => 'integer',
        'users' => 'array',
    ];

    public function isActive()
    {
        return $this->status === 'active';
    }

    // Scopes to distinguish records
    public function scopeTenants($q)
    {
        return $q->where(function($query) {
            $query->whereNull('record_type')->orWhere('record_type', 'tenant');
        });
    }

    public function scopeUsers($q)
    {
        return $q->where('record_type', 'user');
    }

    /**
     * Add a user to the clients collection (legacy method - now creates separate records)
     */
    public function addUser($userId, $email, $status = 'active')
    {
        // This method is now deprecated - we create separate records instead
        \Log::warning('addUser method called - should use separate user records instead');
        return $this;
    }

    /**
     * Remove a user from the clients collection (legacy method - now deletes separate records)
     */
    public function removeUser($userId)
    {
        // This method is now deprecated - we delete separate records instead
        \Log::warning('removeUser method called - should delete separate user records instead');
        return $this;
    }

    /**
     * Find user by email in the clients collection
     */
    public function findUserByEmail($email)
    {
        return self::where('email', $email)
                   ->where('record_type', 'user')
                   ->first();
    }

    /**
     * Find user by hashed email in the clients collection
     */
    public function findUserByHashedEmail($hashedEmail)
    {
        return self::where('hashed_email', $hashedEmail)
                   ->where('record_type', 'user')
                   ->first();
    }
}
