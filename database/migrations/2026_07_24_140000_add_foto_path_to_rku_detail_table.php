<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('rku_detail')) {
            return;
        }

        Schema::table('rku_detail', function (Blueprint $table) {
            if (! Schema::hasColumn('rku_detail', 'foto_path')) {
                $table->string('foto_path', 500)->nullable()->after('keterangan');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('rku_detail') || ! Schema::hasColumn('rku_detail', 'foto_path')) {
            return;
        }

        Schema::table('rku_detail', function (Blueprint $table) {
            $table->dropColumn('foto_path');
        });
    }
};
