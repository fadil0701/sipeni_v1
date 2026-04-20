<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Untuk MySQL/PostgreSQL, gunakan ALTER TABLE untuk menambahkan nilai enum baru
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE approval_log MODIFY COLUMN status ENUM('MENUNGGU', 'DIKETAHUI', 'DIVERIFIKASI', 'DISETUJUI', 'DITOLAK', 'DIDISPOSISIKAN', 'DIPROSES') DEFAULT 'MENUNGGU'");
        } elseif (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE approval_log DROP CONSTRAINT IF EXISTS approval_log_status_check");
            DB::statement("ALTER TABLE approval_log ADD CONSTRAINT approval_log_status_check CHECK (status IN ('MENUNGGU', 'DIKETAHUI', 'DIVERIFIKASI', 'DISETUJUI', 'DITOLAK', 'DIDISPOSISIKAN', 'DIPROSES'))");
        } elseif (DB::getDriverName() === 'sqlite') {
            // SQLite tidak mendukung enum, jadi kita perlu drop dan recreate constraint
            // Atau cukup pastikan bahwa constraint tidak memblokir DIPROSES
            // SQLite menggunakan CHECK constraint untuk validasi
            try {
                // Drop constraint lama jika ada
                DB::statement("DROP INDEX IF EXISTS approval_log_status_check");
            } catch (\Exception $e) {
                // Ignore jika constraint tidak ada
            }
            // SQLite akan menggunakan CHECK constraint dari schema definition
            // Pastikan model/validation mengizinkan DIPROSES
        }
    }

    public function down(): void
    {
        // Kembalikan ke enum sebelumnya tanpa DIPROSES
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE approval_log MODIFY COLUMN status ENUM('MENUNGGU', 'DIKETAHUI', 'DIVERIFIKASI', 'DISETUJUI', 'DITOLAK', 'DIDISPOSISIKAN') DEFAULT 'MENUNGGU'");
        } elseif (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE approval_log DROP CONSTRAINT IF EXISTS approval_log_status_check");
            DB::statement("ALTER TABLE approval_log ADD CONSTRAINT approval_log_status_check CHECK (status IN ('MENUNGGU', 'DIKETAHUI', 'DIVERIFIKASI', 'DISETUJUI', 'DITOLAK', 'DIDISPOSISIKAN'))");
        }
    }
};
