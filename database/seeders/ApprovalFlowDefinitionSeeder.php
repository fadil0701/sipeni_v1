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

            // Step 4: Disposisi ke Admin Gudang berdasarkan kategori
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
            [
                'modul_approval' => 'PERMINTAAN_BARANG',
                'step_order' => 4,
                'role_id' => $roles['pengadaan']->id ?? null,
                'nama_step' => 'Didisposisikan ke Pengadaan',
                'status' => 'MENUNGGU',
                'status_text' => 'Permintaan mengandung item yang tidak ada di stock, didisposisikan ke Pengadaan Barang dan Jasa untuk pengadaan',
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
        ];

        foreach ($flowDefinitions as $flow) {
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
