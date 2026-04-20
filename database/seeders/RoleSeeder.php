<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Admin Sistem',
                'description' => 'Admin IT / Pengelola Aplikasi - Kelola user, role, master data, konfigurasi sistem',
            ],
            [
                'name' => 'pegawai',
                'display_name' => 'Pegawai (Pemohon)',
                'description' => 'Staf Unit Kerja / Pelaksana Teknis - Membuat permintaan barang, melihat status, menerima barang',
            ],
            [
                'name' => 'kepala_unit',
                'display_name' => 'Kepala Unit',
                'description' => 'Kepala Seksi / Kepala Sub Unit - Melihat permintaan dari unitnya, memberi status "Mengetahui"',
            ],
            [
                'name' => 'kasubbag_tu',
                'display_name' => 'Kasubbag TU',
                'description' => 'Kepala Sub Bagian Tata Usaha - Verifikasi administrasi permintaan, cek kelengkapan',
            ],
            [
                'name' => 'kepala_pusat',
                'display_name' => 'Kepala Pusat (Pimpinan)',
                'description' => 'Kepala Pusat / Kepala UPT - Approve/Reject permintaan, memberikan disposisi',
            ],
            [
                'name' => 'admin_gudang',
                'display_name' => 'Admin Gudang / Pengurus Barang',
                'description' => 'Pengurus Barang / Admin Gudang - Kelola stok, proses distribusi, cetak SBBK, register aset',
            ],
            [
                'name' => 'admin_gudang_aset',
                'display_name' => 'Admin Gudang Aset',
                'description' => 'Admin Gudang Pusat Kategori Aset - Kelola stok aset, proses distribusi aset, cetak SBBK aset',
            ],
            [
                'name' => 'admin_gudang_persediaan',
                'display_name' => 'Admin Gudang Persediaan',
                'description' => 'Admin Gudang Pusat Kategori Persediaan - Kelola stok persediaan, proses distribusi persediaan, cetak SBBK persediaan',
            ],
            [
                'name' => 'admin_gudang_farmasi',
                'display_name' => 'Admin Gudang Farmasi',
                'description' => 'Admin Gudang Pusat Kategori Farmasi - Kelola stok farmasi, proses distribusi farmasi, cetak SBBK farmasi',
            ],
            [
                'name' => 'admin_gudang_unit',
                'display_name' => 'Admin Gudang Unit',
                'description' => 'Admin Gudang Unit Kerja - Hanya akses inventory unit kerjanya, tidak bisa akses gudang pusat',
            ],
            [
                'name' => 'perencanaan',
                'display_name' => 'Perencanaan',
                'description' => 'Unit Perencanaan - Menindaklanjuti disposisi pimpinan',
            ],
            [
                'name' => 'pengadaan',
                'display_name' => 'Pengadaan',
                'description' => 'Unit Pengadaan - Menindaklanjuti disposisi pimpinan',
            ],
            [
                'name' => 'keuangan',
                'display_name' => 'Keuangan',
                'description' => 'Unit Keuangan - Menindaklanjuti disposisi pimpinan',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                $role
            );
        }
    }
}
