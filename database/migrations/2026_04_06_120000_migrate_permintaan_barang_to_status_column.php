<?php

use App\Enums\PermintaanBarangStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('permintaan_barang')) {
            return;
        }

        if (Schema::hasColumn('permintaan_barang', 'status')) {
            return;
        }

        Schema::table('permintaan_barang', function (Blueprint $table) {
            $table->string('status', 40)->default(PermintaanBarangStatus::Draft->value);
        });

        if (Schema::hasColumn('permintaan_barang', 'status_permintaan')) {
            $rows = DB::table('permintaan_barang')->select('id_permintaan', 'status_permintaan')->get();
            foreach ($rows as $row) {
                $canonical = PermintaanBarangStatus::fromLegacy($row->status_permintaan ?? null)->value;
                DB::table('permintaan_barang')
                    ->where('id_permintaan', $row->id_permintaan)
                    ->update(['status' => $canonical]);
            }

            Schema::table('permintaan_barang', function (Blueprint $table) {
                $table->dropColumn('status_permintaan');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('permintaan_barang')) {
            return;
        }

        if (! Schema::hasColumn('permintaan_barang', 'status')) {
            return;
        }

        Schema::table('permintaan_barang', function (Blueprint $table) {
            $table->string('status_permintaan', 40)->default('DRAFT');
        });

        $reverse = [
            'draft' => 'DRAFT',
            'diajukan' => 'DIAJUKAN',
            'diverifikasi' => 'DISETUJUI',
            'ditolak' => 'DITOLAK',
            'menunggu_pengadaan' => 'DISETUJUI_PIMPINAN',
            'proses_pengadaan' => 'DIPROSES',
            'barang_tersedia' => 'DIPROSES',
            'proses_distribusi' => 'DIDISPOSISIKAN',
            'dikirim' => 'DIPROSES',
            'diterima' => 'DIPROSES',
            'selesai' => 'SELESAI',
        ];

        $rows = DB::table('permintaan_barang')->select('id_permintaan', 'status')->get();
        foreach ($rows as $row) {
            $legacy = $reverse[$row->status] ?? 'DRAFT';
            DB::table('permintaan_barang')
                ->where('id_permintaan', $row->id_permintaan)
                ->update(['status_permintaan' => $legacy]);
        }

        Schema::table('permintaan_barang', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
