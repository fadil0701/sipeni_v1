<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class ApprovalFlowDefinitionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'kepala_unit' => Role::where('name', 'kepala_unit')->first(),
            'kasubbag_tu' => Role::where('name', 'kasubbag_tu')->first(),
            'kepala_pusat' => Role::where('name', 'kepala_pusat')->first(),
            'admin_gudang_pusat' => Role::where('name', 'admin_gudang_pusat')->first(),
            'admin_gudang_aset' => Role::where('name', 'admin_gudang_aset')->first(),
            'admin_gudang_persediaan' => Role::where('name', 'admin_gudang_persediaan')->first(),
            'admin_gudang_farmasi' => Role::where('name', 'admin_gudang_farmasi')->first(),
            'pengadaan' => Role::where('name', 'pengadaan')->first(),
            'pengurus_barang' => Role::where('name', 'pengurus_barang')->first(),
        ];

        $flowDefinitions = [
            // Step 1: Admin unit mengajukan permintaan
            [
                'modul_approval' => 'PERMINTAAN_BARANG',
                'step_order' => 1,
                'role_id' => null,
                'nama_step' => 'Diajukan',
                'status' => 'MENUNGGU',
                'status_text' => 'Permintaan telah diajukan oleh admin unit',
                'is_required' => true,
                'can_reject' => false,
                'can_approve' => false,
            ],

            // Step 2: Kepala Unit mengetahui
            [
                'modul_approval' => 'PERMINTAAN_BARANG',
                'step_order' => 2,
                'role_id' => $roles['kepala_unit']->id ?? null,
                'nama_step' => 'Diketahui Unit',
                'status' => 'MENUNGGU',
                'status_text' => 'Kepala Unit telah mengetahui permintaan',
                'is_required' => true,
                'can_reject' => false,
                'can_approve' => false,
            ],

            // Step 3: Kasubbag TU verifikasi dan disposisi
            [
                'modul_approval' => 'PERMINTAAN_BARANG',
                'step_order' => 3,
                'role_id' => $roles['kasubbag_tu']->id ?? null,
                'nama_step' => 'Verifikasi dan Disposisi',
                'status' => 'MENUNGGU',
                'status_text' => 'Kasubbag TU memverifikasi, menyetujui, dan melakukan disposisi ke Admin Gudang/Pengurus Barang',
                'is_required' => true,
                'can_reject' => true,
                'can_approve' => true,
            ],

            // Step 4a: Persetujuan Kepala Pusat (hanya jika stok kosong / perlu pengadaan)
            [
                'modul_approval' => 'PERMINTAAN_BARANG',
                'step_order' => 4,
                'role_id' => $roles['kepala_pusat']->id ?? null,
                'nama_step' => 'Persetujuan Kepala Pusat',
                'status' => 'MENUNGGU',
                'status_text' => 'Menunggu persetujuan Kepala Pusat sebelum disposisi ke Pengadaan (stok tidak tersedia)',
                'is_required' => false,
                'can_reject' => true,
                'can_approve' => true,
            ],

            // Step 4b: Disposisi ke Admin Gudang berdasarkan kategori (jika stok tersedia)
            [
                'modul_approval' => 'PERMINTAAN_BARANG',
                'step_order' => 4,
                'role_id' => $roles['admin_gudang_aset']->id ?? null,
                'nama_step' => 'Didisposisikan - ASET',
                'status' => 'MENUNGGU',
                'status_text' => 'Permintaan telah didisposisikan ke Admin Gudang Aset',
                'is_required' => false,
                'can_reject' => false,
                'can_approve' => false,
            ],
            [
                'modul_approval' => 'PERMINTAAN_BARANG',
                'step_order' => 4,
                'role_id' => $roles['admin_gudang_persediaan']->id ?? null,
                'nama_step' => 'Didisposisikan - PERSEDIAAN',
                'status' => 'MENUNGGU',
                'status_text' => 'Permintaan telah didisposisikan ke Admin Gudang Persediaan',
                'is_required' => false,
                'can_reject' => false,
                'can_approve' => false,
            ],
            [
                'modul_approval' => 'PERMINTAAN_BARANG',
                'step_order' => 4,
                'role_id' => $roles['admin_gudang_farmasi']->id ?? null,
                'nama_step' => 'Didisposisikan - FARMASI',
                'status' => 'MENUNGGU',
                'status_text' => 'Permintaan telah didisposisikan ke Admin Gudang Farmasi',
                'is_required' => false,
                'can_reject' => false,
                'can_approve' => false,
            ],
            [
                'modul_approval' => 'PERMINTAAN_BARANG',
                'step_order' => 4,
                'role_id' => $roles['admin_gudang_pusat']->id ?? null,
                'nama_step' => 'Didisposisikan',
                'status' => 'MENUNGGU',
                'status_text' => 'Permintaan telah didisposisikan ke Admin Gudang / Unit Terkait',
                'is_required' => false,
                'can_reject' => false,
                'can_approve' => false,
            ],
            // Step 4c: Disposisi pengadaan (hanya setelah Kepala Pusat menyetujui, jika stok kosong)
            [
                'modul_approval' => 'PERMINTAAN_BARANG',
                'step_order' => 4,
                'role_id' => $roles['pengadaan']->id ?? null,
                'nama_step' => 'Didisposisikan ke Pengadaan',
                'status' => 'MENUNGGU',
                'status_text' => 'Setelah disetujui Kepala Pusat, didisposisikan ke Pengadaan karena stok tidak tersedia',
                'is_required' => false,
                'can_reject' => false,
                'can_approve' => false,
            ],

            // Step 5: Diproses oleh Admin Gudang
            [
                'modul_approval' => 'PERMINTAAN_BARANG',
                'step_order' => 5,
                'role_id' => $roles['admin_gudang_pusat']->id ?? null,
                'nama_step' => 'Diproses',
                'status' => 'MENUNGGU',
                'status_text' => 'Admin Gudang sedang memproses distribusi barang',
                'is_required' => true,
                'can_reject' => false,
                'can_approve' => false,
            ],

            // ===================== PERMINTAAN PEMELIHARAAN =====================
            [
                'modul_approval' => 'PERMINTAAN_PEMELIHARAAN',
                'step_order' => 1,
                'role_id' => null,
                'nama_step' => 'Diajukan',
                'status' => 'MENUNGGU',
                'status_text' => 'Permintaan pemeliharaan telah diajukan',
                'is_required' => true,
                'can_reject' => false,
                'can_approve' => false,
            ],
            [
                'modul_approval' => 'PERMINTAAN_PEMELIHARAAN',
                'step_order' => 2,
                'role_id' => $roles['kepala_unit']->id ?? null,
                'nama_step' => 'Diketahui Kepala Unit',
                'status' => 'MENUNGGU',
                'status_text' => 'Kepala Unit mengetahui permintaan pemeliharaan',
                'is_required' => true,
                'can_reject' => false,
                'can_approve' => true,
            ],
            [
                'modul_approval' => 'PERMINTAAN_PEMELIHARAAN',
                'step_order' => 3,
                'role_id' => $roles['kepala_pusat']->id ?? null,
                'nama_step' => 'Disetujui Kepala Pusat',
                'status' => 'MENUNGGU',
                'status_text' => 'Kepala Pusat menyetujui dan disposisi ke Pengurus Barang',
                'is_required' => true,
                'can_reject' => true,
                'can_approve' => true,
            ],
            [
                'modul_approval' => 'PERMINTAAN_PEMELIHARAAN',
                'step_order' => 4,
                'role_id' => $roles['pengurus_barang']->id ?? null,
                'nama_step' => 'Disposisi Pengurus Barang',
                'status' => 'MENUNGGU',
                'status_text' => 'Pengurus Barang disposisi ke pelaksana (teknisi/vendor)',
                'is_required' => true,
                'can_reject' => false,
                'can_approve' => true,
            ],
            [
                'modul_approval' => 'PERMINTAAN_PEMELIHARAAN',
                'step_order' => 5,
                'role_id' => null,
                'nama_step' => 'Pelaksanaan Service',
                'status' => 'MENUNGGU',
                'status_text' => 'Menunggu pengerjaan dan Service Report',
                'is_required' => false,
                'can_reject' => false,
                'can_approve' => false,
            ],
            [
                'modul_approval' => 'PERMINTAAN_PEMELIHARAAN',
                'step_order' => 6,
                'role_id' => $roles['pengurus_barang']->id ?? null,
                'nama_step' => 'Diketahui SR - Pengurus Barang',
                'status' => 'MENUNGGU',
                'status_text' => 'Pengurus Barang mengetahui hasil Service Report',
                'is_required' => true,
                'can_reject' => false,
                'can_approve' => true,
            ],
            [
                'modul_approval' => 'PERMINTAAN_PEMELIHARAAN',
                'step_order' => 7,
                'role_id' => $roles['kepala_unit']->id ?? null,
                'nama_step' => 'Diketahui SR - Kepala Unit',
                'status' => 'MENUNGGU',
                'status_text' => 'Kepala Unit mengetahui hasil Service Report',
                'is_required' => true,
                'can_reject' => false,
                'can_approve' => true,
            ],
            [
                'modul_approval' => 'PERMINTAAN_PEMELIHARAAN',
                'step_order' => 8,
                'role_id' => $roles['kepala_pusat']->id ?? null,
                'nama_step' => 'Diketahui SR - Kepala Pusat',
                'status' => 'MENUNGGU',
                'status_text' => 'Kepala Pusat mengetahui hasil Service Report dan menentukan tindak lanjut',
                'is_required' => true,
                'can_reject' => true,
                'can_approve' => true,
            ],
            [
                'modul_approval' => 'PERMINTAAN_PEMELIHARAAN',
                'step_order' => 9,
                'role_id' => $roles['kepala_pusat']->id ?? null,
                'nama_step' => 'Persetujuan Pembelian',
                'status' => 'MENUNGGU',
                'status_text' => 'Kepala Pusat menyetujui pembelian spare part sesuai rekomendasi',
                'is_required' => false,
                'can_reject' => true,
                'can_approve' => true,
            ],
            [
                'modul_approval' => 'PERMINTAAN_PEMELIHARAAN',
                'step_order' => 10,
                'role_id' => $roles['pengadaan']->id ?? null,
                'nama_step' => 'Disposisi Pengadaan',
                'status' => 'MENUNGGU',
                'status_text' => 'Didisposisikan ke Pengadaan untuk pembelian',
                'is_required' => false,
                'can_reject' => false,
                'can_approve' => false,
            ],
        ];

        // Rebuild pemeliharaan: hapus lama lalu insert baru.
        DB::table('approval_flow_definition')
            ->where('modul_approval', 'PERMINTAAN_PEMELIHARAAN')
            ->delete();

        foreach ($flowDefinitions as $flow) {
            if ($flow['modul_approval'] === 'PERMINTAAN_PEMELIHARAAN') {
                DB::table('approval_flow_definition')->insert($flow);
                continue;
            }

            DB::table('approval_flow_definition')->updateOrInsert(
                [
                    'modul_approval' => $flow['modul_approval'],
                    'step_order' => $flow['step_order'],
                    'role_id' => $flow['role_id'],
                ],
                $flow
            );
        }
    }
}
