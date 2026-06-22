<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('master_pegawai') || ! Schema::hasTable('master_jabatan')) {
            return;
        }

        if (! Schema::hasColumn('master_pegawai', 'jabatan')) {
            return;
        }

        DB::statement('
            UPDATE master_pegawai mp
            INNER JOIN master_jabatan mj ON mp.id_jabatan = mj.id_jabatan
            SET mp.jabatan = mj.nama_jabatan
            WHERE mp.id_jabatan IS NOT NULL
              AND (mp.jabatan IS NULL OR mp.jabatan = \'\')
        ');
    }

    public function down(): void
    {
        // Data backfill — tidak di-rollback.
    }
};
