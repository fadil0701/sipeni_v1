<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_penerimaan_barang', function (Blueprint $table) {
            if (! Schema::hasColumn('detail_penerimaan_barang', 'hasil_verifikasi')) {
                $table->string('hasil_verifikasi', 20)->nullable()->after('keterangan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('detail_penerimaan_barang', function (Blueprint $table) {
            if (Schema::hasColumn('detail_penerimaan_barang', 'hasil_verifikasi')) {
                $table->dropColumn('hasil_verifikasi');
            }
        });
    }
};
