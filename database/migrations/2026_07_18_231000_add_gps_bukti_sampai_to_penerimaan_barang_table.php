<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penerimaan_barang', function (Blueprint $table) {
            if (! Schema::hasColumn('penerimaan_barang', 'sumber_bukti_sampai')) {
                $table->string('sumber_bukti_sampai', 20)->nullable()->after('foto_bukti_sampai');
            }
            if (! Schema::hasColumn('penerimaan_barang', 'gps_latitude')) {
                $table->decimal('gps_latitude', 10, 7)->nullable()->after('sumber_bukti_sampai');
            }
            if (! Schema::hasColumn('penerimaan_barang', 'gps_longitude')) {
                $table->decimal('gps_longitude', 10, 7)->nullable()->after('gps_latitude');
            }
            if (! Schema::hasColumn('penerimaan_barang', 'gps_akurasi')) {
                $table->decimal('gps_akurasi', 8, 2)->nullable()->after('gps_longitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('penerimaan_barang', function (Blueprint $table) {
            foreach (['gps_akurasi', 'gps_longitude', 'gps_latitude', 'sumber_bukti_sampai'] as $col) {
                if (Schema::hasColumn('penerimaan_barang', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
