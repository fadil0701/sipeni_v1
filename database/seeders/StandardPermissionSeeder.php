<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class StandardPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'web';

        $permissions = [

            // ===================== DASHBOARD =====================
            ['name' => 'dashboard.view', 'display_name' => 'View Dashboard', 'module' => 'dashboard', 'group' => 'dashboard', 'sort_order' => 1],

            // ===================== MASTER MANAJEMEN =====================
            ['name' => 'master.pegawai.view', 'display_name' => 'View Pegawai', 'module' => 'master-manajemen', 'group' => 'master.pegawai', 'sort_order' => 10],
            ['name' => 'master.pegawai.create', 'display_name' => 'Create Pegawai', 'module' => 'master-manajemen', 'group' => 'master.pegawai', 'sort_order' => 11],
            ['name' => 'master.pegawai.edit', 'display_name' => 'Edit Pegawai', 'module' => 'master-manajemen', 'group' => 'master.pegawai', 'sort_order' => 12],
            ['name' => 'master.pegawai.delete', 'display_name' => 'Delete Pegawai', 'module' => 'master-manajemen', 'group' => 'master.pegawai', 'sort_order' => 13],

            ['name' => 'master.jabatan.view', 'display_name' => 'View Jabatan', 'module' => 'master-manajemen', 'group' => 'master.jabatan', 'sort_order' => 20],
            ['name' => 'master.jabatan.create', 'display_name' => 'Create Jabatan', 'module' => 'master-manajemen', 'group' => 'master.jabatan', 'sort_order' => 21],
            ['name' => 'master.jabatan.edit', 'display_name' => 'Edit Jabatan', 'module' => 'master-manajemen', 'group' => 'master.jabatan', 'sort_order' => 22],
            ['name' => 'master.jabatan.delete', 'display_name' => 'Delete Jabatan', 'module' => 'master-manajemen', 'group' => 'master.jabatan', 'sort_order' => 23],

            ['name' => 'master.unit-kerja.view', 'display_name' => 'View Unit Kerja', 'module' => 'master-manajemen', 'group' => 'master.unit-kerja', 'sort_order' => 30],
            ['name' => 'master.unit-kerja.create', 'display_name' => 'Create Unit Kerja', 'module' => 'master-manajemen', 'group' => 'master.unit-kerja', 'sort_order' => 31],
            ['name' => 'master.unit-kerja.edit', 'display_name' => 'Edit Unit Kerja', 'module' => 'master-manajemen', 'group' => 'master.unit-kerja', 'sort_order' => 32],
            ['name' => 'master.unit-kerja.delete', 'display_name' => 'Delete Unit Kerja', 'module' => 'master-manajemen', 'group' => 'master.unit-kerja', 'sort_order' => 33],

            ['name' => 'master.gudang.view', 'display_name' => 'View Gudang', 'module' => 'master-manajemen', 'group' => 'master.gudang', 'sort_order' => 40],
            ['name' => 'master.gudang.create', 'display_name' => 'Create Gudang', 'module' => 'master-manajemen', 'group' => 'master.gudang', 'sort_order' => 41],
            ['name' => 'master.gudang.edit', 'display_name' => 'Edit Gudang', 'module' => 'master-manajemen', 'group' => 'master.gudang', 'sort_order' => 42],
            ['name' => 'master.gudang.delete', 'display_name' => 'Delete Gudang', 'module' => 'master-manajemen', 'group' => 'master.gudang', 'sort_order' => 43],

            ['name' => 'master.ruangan.view', 'display_name' => 'View Ruangan', 'module' => 'master-manajemen', 'group' => 'master.ruangan', 'sort_order' => 50],
            ['name' => 'master.ruangan.create', 'display_name' => 'Create Ruangan', 'module' => 'master-manajemen', 'group' => 'master.ruangan', 'sort_order' => 51],
            ['name' => 'master.ruangan.edit', 'display_name' => 'Edit Ruangan', 'module' => 'master-manajemen', 'group' => 'master.ruangan', 'sort_order' => 52],
            ['name' => 'master.ruangan.delete', 'display_name' => 'Delete Ruangan', 'module' => 'master-manajemen', 'group' => 'master.ruangan', 'sort_order' => 53],

            ['name' => 'master.program.view', 'display_name' => 'View Program', 'module' => 'master-manajemen', 'group' => 'master.program', 'sort_order' => 60],
            ['name' => 'master.program.create', 'display_name' => 'Create Program', 'module' => 'master-manajemen', 'group' => 'master.program', 'sort_order' => 61],
            ['name' => 'master.program.edit', 'display_name' => 'Edit Program', 'module' => 'master-manajemen', 'group' => 'master.program', 'sort_order' => 62],
            ['name' => 'master.program.delete', 'display_name' => 'Delete Program', 'module' => 'master-manajemen', 'group' => 'master.program', 'sort_order' => 63],

            ['name' => 'master.kegiatan.view', 'display_name' => 'View Kegiatan', 'module' => 'master-manajemen', 'group' => 'master.kegiatan', 'sort_order' => 70],
            ['name' => 'master.kegiatan.create', 'display_name' => 'Create Kegiatan', 'module' => 'master-manajemen', 'group' => 'master.kegiatan', 'sort_order' => 71],
            ['name' => 'master.kegiatan.edit', 'display_name' => 'Edit Kegiatan', 'module' => 'master-manajemen', 'group' => 'master.kegiatan', 'sort_order' => 72],
            ['name' => 'master.kegiatan.delete', 'display_name' => 'Delete Kegiatan', 'module' => 'master-manajemen', 'group' => 'master.kegiatan', 'sort_order' => 73],

            ['name' => 'master.sub-kegiatan.view', 'display_name' => 'View Sub Kegiatan', 'module' => 'master-manajemen', 'group' => 'master.sub-kegiatan', 'sort_order' => 80],
            ['name' => 'master.sub-kegiatan.create', 'display_name' => 'Create Sub Kegiatan', 'module' => 'master-manajemen', 'group' => 'master.sub-kegiatan', 'sort_order' => 81],
            ['name' => 'master.sub-kegiatan.edit', 'display_name' => 'Edit Sub Kegiatan', 'module' => 'master-manajemen', 'group' => 'master.sub-kegiatan', 'sort_order' => 82],
            ['name' => 'master.sub-kegiatan.delete', 'display_name' => 'Delete Sub Kegiatan', 'module' => 'master-manajemen', 'group' => 'master.sub-kegiatan', 'sort_order' => 83],

            // ===================== MASTER DATA =====================
            ['name' => 'master-data.aset.view', 'display_name' => 'View Klasifikasi Aset', 'module' => 'master-data', 'group' => 'master-data.aset', 'sort_order' => 100],
            ['name' => 'master-data.aset.create', 'display_name' => 'Create Klasifikasi Aset', 'module' => 'master-data', 'group' => 'master-data.aset', 'sort_order' => 101],
            ['name' => 'master-data.aset.edit', 'display_name' => 'Edit Klasifikasi Aset', 'module' => 'master-data', 'group' => 'master-data.aset', 'sort_order' => 102],
            ['name' => 'master-data.aset.delete', 'display_name' => 'Delete Klasifikasi Aset', 'module' => 'master-data', 'group' => 'master-data.aset', 'sort_order' => 103],

            ['name' => 'master-data.kode-barang.view', 'display_name' => 'View Kode Barang', 'module' => 'master-data', 'group' => 'master-data.kode-barang', 'sort_order' => 110],
            ['name' => 'master-data.kode-barang.create', 'display_name' => 'Create Kode Barang', 'module' => 'master-data', 'group' => 'master-data.kode-barang', 'sort_order' => 111],
            ['name' => 'master-data.kode-barang.edit', 'display_name' => 'Edit Kode Barang', 'module' => 'master-data', 'group' => 'master-data.kode-barang', 'sort_order' => 112],
            ['name' => 'master-data.kode-barang.delete', 'display_name' => 'Delete Kode Barang', 'module' => 'master-data', 'group' => 'master-data.kode-barang', 'sort_order' => 113],

            ['name' => 'master-data.kategori-barang.view', 'display_name' => 'View Kategori Barang', 'module' => 'master-data', 'group' => 'master-data.kategori-barang', 'sort_order' => 120],
            ['name' => 'master-data.kategori-barang.create', 'display_name' => 'Create Kategori Barang', 'module' => 'master-data', 'group' => 'master-data.kategori-barang', 'sort_order' => 121],
            ['name' => 'master-data.kategori-barang.edit', 'display_name' => 'Edit Kategori Barang', 'module' => 'master-data', 'group' => 'master-data.kategori-barang', 'sort_order' => 122],
            ['name' => 'master-data.kategori-barang.delete', 'display_name' => 'Delete Kategori Barang', 'module' => 'master-data', 'group' => 'master-data.kategori-barang', 'sort_order' => 123],

            ['name' => 'master-data.jenis-barang.view', 'display_name' => 'View Jenis Barang', 'module' => 'master-data', 'group' => 'master-data.jenis-barang', 'sort_order' => 130],
            ['name' => 'master-data.jenis-barang.create', 'display_name' => 'Create Jenis Barang', 'module' => 'master-data', 'group' => 'master-data.jenis-barang', 'sort_order' => 131],
            ['name' => 'master-data.jenis-barang.edit', 'display_name' => 'Edit Jenis Barang', 'module' => 'master-data', 'group' => 'master-data.jenis-barang', 'sort_order' => 132],
            ['name' => 'master-data.jenis-barang.delete', 'display_name' => 'Delete Jenis Barang', 'module' => 'master-data', 'group' => 'master-data.jenis-barang', 'sort_order' => 133],

            ['name' => 'master-data.subjenis-barang.view', 'display_name' => 'View Subjenis Barang', 'module' => 'master-data', 'group' => 'master-data.subjenis-barang', 'sort_order' => 140],
            ['name' => 'master-data.subjenis-barang.create', 'display_name' => 'Create Subjenis Barang', 'module' => 'master-data', 'group' => 'master-data.subjenis-barang', 'sort_order' => 141],
            ['name' => 'master-data.subjenis-barang.edit', 'display_name' => 'Edit Subjenis Barang', 'module' => 'master-data', 'group' => 'master-data.subjenis-barang', 'sort_order' => 142],
            ['name' => 'master-data.subjenis-barang.delete', 'display_name' => 'Delete Subjenis Barang', 'module' => 'master-data', 'group' => 'master-data.subjenis-barang', 'sort_order' => 143],

            ['name' => 'master-data.data-barang.view', 'display_name' => 'View Data Barang', 'module' => 'master-data', 'group' => 'master-data.data-barang', 'sort_order' => 150],
            ['name' => 'master-data.data-barang.create', 'display_name' => 'Create Data Barang', 'module' => 'master-data', 'group' => 'master-data.data-barang', 'sort_order' => 151],
            ['name' => 'master-data.data-barang.edit', 'display_name' => 'Edit Data Barang', 'module' => 'master-data', 'group' => 'master-data.data-barang', 'sort_order' => 152],
            ['name' => 'master-data.data-barang.delete', 'display_name' => 'Delete Data Barang', 'module' => 'master-data', 'group' => 'master-data.data-barang', 'sort_order' => 153],

            ['name' => 'master-data.satuan.view', 'display_name' => 'View Satuan', 'module' => 'master-data', 'group' => 'master-data.satuan', 'sort_order' => 160],
            ['name' => 'master-data.satuan.create', 'display_name' => 'Create Satuan', 'module' => 'master-data', 'group' => 'master-data.satuan', 'sort_order' => 161],
            ['name' => 'master-data.satuan.edit', 'display_name' => 'Edit Satuan', 'module' => 'master-data', 'group' => 'master-data.satuan', 'sort_order' => 162],
            ['name' => 'master-data.satuan.delete', 'display_name' => 'Delete Satuan', 'module' => 'master-data', 'group' => 'master-data.satuan', 'sort_order' => 163],

            ['name' => 'master-data.sumber-anggaran.view', 'display_name' => 'View Sumber Anggaran', 'module' => 'master-data', 'group' => 'master-data.sumber-anggaran', 'sort_order' => 170],
            ['name' => 'master-data.sumber-anggaran.create', 'display_name' => 'Create Sumber Anggaran', 'module' => 'master-data', 'group' => 'master-data.sumber-anggaran', 'sort_order' => 171],
            ['name' => 'master-data.sumber-anggaran.edit', 'display_name' => 'Edit Sumber Anggaran', 'module' => 'master-data', 'group' => 'master-data.sumber-anggaran', 'sort_order' => 172],
            ['name' => 'master-data.sumber-anggaran.delete', 'display_name' => 'Delete Sumber Anggaran', 'module' => 'master-data', 'group' => 'master-data.sumber-anggaran', 'sort_order' => 173],

            ['name' => 'master-data.import-struktur-barang.view', 'display_name' => 'Import Struktur Barang', 'module' => 'master-data', 'group' => 'master-data.import-struktur-barang', 'sort_order' => 180],

            // ===================== INVENTORY =====================
            ['name' => 'inventory.data-stock.view', 'display_name' => 'View Data Stock', 'module' => 'inventory', 'group' => 'inventory.data-stock', 'sort_order' => 200],
            ['name' => 'inventory.data-stock.show', 'display_name' => 'Show Data Stock', 'module' => 'inventory', 'group' => 'inventory.data-stock', 'sort_order' => 201],
            ['name' => 'inventory.data-stock.merk-breakdown', 'display_name' => 'Stock Merk Breakdown', 'module' => 'inventory', 'group' => 'inventory.data-stock', 'sort_order' => 202],

            ['name' => 'inventory.data-inventory.view', 'display_name' => 'View Data Inventory', 'module' => 'inventory', 'group' => 'inventory.data-inventory', 'sort_order' => 210],
            ['name' => 'inventory.data-inventory.create', 'display_name' => 'Create Data Inventory', 'module' => 'inventory', 'group' => 'inventory.data-inventory', 'sort_order' => 211],
            ['name' => 'inventory.data-inventory.edit', 'display_name' => 'Edit Data Inventory', 'module' => 'inventory', 'group' => 'inventory.data-inventory', 'sort_order' => 212],
            ['name' => 'inventory.data-inventory.delete', 'display_name' => 'Delete Data Inventory', 'module' => 'inventory', 'group' => 'inventory.data-inventory', 'sort_order' => 213],
            ['name' => 'inventory.data-inventory.import', 'display_name' => 'Import Data Inventory', 'module' => 'inventory', 'group' => 'inventory.data-inventory', 'sort_order' => 214],

            ['name' => 'inventory.farmasi-kedaluwarsa.view', 'display_name' => 'View Farmasi Kedaluwarsa', 'module' => 'inventory', 'group' => 'inventory.farmasi-kedaluwarsa', 'sort_order' => 220],
            ['name' => 'inventory.farmasi-kedaluwarsa.export', 'display_name' => 'Export Farmasi Kedaluwarsa', 'module' => 'inventory', 'group' => 'inventory.farmasi-kedaluwarsa', 'sort_order' => 221],

            ['name' => 'inventory.stock-adjustment.view', 'display_name' => 'View Stock Adjustment', 'module' => 'inventory', 'group' => 'inventory.stock-adjustment', 'sort_order' => 230],
            ['name' => 'inventory.stock-adjustment.create', 'display_name' => 'Create Stock Adjustment', 'module' => 'inventory', 'group' => 'inventory.stock-adjustment', 'sort_order' => 231],
            ['name' => 'inventory.stock-adjustment.edit', 'display_name' => 'Edit Stock Adjustment', 'module' => 'inventory', 'group' => 'inventory.stock-adjustment', 'sort_order' => 232],
            ['name' => 'inventory.stock-adjustment.delete', 'display_name' => 'Delete Stock Adjustment', 'module' => 'inventory', 'group' => 'inventory.stock-adjustment', 'sort_order' => 233],

            ['name' => 'inventory.item.view', 'display_name' => 'View Inventory Item', 'module' => 'inventory', 'group' => 'inventory.item', 'sort_order' => 240],

            // ===================== PERMINTAAN BARANG =====================
            ['name' => 'permintaan.barang.view', 'display_name' => 'View Permintaan Barang', 'module' => 'permintaan', 'group' => 'permintaan.barang', 'sort_order' => 300],
            ['name' => 'permintaan.barang.create', 'display_name' => 'Create Permintaan Barang', 'module' => 'permintaan', 'group' => 'permintaan.barang', 'sort_order' => 301],
            ['name' => 'permintaan.barang.edit', 'display_name' => 'Edit Permintaan Barang', 'module' => 'permintaan', 'group' => 'permintaan.barang', 'sort_order' => 302],
            ['name' => 'permintaan.barang.show', 'display_name' => 'Show Permintaan Barang', 'module' => 'permintaan', 'group' => 'permintaan.barang', 'sort_order' => 303],
            ['name' => 'permintaan.barang.submit', 'display_name' => 'Submit Permintaan Barang', 'module' => 'permintaan', 'group' => 'permintaan.barang', 'sort_order' => 304],
            ['name' => 'permintaan.barang.verify', 'display_name' => 'Verify Permintaan Barang', 'module' => 'permintaan', 'group' => 'permintaan.barang', 'sort_order' => 305],
            ['name' => 'permintaan.barang.approve', 'display_name' => 'Approve Permintaan Barang', 'module' => 'permintaan', 'group' => 'permintaan.barang', 'sort_order' => 306],
            ['name' => 'permintaan.barang.reject', 'display_name' => 'Reject Permintaan Barang', 'module' => 'permintaan', 'group' => 'permintaan.barang', 'sort_order' => 307],
            ['name' => 'permintaan.barang.dispose', 'display_name' => 'Dispose Permintaan', 'module' => 'permintaan', 'group' => 'permintaan.barang', 'sort_order' => 308],

            // ===================== PEMINJAMAN BARANG =====================
            ['name' => 'peminjaman.barang.view', 'display_name' => 'View Peminjaman Barang', 'module' => 'permintaan', 'group' => 'peminjaman.barang', 'sort_order' => 310],
            ['name' => 'peminjaman.barang.create', 'display_name' => 'Create Peminjaman Barang', 'module' => 'permintaan', 'group' => 'peminjaman.barang', 'sort_order' => 311],
            ['name' => 'peminjaman.barang.approve-pengurus', 'display_name' => 'Approve Peminjaman (Pengurus)', 'module' => 'permintaan', 'group' => 'peminjaman.barang', 'sort_order' => 312],
            ['name' => 'peminjaman.barang.approve-unit', 'display_name' => 'Approve Peminjaman (Kepala Unit)', 'module' => 'permintaan', 'group' => 'peminjaman.barang', 'sort_order' => 313],
            ['name' => 'peminjaman.barang.serah-terima', 'display_name' => 'Serah Terima Peminjaman', 'module' => 'permintaan', 'group' => 'peminjaman.barang', 'sort_order' => 314],
            ['name' => 'peminjaman.barang.pengembalian', 'display_name' => 'Pengembalian Barang', 'module' => 'permintaan', 'group' => 'peminjaman.barang', 'sort_order' => 315],

            // ===================== PEMELIHARAAN =====================
            ['name' => 'pemeliharaan.permintaan.view', 'display_name' => 'View Permintaan Pemeliharaan', 'module' => 'maintenance', 'group' => 'pemeliharaan.permintaan', 'sort_order' => 320],

            // ===================== APPROVAL =====================
            ['name' => 'approval.view', 'display_name' => 'View Approval', 'module' => 'approval', 'group' => 'approval', 'sort_order' => 400],
            ['name' => 'approval.show', 'display_name' => 'Show Approval', 'module' => 'approval', 'group' => 'approval', 'sort_order' => 401],
            ['name' => 'approval.verify', 'display_name' => 'Verify Approval', 'module' => 'approval', 'group' => 'approval', 'sort_order' => 402],
            ['name' => 'approval.approve', 'display_name' => 'Approve', 'module' => 'approval', 'group' => 'approval', 'sort_order' => 403],
            ['name' => 'approval.reject', 'display_name' => 'Reject', 'module' => 'approval', 'group' => 'approval', 'sort_order' => 404],
            ['name' => 'approval.know', 'display_name' => 'Mengetahui', 'module' => 'approval', 'group' => 'approval', 'sort_order' => 405],
            ['name' => 'approval.dispose', 'display_name' => 'Disposisi', 'module' => 'approval', 'group' => 'approval', 'sort_order' => 406],

            // ===================== DISTRIBUSI =====================
            ['name' => 'distribusi.draft.view', 'display_name' => 'View Draft Distribusi', 'module' => 'distribusi', 'group' => 'distribusi.draft', 'sort_order' => 500],
            ['name' => 'distribusi.draft.create', 'display_name' => 'Create Draft Distribusi', 'module' => 'distribusi', 'group' => 'distribusi.draft', 'sort_order' => 501],
            ['name' => 'distribusi.draft.show', 'display_name' => 'Show Draft Distribusi', 'module' => 'distribusi', 'group' => 'distribusi.draft', 'sort_order' => 502],

            ['name' => 'distribusi.compile.view', 'display_name' => 'View Compile Distribusi', 'module' => 'distribusi', 'group' => 'distribusi.compile', 'sort_order' => 510],
            ['name' => 'distribusi.compile.create', 'display_name' => 'Create Compile Distribusi', 'module' => 'distribusi', 'group' => 'distribusi.compile', 'sort_order' => 511],

            ['name' => 'distribusi.view', 'display_name' => 'View Distribusi', 'module' => 'distribusi', 'group' => 'distribusi', 'sort_order' => 520],
            ['name' => 'distribusi.show', 'display_name' => 'Show Distribusi', 'module' => 'distribusi', 'group' => 'distribusi', 'sort_order' => 521],
            ['name' => 'distribusi.create', 'display_name' => 'Create Distribusi', 'module' => 'distribusi', 'group' => 'distribusi', 'sort_order' => 522],

            ['name' => 'distribusi.receive', 'display_name' => 'Receive Distribusi', 'module' => 'distribusi', 'group' => 'distribusi', 'sort_order' => 530],
            ['name' => 'distribusi.retur', 'display_name' => 'Retur Barang', 'module' => 'distribusi', 'group' => 'distribusi', 'sort_order' => 540],

            // ===================== ASET =====================
            ['name' => 'aset.register.view', 'display_name' => 'View Register Aset', 'module' => 'asset', 'group' => 'aset.register', 'sort_order' => 600],
            ['name' => 'aset.register.create', 'display_name' => 'Create Register Aset', 'module' => 'asset', 'group' => 'aset.register', 'sort_order' => 601],
            ['name' => 'aset.register.edit', 'display_name' => 'Edit Register Aset', 'module' => 'asset', 'group' => 'aset.register', 'sort_order' => 602],
            ['name' => 'aset.register.show', 'display_name' => 'Show Register Aset', 'module' => 'asset', 'group' => 'aset.register', 'sort_order' => 603],
            ['name' => 'aset.register.delete', 'display_name' => 'Delete Register Aset', 'module' => 'asset', 'group' => 'aset.register', 'sort_order' => 604],

            ['name' => 'aset.kir.view', 'display_name' => 'View KIR', 'module' => 'asset', 'group' => 'aset.kir', 'sort_order' => 610],
            ['name' => 'aset.kir.print', 'display_name' => 'Print KIR', 'module' => 'asset', 'group' => 'aset.kir', 'sort_order' => 611],

            ['name' => 'aset.mutasi.view', 'display_name' => 'View Mutasi Aset', 'module' => 'asset', 'group' => 'aset.mutasi', 'sort_order' => 620],
            ['name' => 'aset.mutasi.create', 'display_name' => 'Create Mutasi Aset', 'module' => 'asset', 'group' => 'aset.mutasi', 'sort_order' => 621],

            // ===================== RKU / PLANNING =====================
            ['name' => 'rku.view', 'display_name' => 'View RKU', 'module' => 'planning', 'group' => 'rku', 'sort_order' => 700],
            ['name' => 'rku.create', 'display_name' => 'Create RKU', 'module' => 'planning', 'group' => 'rku', 'sort_order' => 701],
            ['name' => 'rku.edit', 'display_name' => 'Edit RKU', 'module' => 'planning', 'group' => 'rku', 'sort_order' => 702],
            ['name' => 'rku.delete', 'display_name' => 'Delete RKU', 'module' => 'planning', 'group' => 'rku', 'sort_order' => 703],
            ['name' => 'rku.submit', 'display_name' => 'Submit RKU', 'module' => 'planning', 'group' => 'rku', 'sort_order' => 704],
            ['name' => 'rku.approve', 'display_name' => 'Approve RKU', 'module' => 'planning', 'group' => 'rku', 'sort_order' => 705],
            ['name' => 'rku.reject', 'display_name' => 'Reject RKU', 'module' => 'planning', 'group' => 'rku', 'sort_order' => 706],
            ['name' => 'rku.lock', 'display_name' => 'Lock RKU', 'module' => 'planning', 'group' => 'rku', 'sort_order' => 707],
            ['name' => 'rku.export', 'display_name' => 'Export RKU', 'module' => 'planning', 'group' => 'rku', 'sort_order' => 708],
            ['name' => 'rku.rekap', 'display_name' => 'Rekap RKU', 'module' => 'planning', 'group' => 'rku', 'sort_order' => 709],
            ['name' => 'rku.manage', 'display_name' => 'Manage RKU', 'module' => 'planning', 'group' => 'rku', 'sort_order' => 710],

            // ===================== PENGADAAN / PROCUREMENT =====================
            ['name' => 'pengadaan.paket.view', 'display_name' => 'View Paket Pengadaan', 'module' => 'procurement', 'group' => 'pengadaan.paket', 'sort_order' => 800],
            ['name' => 'pengadaan.proses.view', 'display_name' => 'View Proses Pengadaan', 'module' => 'procurement', 'group' => 'pengadaan.proses', 'sort_order' => 810],
            ['name' => 'pengadaan.approve', 'display_name' => 'Approve Pengadaan', 'module' => 'procurement', 'group' => 'pengadaan', 'sort_order' => 820],

            // ===================== KEUANGAN / FINANCE =====================
            ['name' => 'keuangan.pembayaran.view', 'display_name' => 'View Pembayaran', 'module' => 'finance', 'group' => 'keuangan.pembayaran', 'sort_order' => 900],
            ['name' => 'keuangan.verify', 'display_name' => 'Verify Keuangan', 'module' => 'finance', 'group' => 'keuangan', 'sort_order' => 910],
            ['name' => 'keuangan.pay', 'display_name' => 'Pay', 'module' => 'finance', 'group' => 'keuangan', 'sort_order' => 911],
            ['name' => 'keuangan.print', 'display_name' => 'Print Keuangan', 'module' => 'finance', 'group' => 'keuangan', 'sort_order' => 912],

            // ===================== MAINTENANCE =====================
            ['name' => 'maintenance.jadwal.view', 'display_name' => 'View Jadwal Maintenance', 'module' => 'maintenance', 'group' => 'maintenance.jadwal', 'sort_order' => 1000],
            ['name' => 'maintenance.kalibrasi.view', 'display_name' => 'View Kalibrasi', 'module' => 'maintenance', 'group' => 'maintenance.kalibrasi', 'sort_order' => 1010],
            ['name' => 'maintenance.service-report.view', 'display_name' => 'View Service Report', 'module' => 'maintenance', 'group' => 'maintenance.service-report', 'sort_order' => 1020],

            // ===================== LAPORAN =====================
            ['name' => 'laporan.view', 'display_name' => 'View Laporan', 'module' => 'reports', 'group' => 'laporan', 'sort_order' => 1100],
            ['name' => 'laporan.stok-gudang', 'display_name' => 'Laporan Stok Gudang', 'module' => 'reports', 'group' => 'laporan', 'sort_order' => 1101],
            ['name' => 'laporan.kartu-stok', 'display_name' => 'Kartu Stok', 'module' => 'reports', 'group' => 'laporan', 'sort_order' => 1102],
            ['name' => 'laporan.kartu-stok.merk-breakdown', 'display_name' => 'Kartu Stok Merk Breakdown', 'module' => 'reports', 'group' => 'laporan', 'sort_order' => 1103],
            ['name' => 'laporan.export', 'display_name' => 'Export Laporan', 'module' => 'reports', 'group' => 'laporan', 'sort_order' => 1104],
            ['name' => 'laporan.print', 'display_name' => 'Print Laporan', 'module' => 'reports', 'group' => 'laporan', 'sort_order' => 1105],

            // ===================== ADMIN =====================
            ['name' => 'admin.roles.view', 'display_name' => 'View Roles', 'module' => 'admin', 'group' => 'admin.roles', 'sort_order' => 2000],
            ['name' => 'admin.roles.create', 'display_name' => 'Create Role', 'module' => 'admin', 'group' => 'admin.roles', 'sort_order' => 2001],
            ['name' => 'admin.roles.edit', 'display_name' => 'Edit Role', 'module' => 'admin', 'group' => 'admin.roles', 'sort_order' => 2002],
            ['name' => 'admin.roles.delete', 'display_name' => 'Delete Role', 'module' => 'admin', 'group' => 'admin.roles', 'sort_order' => 2003],

            ['name' => 'admin.users.view', 'display_name' => 'View Users', 'module' => 'admin', 'group' => 'admin.users', 'sort_order' => 2010],
            ['name' => 'admin.users.create', 'display_name' => 'Create User', 'module' => 'admin', 'group' => 'admin.users', 'sort_order' => 2011],
            ['name' => 'admin.users.edit', 'display_name' => 'Edit User', 'module' => 'admin', 'group' => 'admin.users', 'sort_order' => 2012],
            ['name' => 'admin.users.delete', 'display_name' => 'Delete User', 'module' => 'admin', 'group' => 'admin.users', 'sort_order' => 2013],

            ['name' => 'admin.print-templates.view', 'display_name' => 'View Print Templates', 'module' => 'admin', 'group' => 'admin.print-templates', 'sort_order' => 2020],
            ['name' => 'admin.print-templates.edit', 'display_name' => 'Edit Print Templates', 'module' => 'admin', 'group' => 'admin.print-templates', 'sort_order' => 2021],

            ['name' => 'admin.audit-trail.view', 'display_name' => 'View Audit Trail', 'module' => 'admin', 'group' => 'admin.audit-trail', 'sort_order' => 2030],
        ];

        foreach ($permissions as $permission) {
            $row = array_merge(['guard_name' => $guard], $permission);
            Permission::updateOrCreate(
                ['name' => $permission['name'], 'guard_name' => $guard],
                $row
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->command?->info('✓ Standard permissions seeded: '.count($permissions).' permissions');
    }
}
