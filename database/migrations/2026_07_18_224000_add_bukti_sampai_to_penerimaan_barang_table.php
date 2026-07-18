<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE penerimaan_barang MODIFY COLUMN status_penerimaan ENUM('MENUNGGU_BUKTI_SAMPAI', 'MENUNGGU_VERIFIKASI', 'DITERIMA', 'DITOLAK') NOT NULL DEFAULT 'MENUNGGU_BUKTI_SAMPAI'");
        }

        Schema::table('penerimaan_barang', function (Blueprint $table) {
            if (! Schema::hasColumn('penerimaan_barang', 'nama_penerima_lokasi')) {
                $table->string('nama_penerima_lokasi', 150)->nullable()->after('keterangan');
            }
            if (! Schema::hasColumn('penerimaan_barang', 'foto_bukti_sampai')) {
                $table->string('foto_bukti_sampai', 255)->nullable()->after('nama_penerima_lokasi');
            }
            if (! Schema::hasColumn('penerimaan_barang', 'waktu_sampai')) {
                $table->timestamp('waktu_sampai')->nullable()->after('foto_bukti_sampai');
            }
            if (! Schema::hasColumn('penerimaan_barang', 'dilapor_oleh')) {
                $table->foreignId('dilapor_oleh')->nullable()->after('waktu_sampai')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('penerimaan_barang', 'catatan_pengirim')) {
                $table->text('catatan_pengirim')->nullable()->after('dilapor_oleh');
            }
        });
    }

    public function down(): void
    {
        Schema::table('penerimaan_barang', function (Blueprint $table) {
            if (Schema::hasColumn('penerimaan_barang', 'dilapor_oleh')) {
                $table->dropConstrainedForeignId('dilapor_oleh');
            }
            foreach (['catatan_pengirim', 'waktu_sampai', 'foto_bukti_sampai', 'nama_penerima_lokasi'] as $col) {
                if (Schema::hasColumn('penerimaan_barang', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::table('penerimaan_barang')
                ->where('status_penerimaan', 'MENUNGGU_BUKTI_SAMPAI')
                ->update(['status_penerimaan' => 'MENUNGGU_VERIFIKASI']);

            DB::statement("ALTER TABLE penerimaan_barang MODIFY COLUMN status_penerimaan ENUM('MENUNGGU_VERIFIKASI', 'DITERIMA', 'DITOLAK') NOT NULL DEFAULT 'MENUNGGU_VERIFIKASI'");
        }
    }
};
