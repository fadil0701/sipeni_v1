<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE approval_log MODIFY COLUMN status ENUM('MENUNGGU', 'DIKETAHUI', 'DIVERIFIKASI', 'DISETUJUI', 'DITOLAK', 'DIDISPOSISIKAN', 'DIPROSES', 'SELESAI') DEFAULT 'MENUNGGU'");
        } elseif (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE approval_log DROP CONSTRAINT IF EXISTS approval_log_status_check');
            DB::statement("ALTER TABLE approval_log ADD CONSTRAINT approval_log_status_check CHECK (status IN ('MENUNGGU', 'DIKETAHUI', 'DIVERIFIKASI', 'DISETUJUI', 'DITOLAK', 'DIDISPOSISIKAN', 'DIPROSES', 'SELESAI'))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::table('approval_log')->where('status', 'SELESAI')->update(['status' => 'DIPROSES']);
            DB::statement("ALTER TABLE approval_log MODIFY COLUMN status ENUM('MENUNGGU', 'DIKETAHUI', 'DIVERIFIKASI', 'DISETUJUI', 'DITOLAK', 'DIDISPOSISIKAN', 'DIPROSES') DEFAULT 'MENUNGGU'");
        } elseif (DB::getDriverName() === 'pgsql') {
            DB::table('approval_log')->where('status', 'SELESAI')->update(['status' => 'DIPROSES']);
            DB::statement('ALTER TABLE approval_log DROP CONSTRAINT IF EXISTS approval_log_status_check');
            DB::statement("ALTER TABLE approval_log ADD CONSTRAINT approval_log_status_check CHECK (status IN ('MENUNGGU', 'DIKETAHUI', 'DIVERIFIKASI', 'DISETUJUI', 'DITOLAK', 'DIDISPOSISIKAN', 'DIPROSES'))");
        }
    }
};
