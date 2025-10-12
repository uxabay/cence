<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsRefreshCommand extends Command
{
    protected $signature = 'permissions:refresh {--seed : Reseed default roles & permissions after refresh}';
    protected $description = 'Safely reset Spatie roles/permissions tables and (optionally) reseed.';

    public function handle(): int
    {
        $this->info('Resetting roles & permissions safely...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Σωστή σειρά αδειάσματος
        DB::table('model_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('role_has_permissions')->truncate();
        DB::table('permissions')->truncate();
        DB::table('roles')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Καθαρισμός cache Spatie
        $this->callSilent('permission:cache-reset');

        $this->info('Tables truncated and cache reset.');

        if ($this->option('seed')) {
            $this->info('Seeding default roles & permissions...');
            $this->call('db:seed', ['--class' => 'Database\\Seeders\\AuthSeeder']);
            $this->info('Seeding done.');
        }

        return self::SUCCESS;
    }
}
