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
            if (! Schema::hasColumn('roles', 'is_deprecated')) {
                $table->boolean('is_deprecated')->default(false)->after('is_active');
            }
            if (! Schema::hasColumn('roles', 'maps_to_role')) {
                $table->string('maps_to_role')->nullable()->after('is_deprecated');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        Schema::table('roles', function (Blueprint $table): void {
            if (Schema::hasColumn('roles', 'maps_to_role')) {
                $table->dropColumn('maps_to_role');
            }
            if (Schema::hasColumn('roles', 'is_deprecated')) {
                $table->dropColumn('is_deprecated');
            }
        });
    }
};
