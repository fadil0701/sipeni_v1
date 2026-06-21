<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('rku_header')) {
            return;
        }

        // Mapping nilai lama agar tetap valid pada enum baru.
        DB::table('rku_header')
            ->where('jenis_rku', 'ASET')
            ->update(['jenis_rku' => 'MODAL']);

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("
                ALTER TABLE `rku_header`
                MODIFY COLUMN `jenis_rku` ENUM('BARANG', 'JASA', 'MODAL') NOT NULL DEFAULT 'BARANG'
            ");
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('rku_header')) {
            return;
        }

        DB::table('rku_header')
            ->where('jenis_rku', 'MODAL')
            ->update(['jenis_rku' => 'ASET']);

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("
                ALTER TABLE `rku_header`
                MODIFY COLUMN `jenis_rku` ENUM('BARANG', 'ASET') NOT NULL DEFAULT 'BARANG'
            ");
        }
    }
};
