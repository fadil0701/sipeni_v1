<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        Schema::table('roles', function (Blueprint $table): void {
            if (! Schema::hasColumn('roles', 'is_system_role')) {
                $table->boolean('is_system_role')->default(false)->after('is_active');
            }
            if (! Schema::hasColumn('roles', 'is_protected')) {
                $table->boolean('is_protected')->default(false)->after('is_system_role');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        Schema::table('roles', function (Blueprint $table): void {
            if (Schema::hasColumn('roles', 'is_protected')) {
                $table->dropColumn('is_protected');
            }
            if (Schema::hasColumn('roles', 'is_system_role')) {
                $table->dropColumn('is_system_role');
            }
        });
    }
};
