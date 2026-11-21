<?php

namespace App\Console\Commands;

use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Console\Command;

class SyncRolesCommand extends Command
{
    protected $signature = 'roles:sync';

    protected $description = 'Create or update permissions and roles without seeding demo users';

    public function handle(): int
    {
        app(RolesAndPermissionsSeeder::class)->seedPermissionsAndRoles();

        $this->info('Permissions and roles synchronized.');

        return self::SUCCESS;
    }
}
