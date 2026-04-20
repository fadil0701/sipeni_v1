<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ubah enum menjadi VARCHAR sementara untuk menghindari masalah dengan data yang sudah ada
        DB::statement("ALTER TABLE `permintaan_barang` MODIFY COLUMN `tipe_permintaan` VARCHAR(20) NULL AFTER `tanggal_permintaan`");
        
        // Update data yang sudah ada: TAHUNAN -> CITO
        DB::table('permintaan_barang')
            ->where('tipe_permintaan', 'TAHUNAN')
            ->update(['tipe_permintaan' => 'CITO']);
        
        // Ubah kembali menjadi enum dengan nilai baru
        DB::statement("ALTER TABLE `permintaan_barang` MODIFY COLUMN `tipe_permintaan` ENUM('RUTIN', 'CITO') NULL AFTER `tanggal_permintaan`");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Ubah enum menjadi VARCHAR sementara
        DB::statement("ALTER TABLE `permintaan_barang` MODIFY COLUMN `tipe_permintaan` VARCHAR(20) NULL AFTER `tanggal_permintaan`");
        
        // Update data yang sudah ada: CITO -> TAHUNAN
        DB::table('permintaan_barang')
            ->where('tipe_permintaan', 'CITO')
            ->update(['tipe_permintaan' => 'TAHUNAN']);
        
        // Ubah kembali menjadi enum dengan nilai lama
        DB::statement("ALTER TABLE `permintaan_barang` MODIFY COLUMN `tipe_permintaan` ENUM('RUTIN', 'TAHUNAN') NULL AFTER `tanggal_permintaan`");
    }
};
