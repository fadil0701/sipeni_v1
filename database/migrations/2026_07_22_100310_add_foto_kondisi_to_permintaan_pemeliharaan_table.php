<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('permintaan_pemeliharaan', function (Blueprint $table) {
            if (! Schema::hasColumn('permintaan_pemeliharaan', 'foto_kondisi')) {
                $table->string('foto_kondisi', 255)->nullable()->after('deskripsi_kerusakan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permintaan_pemeliharaan', function (Blueprint $table) {
            if (Schema::hasColumn('permintaan_pemeliharaan', 'foto_kondisi')) {
                $table->dropColumn('foto_kondisi');
            }
        });
    }
};
