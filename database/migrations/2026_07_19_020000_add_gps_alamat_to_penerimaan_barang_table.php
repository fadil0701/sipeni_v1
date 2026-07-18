<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penerimaan_barang', function (Blueprint $table) {
            if (! Schema::hasColumn('penerimaan_barang', 'gps_alamat')) {
                $table->string('gps_alamat', 500)->nullable()->after('gps_akurasi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('penerimaan_barang', function (Blueprint $table) {
            if (Schema::hasColumn('penerimaan_barang', 'gps_alamat')) {
                $table->dropColumn('gps_alamat');
            }
        });
    }
};
