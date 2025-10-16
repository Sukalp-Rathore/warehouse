<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Master\Client;
use App\Services\TenantService;
use App\Models\Client\User as ClientUser;

class MigrateClientUserEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:client-user-emails {--force : Force migration without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing client user emails to the optimized hashed email structure';

    protected $tenantService;

    public function __construct(TenantService $tenantService)
    {
        parent::__construct();
        $this->tenantService = $tenantService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting migration of client user emails...');

        if (!$this->option('force')) {
            if (!$this->confirm('This will update all existing clients with user email hashes. Continue?')) {
                $this->info('Migration cancelled.');
                return;
            }
        }

        $clients = Client::where('status', 'active')->get();
        $this->info("Found {$clients->count()} active clients to migrate.");

        $bar = $this->output->createProgressBar($clients->count());
        $bar->start();

        $migratedCount = 0;
        $errorCount = 0;

        foreach ($clients as $client) {
            try {
                $this->migrateClientUserEmails($client);
                $migratedCount++;
            } catch (\Exception $e) {
                $this->error("\nError migrating client {$client->client_id}: " . $e->getMessage());
                $errorCount++;
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info("Migration completed!");
        $this->info("Successfully migrated: {$migratedCount} clients");
        if ($errorCount > 0) {
            $this->warn("Errors encountered: {$errorCount} clients");
        }
    }

    private function migrateClientUserEmails(Client $client)
    {
        // Switch to client's tenant database
        $this->tenantService->setTenantConnection($client->database_name);
        
        // Get all users from tenant database
        $tenantUsers = ClientUser::all();
        
        $userEmails = [];
        foreach ($tenantUsers as $user) {
            $userEmails[] = [
                'email_hash' => hash('sha256', strtolower($user->email)),
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status ?? 'active',
                'created_at' => $user->created_at ?? now(),
                'updated_at' => now()
            ];
        }
        
        // Update client with user emails
        $client->user_emails = $userEmails;
        $client->save();
        
        $this->line("\nClient {$client->client_id}: Migrated " . count($userEmails) . " user emails");
    }
}
