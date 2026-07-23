<?php

namespace Database\Seeders;

use App\Models\MasterJabatan;
use Illuminate\Database\Seeder;

class MasterJabatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Jabatan hanya gelar organisasi (baku). Hak akses sistem diatur per akun user
     * lewat master pegawai, bukan lewat jabatan.
     */
    public function run(): void
    {
        $jabatans = [
            [
                'urutan' => 1,
                'nama_jabatan' => 'Admin IT / Pengelola Aplikasi',
                'deskripsi' => 'Admin Sistem - Kelola user, role, master data, konfigurasi sistem',
            ],
            [
                'urutan' => 2,
                'nama_jabatan' => 'Admin Unit',
                'deskripsi' => 'Admin Unit / Staf Unit Kerja / Pelaksana Teknis - Membuat permintaan barang, melihat status, menerima barang',
            ],
            [
                'urutan' => 3,
                'nama_jabatan' => 'Kepala Unit',
                'deskripsi' => 'Kepala Unit / Kepala Seksi / Kepala Sub Unit - Melihat permintaan dari unitnya, memberi status "Mengetahui"',
            ],
            [
                'urutan' => 4,
                'nama_jabatan' => 'Kasubbag TU',
                'deskripsi' => 'Kasubbag TU - Verifikasi administrasi permintaan, cek kelengkapan',
            ],
            [
                'urutan' => 5,
                'nama_jabatan' => 'Kepala Pusat',
                'deskripsi' => 'Kepala Pusat / Kepala UPT (Pimpinan) - Approve/Reject permintaan, memberikan disposisi',
            ],
            [
                'urutan' => 6,
                'nama_jabatan' => 'Pengurus Barang',
                'deskripsi' => 'Admin Gudang / Pengurus Barang - Kelola stok, proses distribusi, cetak SBBK',
            ],
            [
                'urutan' => 7,
                'nama_jabatan' => 'Admin Gudang',
                'deskripsi' => 'Admin Gudang / Pengurus Barang - Kelola stok, proses distribusi, cetak SBBK',
            ],
            [
                'urutan' => 8,
                'nama_jabatan' => 'Admin Gudang Aset',
                'deskripsi' => 'Admin Gudang Kategori Aset - Hanya akses gudang Aset, tidak bisa Persediaan/Farmasi',
            ],
            [
                'urutan' => 9,
                'nama_jabatan' => 'Admin Gudang Persediaan',
                'deskripsi' => 'Admin Gudang Kategori Persediaan - Hanya akses gudang Persediaan, tidak bisa Aset/Farmasi',
            ],
            [
                'urutan' => 10,
                'nama_jabatan' => 'Admin Gudang Farmasi',
                'deskripsi' => 'Admin Gudang Kategori Farmasi - Hanya akses gudang Farmasi, tidak bisa Aset/Persediaan',
            ],
            [
                'urutan' => 11,
                'nama_jabatan' => 'Admin Gudang Unit',
                'deskripsi' => 'Admin Gudang Unit Kerja - Hanya akses inventory unit kerjanya, tidak bisa gudang pusat',
            ],
            [
                'urutan' => 12,
                'nama_jabatan' => 'Perencanaan',
                'deskripsi' => 'Unit Perencanaan - Menindaklanjuti disposisi pimpinan',
            ],
            [
                'urutan' => 13,
                'nama_jabatan' => 'Pengadaan Barang',
                'deskripsi' => 'Unit Pengadaan - Menindaklanjuti disposisi pimpinan',
            ],
            [
                'urutan' => 14,
                'nama_jabatan' => 'Keuangan/Bendahara',
                'deskripsi' => 'Unit Keuangan - Menindaklanjuti disposisi pimpinan',
            ],
            [
                'urutan' => 15,
                'nama_jabatan' => 'Staf Administrasi',
                'deskripsi' => 'Gelar organisasi baku (banyak orang bisa memakai nama ini). Hak akses aplikasi ditetapkan per akun pegawai, bukan dari nama jabatan.',
            ],
            [
                'urutan' => 16,
                'nama_jabatan' => 'ATEM (Teknisi Alat Kesehatan)',
                'deskripsi' => 'Teknisi Alat Kesehatan (ATEM) - Pelaksana pemeliharaan/servis aset alat kesehatan',
            ],
            [
                'urutan' => 17,
                'nama_jabatan' => 'Admin IT/IT Support (Teknisi IT)',
                'deskripsi' => 'Teknisi IT / IT Support - Pelaksana pemeliharaan/servis aset IT',
            ],
        ];

        foreach ($jabatans as $row) {
            MasterJabatan::updateOrCreate(
                ['nama_jabatan' => $row['nama_jabatan']],
                $row
            );
        }
    }
}
