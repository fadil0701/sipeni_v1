<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('rku_header')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE rku_header MODIFY status_rku VARCHAR(64) NOT NULL DEFAULT 'DRAFT'");
        }
    }

    public function down(): void
    {
        // Non-destructive rollback skipped — status values may already use workflow codes.
    }
};
