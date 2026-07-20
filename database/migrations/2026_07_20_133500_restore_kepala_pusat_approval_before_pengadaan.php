<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Restore Kepala Pusat step for pengadaan path, and remove premature
     * "Didisposisikan ke Pengadaan" MENUNGGU logs that were created before
     * Kepala Pusat approval. Re-queue those permintaan to Kepala Pusat.
     */
    public function up(): void
    {
        $kepalaPusatRole = DB::table('roles')->where('name', 'kepala_pusat')->first();
        if ($kepalaPusatRole) {
            DB::table('approval_flow_definition')->updateOrInsert(
                [
                    'modul_approval' => 'PERMINTAAN_BARANG',
                    'step_order' => 4,
                    'role_id' => $kepalaPusatRole->id,
                ],
                [
                    'nama_step' => 'Persetujuan Kepala Pusat',
                    'status' => 'MENUNGGU',
                    'status_text' => 'Menunggu persetujuan Kepala Pusat sebelum disposisi ke Pengadaan (stok tidak tersedia)',
                    'is_required' => false,
                    'can_reject' => true,
                    'can_approve' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $pengadaanRole = DB::table('roles')->where('name', 'pengadaan')->first();
        if (! $pengadaanRole || ! $kepalaPusatRole) {
            return;
        }

        $pengadaanFlow = DB::table('approval_flow_definition')
            ->where('modul_approval', 'PERMINTAAN_BARANG')
            ->where('step_order', 4)
            ->where('role_id', $pengadaanRole->id)
            ->first();

        $kepalaPusatFlowId = DB::table('approval_flow_definition')
            ->where('modul_approval', 'PERMINTAAN_BARANG')
            ->where('step_order', 4)
            ->where('role_id', $kepalaPusatRole->id)
            ->value('id');

        if (! $pengadaanFlow || ! $kepalaPusatFlowId) {
            return;
        }

        $prematureLogs = DB::table('approval_log')
            ->where('modul_approval', 'PERMINTAAN_BARANG')
            ->where('id_approval_flow', $pengadaanFlow->id)
            ->where('status', 'MENUNGGU')
            ->get();

        $needKepalaPusat = [];

        foreach ($prematureLogs as $log) {
            $kepalaApproved = DB::table('approval_log')
                ->where('modul_approval', 'PERMINTAAN_BARANG')
                ->where('id_referensi', $log->id_referensi)
                ->where('id_approval_flow', $kepalaPusatFlowId)
                ->where('status', 'DISETUJUI')
                ->exists();

            if ($kepalaApproved) {
                continue;
            }

            DB::table('approval_log')->where('id', $log->id)->delete();
            $needKepalaPusat[(int) $log->id_referensi] = true;
        }

        // Untuk permintaan yang dialihkan ke Kepala Pusat: hapus log disposisi gudang
        // MENUNGGU yang sempat dibuat paralel (belum relevan sebelum jalur ditentukan).
        $gudangRoleIds = DB::table('roles')
            ->whereIn('name', [
                'admin_gudang_aset',
                'admin_gudang_persediaan',
                'admin_gudang_farmasi',
                'admin_gudang_pusat',
                'admin_gudang',
            ])
            ->pluck('id')
            ->all();

        if ($gudangRoleIds !== [] && $needKepalaPusat !== []) {
            $gudangFlowIds = DB::table('approval_flow_definition')
                ->where('modul_approval', 'PERMINTAAN_BARANG')
                ->where('step_order', 4)
                ->whereIn('role_id', $gudangRoleIds)
                ->pluck('id')
                ->all();

            if ($gudangFlowIds !== []) {
                DB::table('approval_log')
                    ->where('modul_approval', 'PERMINTAAN_BARANG')
                    ->whereIn('id_referensi', array_keys($needKepalaPusat))
                    ->whereIn('id_approval_flow', $gudangFlowIds)
                    ->where('status', 'MENUNGGU')
                    ->delete();
            }
        }

        $now = now();
        foreach (array_keys($needKepalaPusat) as $idReferensi) {
            $exists = DB::table('approval_log')
                ->where('modul_approval', 'PERMINTAAN_BARANG')
                ->where('id_referensi', $idReferensi)
                ->where('id_approval_flow', $kepalaPusatFlowId)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('approval_log')->insert([
                'modul_approval' => 'PERMINTAAN_BARANG',
                'id_referensi' => $idReferensi,
                'id_approval_flow' => $kepalaPusatFlowId,
                'user_id' => null,
                'role_id' => $kepalaPusatRole->id,
                'status' => 'MENUNGGU',
                'catatan' => 'Menunggu persetujuan Kepala Pusat karena stok tidak tersedia (dirapikan otomatis)',
                'approved_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        $kepalaPusatRole = DB::table('roles')->where('name', 'kepala_pusat')->first();
        if (! $kepalaPusatRole) {
            return;
        }

        DB::table('approval_flow_definition')
            ->where('modul_approval', 'PERMINTAAN_BARANG')
            ->where('step_order', 4)
            ->where('role_id', $kepalaPusatRole->id)
            ->delete();
    }
};
