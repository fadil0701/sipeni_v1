<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE penerimaan_barang MODIFY COLUMN status_penerimaan ENUM('MENUNGGU_VERIFIKASI', 'DITERIMA', 'DITOLAK') NOT NULL DEFAULT 'MENUNGGU_VERIFIKASI'");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("UPDATE penerimaan_barang SET status_penerimaan = 'DITERIMA' WHERE status_penerimaan = 'MENUNGGU_VERIFIKASI'");
        DB::statement("ALTER TABLE penerimaan_barang MODIFY COLUMN status_penerimaan ENUM('DITERIMA', 'DITOLAK') NOT NULL DEFAULT 'DITERIMA'");
    }
};
