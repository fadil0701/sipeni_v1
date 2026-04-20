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
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql') {
            // MySQL: Tambahkan 'DISETUJUI' ke enum status_permintaan (jika belum ada)
            // Pastikan semua status yang diperlukan sudah ada
            DB::statement("ALTER TABLE permintaan_barang MODIFY COLUMN status_permintaan ENUM(
                'DRAFT',
                'DIAJUKAN',
                'DIKETAHUI_UNIT',
                'DIKETAHUI_TU',
                'DISETUJUI',
                'DISETUJUI_PIMPINAN',
                'DITOLAK',
                'DIDISPOSISIKAN',
                'DIPROSES',
                'SELESAI'
            ) DEFAULT 'DRAFT'");
        } else {
            // SQLite/PostgreSQL: Enum tidak didukung, hanya perlu memastikan data konsisten
            // Tidak ada perubahan yang diperlukan karena SQLite menyimpan sebagai string
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql') {
            // MySQL: Hapus 'DISETUJUI' dari enum (kembalikan ke sebelumnya)
            DB::statement("ALTER TABLE permintaan_barang MODIFY COLUMN status_permintaan ENUM(
                'DRAFT',
                'DIAJUKAN',
                'DIKETAHUI_UNIT',
                'DIKETAHUI_TU',
                'DISETUJUI_PIMPINAN',
                'DITOLAK',
                'DIDISPOSISIKAN',
                'DIPROSES',
                'SELESAI'
            ) DEFAULT 'DRAFT'");
            
            // Update semua record yang menggunakan 'DISETUJUI' menjadi 'DISETUJUI_PIMPINAN'
            DB::statement("UPDATE permintaan_barang SET status_permintaan = 'DISETUJUI_PIMPINAN' WHERE status_permintaan = 'DISETUJUI'");
        }
    }
};
