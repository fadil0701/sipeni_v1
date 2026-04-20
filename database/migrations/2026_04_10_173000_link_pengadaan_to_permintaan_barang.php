<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pengadaan_paket')) {
            return;
        }

        Schema::table('pengadaan_paket', function (Blueprint $table) {
            if (! Schema::hasColumn('pengadaan_paket', 'id_permintaan')) {
                $table->foreignId('id_permintaan')
                    ->nullable()
                    ->after('id_paket')
                    ->constrained('permintaan_barang', 'id_permintaan')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pengadaan_paket') || ! Schema::hasColumn('pengadaan_paket', 'id_permintaan')) {
            return;
        }

        Schema::table('pengadaan_paket', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_permintaan');
        });
    }
};
