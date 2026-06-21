<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('rku_detail') || ! Schema::hasColumn('rku_detail', 'jenis_rku')) {
            return;
        }

        // Legacy: detail memakai ASET — selaraskan ke MODAL (sama arah migrasi header ASET → MODAL)
        DB::table('rku_detail')->where('jenis_rku', 'ASET')->update(['jenis_rku' => 'MODAL']);

        if (DB::getDriverName() === 'mysql') {
            try {
                DB::statement("ALTER TABLE `rku_detail` MODIFY COLUMN `jenis_rku` ENUM('BARANG','JASA','MODAL') NOT NULL DEFAULT 'BARANG'");
            } catch (\Throwable $e) {
                // Kolom mungkin sudah varchar / sudah diubah manual
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('rku_detail') || ! Schema::hasColumn('rku_detail', 'jenis_rku')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::table('rku_detail')->whereIn('jenis_rku', ['JASA', 'MODAL'])->update(['jenis_rku' => 'ASET']);
            try {
                DB::statement("ALTER TABLE `rku_detail` MODIFY COLUMN `jenis_rku` ENUM('BARANG','ASET') NOT NULL DEFAULT 'BARANG'");
            } catch (\Throwable $e) {
                //
            }
        }
    }
};
