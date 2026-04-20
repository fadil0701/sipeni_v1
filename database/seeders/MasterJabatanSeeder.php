<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterJabatan;
use App\Models\Role;

class MasterJabatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan RoleSeeder sudah dijalankan terlebih dahulu
        $roles = [
            'admin' => Role::where('name', 'admin')->first(),
            'pegawai' => Role::where('name', 'pegawai')->first(),
            'kepala_unit' => Role::where('name', 'kepala_unit')->first(),
            'kasubbag_tu' => Role::where('name', 'kasubbag_tu')->first(),
            'kepala_pusat' => Role::where('name', 'kepala_pusat')->first(),
            'admin_gudang' => Role::where('name', 'admin_gudang')->first(),
            'admin_gudang_aset' => Role::where('name', 'admin_gudang_aset')->first(),
            'admin_gudang_persediaan' => Role::where('name', 'admin_gudang_persediaan')->first(),
            'admin_gudang_farmasi' => Role::where('name', 'admin_gudang_farmasi')->first(),
            'admin_gudang_unit' => Role::where('name', 'admin_gudang_unit')->first(),
            'perencanaan' => Role::where('name', 'perencanaan')->first(),
            'pengadaan' => Role::where('name', 'pengadaan')->first(),
            'keuangan' => Role::where('name', 'keuangan')->first(),
        ];

        $jabatans = [
            // 1. ADMIN SISTEM
            [
                'urutan' => 1,
                'nama_jabatan' => 'Admin IT / Pengelola Aplikasi',
                'role_id' => $roles['admin']->id ?? null,
                'deskripsi' => 'Admin Sistem - Kelola user, role, master data, konfigurasi sistem',
            ],
            
            // 2. PEGAWAI (PEMOHON) / ADMIN UNIT
            [
                'urutan' => 2,
                'nama_jabatan' => 'Admin Unit',
                'role_id' => $roles['pegawai']->id ?? null,
                'deskripsi' => 'Admin Unit / Staf Unit Kerja / Pelaksana Teknis - Membuat permintaan barang, melihat status, menerima barang',
            ],
            
            // 3. KEPALA UNIT
            [
                'urutan' => 3,
                'nama_jabatan' => 'Kepala Unit',
                'role_id' => $roles['kepala_unit']->id ?? null,
                'deskripsi' => 'Kepala Unit / Kepala Seksi / Kepala Sub Unit - Melihat permintaan dari unitnya, memberi status "Mengetahui"',
            ],
            
            // 4. KASUBBAG TU
            [
                'urutan' => 4,
                'nama_jabatan' => 'Kasubbag TU',
                'role_id' => $roles['kasubbag_tu']->id ?? null,
                'deskripsi' => 'Kasubbag TU - Verifikasi administrasi permintaan, cek kelengkapan',
            ],
            
            // 5. KEPALA PUSAT (PIMPINAN)
            [
                'urutan' => 5,
                'nama_jabatan' => 'Kepala Pusat',
                'role_id' => $roles['kepala_pusat']->id ?? null,
                'deskripsi' => 'Kepala Pusat / Kepala UPT (Pimpinan) - Approve/Reject permintaan, memberikan disposisi',
            ],
            
            // 6. ADMIN GUDANG / PENGURUS BARANG
            [
                'urutan' => 6,
                'nama_jabatan' => 'Pengurus Barang',
                'role_id' => $roles['admin_gudang']->id ?? null,
                'deskripsi' => 'Admin Gudang / Pengurus Barang - Kelola stok, proses distribusi, cetak SBBK',
            ],
            [
                'urutan' => 7,
                'nama_jabatan' => 'Admin Gudang',
                'role_id' => $roles['admin_gudang']->id ?? null,
                'deskripsi' => 'Admin Gudang / Pengurus Barang - Kelola stok, proses distribusi, cetak SBBK',
            ],
            [
                'urutan' => 8,
                'nama_jabatan' => 'Admin Gudang Aset',
                'role_id' => $roles['admin_gudang_aset']->id ?? null,
                'deskripsi' => 'Admin Gudang Kategori Aset - Hanya akses gudang Aset, tidak bisa Persediaan/Farmasi',
            ],
            [
                'urutan' => 9,
                'nama_jabatan' => 'Admin Gudang Persediaan',
                'role_id' => $roles['admin_gudang_persediaan']->id ?? null,
                'deskripsi' => 'Admin Gudang Kategori Persediaan - Hanya akses gudang Persediaan, tidak bisa Aset/Farmasi',
            ],
            [
                'urutan' => 10,
                'nama_jabatan' => 'Admin Gudang Farmasi',
                'role_id' => $roles['admin_gudang_farmasi']->id ?? null,
                'deskripsi' => 'Admin Gudang Kategori Farmasi - Hanya akses gudang Farmasi, tidak bisa Aset/Persediaan',
            ],
            [
                'urutan' => 11,
                'nama_jabatan' => 'Admin Gudang Unit',
                'role_id' => $roles['admin_gudang_unit']->id ?? null,
                'deskripsi' => 'Admin Gudang Unit Kerja - Hanya akses inventory unit kerjanya, tidak bisa gudang pusat',
            ],
            
            // UNIT TERKAIT
            [
                'urutan' => 12,
                'nama_jabatan' => 'Perencanaan',
                'role_id' => $roles['perencanaan']->id ?? null,
                'deskripsi' => 'Unit Perencanaan - Menindaklanjuti disposisi pimpinan',
            ],
            [
                'urutan' => 13,
                'nama_jabatan' => 'Pengadaan Barang',
                'role_id' => $roles['pengadaan']->id ?? null,
                'deskripsi' => 'Unit Pengadaan - Menindaklanjuti disposisi pimpinan',
            ],
            [
                'urutan' => 14,
                'nama_jabatan' => 'Keuangan/Bendahara',
                'role_id' => $roles['keuangan']->id ?? null,
                'deskripsi' => 'Unit Keuangan - Menindaklanjuti disposisi pimpinan',
            ],
        ];

        foreach ($jabatans as $jabatan) {
            MasterJabatan::updateOrCreate(
                ['nama_jabatan' => $jabatan['nama_jabatan']],
                $jabatan
            );
        }
    }
}

