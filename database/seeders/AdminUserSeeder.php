<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Master\AdminUser;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        $adminUsers = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@wms.com',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
            ],
            [
                'name' => 'Master Admin',
                'email' => 'admin@wms.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ],
        ];

        foreach ($adminUsers as $userData) {
            $existingUser = AdminUser::where('email', $userData['email'])->first();
            
            if (!$existingUser) {
                AdminUser::create($userData);
                $this->command->info("Created admin user: {$userData['email']}");
            } else {
                $this->command->info("Admin user already exists: {$userData['email']}");
            }
        }
    }
}
