<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class ApprovalFlowDefinitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan RoleSeeder sudah dijalankan terlebih dahulu
        $roles = [
            'pegawai' => Role::where('name', 'pegawai')->first(),
            'kepala_unit' => Role::where('name', 'kepala_unit')->first(),
            'kasubbag_tu' => Role::where('name', 'kasubbag_tu')->first(),
            'kepala_pusat' => Role::where('name', 'kepala_pusat')->first(),
            'admin_gudang' => Role::where('name', 'admin_gudang')->first(),
            'admin_gudang_aset' => Role::where('name', 'admin_gudang_aset')->first(),
            'admin_gudang_persediaan' => Role::where('name', 'admin_gudang_persediaan')->first(),
            'admin_gudang_farmasi' => Role::where('name', 'admin_gudang_farmasi')->first(),
            'pengadaan' => Role::where('name', 'pengadaan')->first(),
        ];

        // Flow approval untuk PERMINTAAN_BARANG
        $flowDefinitions = [
            // Step 1: Pegawai mengajukan permintaan
            [
                'modul_approval' => 'PERMINTAAN_BARANG',
                'step_order' => 1,
                'role_id' => $roles['pegawai']->id ?? null,
                'nama_step' => 'Diajukan',
                'status' => 'MENUNGGU',
                'status_text' => 'Permintaan telah diajukan oleh pegawai',
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
                'can_reject' => false, // Kepala Unit tidak bisa reject
                'can_approve' => false, // Kepala Unit tidak bisa approve
            ],
            
            // Step 3: Kasubbag TU verifikasi dan menyetujui serta melakukan disposisi
            [
                'modul_approval' => 'PERMINTAAN_BARANG',
                'step_order' => 3,
                'role_id' => $roles['kasubbag_tu']->id ?? null,
                'nama_step' => 'Verifikasi dan Disposisi',
                'status' => 'MENUNGGU',
                'status_text' => 'Kasubbag TU memverifikasi, menyetujui, dan melakukan disposisi ke Admin Gudang/Pengurus Barang',
                'is_required' => true,
                'can_reject' => true, // Bisa mengembalikan jika tidak lengkap
                'can_approve' => true, // Bisa approve dan langsung disposisi
            ],
            
            // Step 4: Disposisi ke Admin Gudang berdasarkan kategori (akan dibuat dinamis saat verifikasi)
            // Step 4.1: Disposisi ke Admin Gudang Aset
            [
                'modul_approval' => 'PERMINTAAN_BARANG',
                'step_order' => 4,
                'role_id' => $roles['admin_gudang_aset']->id ?? null,
                'nama_step' => 'Didisposisikan - ASET',
                'status' => 'MENUNGGU',
                'status_text' => 'Permintaan telah didisposisikan ke Admin Gudang Aset',
                'is_required' => false, // Tidak wajib, hanya dibuat jika ada permintaan ASET
                'can_reject' => false,
                'can_approve' => false,
            ],
            // Step 4.2: Disposisi ke Admin Gudang Persediaan
            [
                'modul_approval' => 'PERMINTAAN_BARANG',
                'step_order' => 4,
                'role_id' => $roles['admin_gudang_persediaan']->id ?? null,
                'nama_step' => 'Didisposisikan - PERSEDIAAN',
                'status' => 'MENUNGGU',
                'status_text' => 'Permintaan telah didisposisikan ke Admin Gudang Persediaan',
                'is_required' => false, // Tidak wajib, hanya dibuat jika ada permintaan PERSEDIAAN
                'can_reject' => false,
                'can_approve' => false,
            ],
            // Step 4.3: Disposisi ke Admin Gudang Farmasi
            [
                'modul_approval' => 'PERMINTAAN_BARANG',
                'step_order' => 4,
                'role_id' => $roles['admin_gudang_farmasi']->id ?? null,
                'nama_step' => 'Didisposisikan - FARMASI',
                'status' => 'MENUNGGU',
                'status_text' => 'Permintaan telah didisposisikan ke Admin Gudang Farmasi',
                'is_required' => false, // Tidak wajib, hanya dibuat jika ada permintaan FARMASI
                'can_reject' => false,
                'can_approve' => false,
            ],
            // Step 4.4: Disposisi ke Admin Gudang Umum (fallback)
            [
                'modul_approval' => 'PERMINTAAN_BARANG',
                'step_order' => 4,
                'role_id' => $roles['admin_gudang']->id ?? null,
                'nama_step' => 'Didisposisikan',
                'status' => 'MENUNGGU',
                'status_text' => 'Permintaan telah didisposisikan ke Admin Gudang / Unit Terkait',
                'is_required' => false,
                'can_reject' => false,
                'can_approve' => false,
            ],
            // Step 4.5: Disposisi ke Pengadaan Barang dan Jasa (jika ada item tidak ada di stock)
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
                'role_id' => $roles['admin_gudang']->id ?? null,
                'nama_step' => 'Diproses',
                'status' => 'MENUNGGU',
                'status_text' => 'Admin Gudang sedang memproses distribusi barang',
                'is_required' => true,
                'can_reject' => false,
                'can_approve' => false,
            ],
            
            // Step 7: Selesai (tidak memerlukan role khusus, otomatis setelah diproses)
            // Step ini bisa di-skip karena tidak memerlukan approval dari role tertentu
        ];

        foreach ($flowDefinitions as $flow) {
            // Gunakan modul_approval, step_order, dan role_id sebagai unique key
            // karena constraint unique adalah ['modul_approval', 'step_order', 'role_id']
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
