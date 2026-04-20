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
        // Ubah enum dari TAHUNAN menjadi CITO menggunakan raw SQL
        // MySQL tidak bisa langsung mengubah enum jika ada data yang tidak sesuai
        // Jadi kita ubah enum dulu, baru update data yang sudah ada
        DB::statement("ALTER TABLE `permintaan_barang` MODIFY COLUMN `tipe_permintaan` ENUM('RUTIN', 'CITO') NULL AFTER `tanggal_permintaan`");
        
        // Update data yang sudah ada: TAHUNAN -> CITO
        // Catatan: Jika ada data TAHUNAN yang masih ada, akan diubah menjadi CITO
        DB::table('permintaan_barang')
            ->where('tipe_permintaan', 'TAHUNAN')
            ->update(['tipe_permintaan' => 'CITO']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Ubah enum kembali dari CITO menjadi TAHUNAN menggunakan raw SQL
        DB::statement("ALTER TABLE `permintaan_barang` MODIFY COLUMN `tipe_permintaan` ENUM('RUTIN', 'TAHUNAN') NULL AFTER `tanggal_permintaan`");
        
        // Update data yang sudah ada: CITO -> TAHUNAN
        DB::table('permintaan_barang')
            ->where('tipe_permintaan', 'CITO')
            ->update(['tipe_permintaan' => 'TAHUNAN']);
    }
};
