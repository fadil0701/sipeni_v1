<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql') {
            // MySQL: Use MODIFY COLUMN
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
        } else {
            // SQLite: SQLite doesn't support ALTER COLUMN for enum
            // We'll use a workaround: add new column, copy data, drop old, rename new
            // But for simplicity, we'll just update existing data to match new values
            // Note: SQLite stores enum as string, so we just need to ensure data consistency
            DB::statement("UPDATE permintaan_barang SET status_permintaan = 'DRAFT' WHERE status_permintaan NOT IN (
                'DRAFT', 'DIAJUKAN', 'DIKETAHUI_UNIT', 'DIKETAHUI_TU', 
                'DISETUJUI_PIMPINAN', 'DITOLAK', 'DIDISPOSISIKAN', 'DIPROSES', 'SELESAI'
            )");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql') {
            // MySQL: Revert ke status sebelumnya
            DB::statement("ALTER TABLE permintaan_barang MODIFY COLUMN status_permintaan ENUM(
                'DRAFT',
                'DIAJUKAN',
                'DISETUJUI',
                'DITOLAK'
            ) DEFAULT 'DRAFT'");
        } else {
            // SQLite/PostgreSQL: Drop and recreate column
            Schema::table('permintaan_barang', function (Blueprint $table) {
                $table->dropColumn('status_permintaan');
            });
            
            Schema::table('permintaan_barang', function (Blueprint $table) {
                $table->enum('status_permintaan', [
                    'DRAFT',
                    'DIAJUKAN',
                    'DISETUJUI',
                    'DITOLAK'
                ])->default('DRAFT')->after('jenis_permintaan');
            });
        }
    }
};
