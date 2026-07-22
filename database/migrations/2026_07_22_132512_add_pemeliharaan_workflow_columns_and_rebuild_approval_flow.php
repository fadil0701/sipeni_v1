<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('permintaan_pemeliharaan')) {
            Schema::table('permintaan_pemeliharaan', function (Blueprint $table) {
                if (! Schema::hasColumn('permintaan_pemeliharaan', 'jenis_pelaksana')) {
                    $table->string('jenis_pelaksana', 40)->nullable()->after('foto_kondisi');
                }
                if (! Schema::hasColumn('permintaan_pemeliharaan', 'id_pegawai_pelaksana')) {
                    $table->unsignedBigInteger('id_pegawai_pelaksana')->nullable()->after('jenis_pelaksana');
                }
                if (! Schema::hasColumn('permintaan_pemeliharaan', 'nama_vendor')) {
                    $table->string('nama_vendor', 255)->nullable()->after('id_pegawai_pelaksana');
                }
                if (! Schema::hasColumn('permintaan_pemeliharaan', 'disposisi_catatan')) {
                    $table->text('disposisi_catatan')->nullable()->after('nama_vendor');
                }
                if (! Schema::hasColumn('permintaan_pemeliharaan', 'rekomendasi_akhir')) {
                    $table->string('rekomendasi_akhir', 40)->nullable()->after('disposisi_catatan');
                }
            });

            $this->expandPermintaanStatusEnum();
        }

        if (Schema::hasTable('service_report')) {
            Schema::table('service_report', function (Blueprint $table) {
                if (! Schema::hasColumn('service_report', 'rekomendasi')) {
                    $table->string('rekomendasi', 40)->nullable()->after('kondisi_setelah_service');
                }
                if (! Schema::hasColumn('service_report', 'rekomendasi_catatan')) {
                    $table->text('rekomendasi_catatan')->nullable()->after('rekomendasi');
                }
            });
        }

        $this->rebuildPemeliharaanApprovalFlows();
    }

    public function down(): void
    {
        if (Schema::hasTable('service_report')) {
            Schema::table('service_report', function (Blueprint $table) {
                if (Schema::hasColumn('service_report', 'rekomendasi_catatan')) {
                    $table->dropColumn('rekomendasi_catatan');
                }
                if (Schema::hasColumn('service_report', 'rekomendasi')) {
                    $table->dropColumn('rekomendasi');
                }
            });
        }

        if (Schema::hasTable('permintaan_pemeliharaan')) {
            Schema::table('permintaan_pemeliharaan', function (Blueprint $table) {
                foreach (['rekomendasi_akhir', 'disposisi_catatan', 'nama_vendor', 'id_pegawai_pelaksana', 'jenis_pelaksana'] as $col) {
                    if (Schema::hasColumn('permintaan_pemeliharaan', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }

    private function expandPermintaanStatusEnum(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        $statuses = [
            'DRAFT', 'DIAJUKAN', 'DISETUJUI', 'DITOLAK', 'DIPROSES', 'SELESAI', 'DIBATALKAN',
            'MENUNGGU_DIKETAHUI_SR', 'MENUNGGU_PENGADAAN', 'DIKEMBALIKAN_PENGURUS',
        ];

        if ($driver === 'mysql') {
            $list = "'".implode("','", $statuses)."'";
            DB::statement("ALTER TABLE permintaan_pemeliharaan MODIFY COLUMN status_permintaan ENUM({$list}) DEFAULT 'DRAFT'");

            return;
        }

        // SQLite stores enum as CHECK — recreate column so new statuses are allowed.
        if (! Schema::hasColumn('permintaan_pemeliharaan', 'status_permintaan')) {
            return;
        }

        Schema::table('permintaan_pemeliharaan', function (Blueprint $table) {
            $table->string('status_permintaan_tmp', 40)->nullable();
        });

        DB::table('permintaan_pemeliharaan')->update([
            'status_permintaan_tmp' => DB::raw('status_permintaan'),
        ]);

        Schema::table('permintaan_pemeliharaan', function (Blueprint $table) {
            $table->dropColumn('status_permintaan');
        });

        Schema::table('permintaan_pemeliharaan', function (Blueprint $table) use ($statuses) {
            $table->enum('status_permintaan', $statuses)->default('DRAFT');
        });

        DB::table('permintaan_pemeliharaan')->update([
            'status_permintaan' => DB::raw('COALESCE(status_permintaan_tmp, \'DRAFT\')'),
        ]);

        Schema::table('permintaan_pemeliharaan', function (Blueprint $table) {
            $table->dropColumn('status_permintaan_tmp');
        });
    }

    private function rebuildPemeliharaanApprovalFlows(): void
    {
        if (! Schema::hasTable('approval_flow_definition') || ! Schema::hasTable('roles')) {
            return;
        }

        $roles = [
            'kepala_unit' => Role::where('name', 'kepala_unit')->value('id'),
            'kepala_pusat' => Role::where('name', 'kepala_pusat')->value('id'),
            'pengurus_barang' => Role::where('name', 'pengurus_barang')->value('id'),
            'pengadaan' => Role::where('name', 'pengadaan')->value('id'),
        ];

        if (! $roles['kepala_unit'] || ! $roles['kepala_pusat'] || ! $roles['pengurus_barang']) {
            return;
        }

        // Hapus flow lama (termasuk Kasubbag) — log lama tetap referensi id lama jika ada.
        DB::table('approval_flow_definition')
            ->where('modul_approval', 'PERMINTAAN_PEMELIHARAAN')
            ->delete();

        $now = now();
        $flows = [
            [1, null, 'Diajukan', 'Permintaan pemeliharaan telah diajukan', false, false],
            [2, $roles['kepala_unit'], 'Diketahui Kepala Unit', 'Kepala Unit mengetahui permintaan pemeliharaan', true, false],
            [3, $roles['kepala_pusat'], 'Disetujui Kepala Pusat', 'Kepala Pusat menyetujui dan disposisi ke Pengurus Barang', true, true],
            [4, $roles['pengurus_barang'], 'Disposisi Pengurus Barang', 'Pengurus Barang disposisi ke pelaksana (teknisi/vendor)', true, false],
            [5, null, 'Pelaksanaan Service', 'Menunggu pengerjaan dan Service Report', false, false],
            [6, $roles['pengurus_barang'], 'Diketahui SR - Pengurus Barang', 'Pengurus Barang mengetahui hasil Service Report', true, false],
            [7, $roles['kepala_unit'], 'Diketahui SR - Kepala Unit', 'Kepala Unit mengetahui hasil Service Report', true, false],
            [8, $roles['kepala_pusat'], 'Diketahui SR - Kepala Pusat', 'Kepala Pusat mengetahui hasil Service Report dan menentukan tindak lanjut', true, true],
            [9, $roles['kepala_pusat'], 'Persetujuan Pembelian', 'Kepala Pusat menyetujui pembelian spare part sesuai rekomendasi', true, true],
            [10, $roles['pengadaan'], 'Disposisi Pengadaan', 'Didisposisikan ke Pengadaan untuk pembelian', false, false],
        ];

        foreach ($flows as [$step, $roleId, $nama, $text, $canApprove, $canReject]) {
            DB::table('approval_flow_definition')->insert([
                'modul_approval' => 'PERMINTAAN_PEMELIHARAAN',
                'step_order' => $step,
                'role_id' => $roleId,
                'nama_step' => $nama,
                'status' => 'MENUNGGU',
                'status_text' => $text,
                'is_required' => in_array($step, [1, 2, 3, 4, 6, 7, 8], true),
                'can_reject' => $canReject,
                'can_approve' => $canApprove,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
};
