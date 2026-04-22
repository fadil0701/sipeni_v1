<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_permintaan_barang', function (Blueprint $table) {
            if (!Schema::hasColumn('detail_permintaan_barang', 'qty_diminta_awal')) {
                $table->decimal('qty_diminta_awal', 15, 2)->nullable()->after('qty_diminta');
            }
            if (!Schema::hasColumn('detail_permintaan_barang', 'qty_disetujui')) {
                $table->decimal('qty_disetujui', 15, 2)->nullable()->after('qty_diminta_awal');
            }
        });

        // Backfill data existing: jika belum ada nilai, samakan dengan qty_diminta saat ini.
        DB::table('detail_permintaan_barang')
            ->whereNull('qty_diminta_awal')
            ->update(['qty_diminta_awal' => DB::raw('qty_diminta')]);

        DB::table('detail_permintaan_barang')
            ->whereNull('qty_disetujui')
            ->update(['qty_disetujui' => DB::raw('qty_diminta')]);
    }

    public function down(): void
    {
        Schema::table('detail_permintaan_barang', function (Blueprint $table) {
            if (Schema::hasColumn('detail_permintaan_barang', 'qty_disetujui')) {
                $table->dropColumn('qty_disetujui');
            }
            if (Schema::hasColumn('detail_permintaan_barang', 'qty_diminta_awal')) {
                $table->dropColumn('qty_diminta_awal');
            }
        });
    }
};

