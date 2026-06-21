<?php

use App\Helpers\PermissionHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration
{
    /**
     * Seed standard format permissions (module.action) dan assign ke canonical roles.
     */
    public function up(): void
    {
        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\StandardPermissionSeeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\StandardRolePermissionV2Seeder',
            '--force' => true,
        ]);

        PermissionHelper::bumpAccessibleMenusCacheGeneration();
    }

    /**
     * Tidak perlu rollback — permission baru bersifat additive (updateOrCreate).
     */
    public function down(): void
    {
        // Tidak ada rollback: standard permissions bersifat additive
    }
};
