<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Master\Client;

class AddUserEmailToClient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'client:add-user-email {client_id} {email} {role=admin} {status=active}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a user email to a specific client for optimized login';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $clientId = $this->argument('client_id');
        $email = $this->argument('email');
        $role = $this->argument('role');
        $status = $this->argument('status');

        $this->info("Adding user email to client: {$clientId}");
        $this->info("Email: {$email}");
        $this->info("Role: {$role}");
        $this->info("Status: {$status}");

        $client = Client::where('client_id', $clientId)->first();

        if (!$client) {
            $this->error("Client with ID '{$clientId}' not found!");
            return 1;
        }

        $this->info("Found client: {$client->name}");
        $this->info("Current user emails count: " . count($client->user_emails ?? []));

        try {
            $result = $client->addUserEmail($email, $role, $status);
            
            if ($result) {
                $this->info("✅ Successfully added user email!");
                $this->info("New user emails count: " . count($client->user_emails ?? []));
                
                // Show the user emails
                $this->table(
                    ['Email', 'Role', 'Status', 'Email Hash'],
                    collect($client->user_emails ?? [])->map(function ($user) {
                        return [
                            $user['email'],
                            $user['role'],
                            $user['status'],
                            substr($user['email_hash'], 0, 16) . '...'
                        ];
                    })->toArray()
                );
            } else {
                $this->error("❌ Failed to add user email!");
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
