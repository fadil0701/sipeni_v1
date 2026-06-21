<?php

namespace App\Console\Commands;

use App\Support\Rbac\CanonicalRoleCatalog;
use Illuminate\Console\Command;
use Spatie\Permission\PermissionRegistrar;

class SeedSystemRoles extends Command
{
    protected $signature = 'app:seed-system-roles';

    protected $description = 'Ensure canonical system roles exist and are marked protected';

    public function handle(): int
    {
        $count = CanonicalRoleCatalog::upsertAll();

        foreach (array_keys(CanonicalRoleCatalog::definitions()) as $roleName) {
            $this->line('✓ '.$roleName);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->info("System roles seeded ({$count} role kanonik, flags is_system_role / is_protected).");

        return self::SUCCESS;
    }
}
