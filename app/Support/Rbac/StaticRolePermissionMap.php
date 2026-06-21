<?php

namespace App\Support\Rbac;

/**
 * Referensi permission per role — HANYA untuk seeder/migrasi.
 * Bukan sumber authorization runtime.
 */
final class StaticRolePermissionMap
{
    public static function all(): array
    {
        $adminGudangCorePermissions = [
            'inventory.data-stock.index', 'inventory.data-stock.merk-breakdown', 'inventory.data-stock.show', 'inventory.data-stock.create', 'inventory.data-stock.store', 'inventory.data-stock.edit', 'inventory.data-stock.update',
            'inventory.data-inventory.index', 'inventory.data-inventory.show', 'inventory.data-inventory.create', 'inventory.data-inventory.store', 'inventory.data-inventory.edit', 'inventory.data-inventory.update',
            'inventory.data-inventory.import.*',
            'transaction.distribusi.index', 'transaction.distribusi.show',
            'transaction.penerimaan-barang.index', 'transaction.penerimaan-barang.show', 'transaction.penerimaan-barang.create', 'transaction.penerimaan-barang.store', 'transaction.penerimaan-barang.edit', 'transaction.penerimaan-barang.update',
            'transaction.approval.index', 'transaction.approval.show', 'transaction.approval.disposisi',
            'asset.register-aset.index', 'asset.register-aset.show', 'asset.register-aset.create', 'asset.register-aset.store', 'asset.register-aset.edit', 'asset.register-aset.update',
            'reports.stock-gudang', 'reports.kartu-stok', 'reports.kartu-stok.merk-breakdown', 'master.gudang.index', 'master.gudang.show',
            'master-data.data-barang.index', 'master-data.data-barang.show', 'master-data.data-barang.create', 'master-data.data-barang.store', 'master-data.data-barang.edit', 'master-data.data-barang.update',
        ];
        $approvalDisposisiPermissions = [
            'transaction.approval.index',
            'transaction.approval.show',
            'transaction.approval.disposisi',
        ];
        $pptkPermissions = [
            'planning.*',
            'procurement.*',
            'reports.*',
        ];

        return [
            // 1. ADMIN SISTEM
            'admin' => [
                'master-manajemen.*',
                'master.*',
                'master-data.*',
                'inventory.*',
                'transaction.*',
                'asset.*',
                'planning.*',
                'procurement.*',
                'finance.*',
                'reports.*',
                'admin.*',
            ],

            // 2. PEGAWAI (PEMOHON) / ADMIN UNIT
            'pegawai' => [
                'user.dashboard',
                'user.assets.index',
                'user.assets.show',
                'user.requests.index',
                'user.requests.show',
                'user.requests.create',
                'user.requests.store',
                'transaction.permintaan-barang.create',
                'transaction.permintaan-barang.store',
                'transaction.permintaan-barang.index',
                'transaction.permintaan-barang.show',
                'transaction.permintaan-barang.edit',
                // Action submit dari status DRAFT permintaan (pengajuan)
                'transaction.permintaan-barang.ajukan',
                'transaction.peminjaman-barang.index',
                'transaction.peminjaman-barang.create',
                'transaction.peminjaman-barang.store',
                'transaction.peminjaman-barang.show',
                'transaction.pengembalian-barang.index',
                'transaction.peminjaman-barang.pengembalian.create',
                'transaction.peminjaman-barang.pengembalian',
                'transaction.penerimaan-barang.index',
                'transaction.penerimaan-barang.show',
                'transaction.penerimaan-barang.create',
                'transaction.penerimaan-barang.store',
                // Tidak termasuk edit, update, destroy, delete - harus di-checklist secara eksplisit
                // Akses inventory untuk gudang unit
                'inventory.data-stock.index', // Hanya untuk gudang unit
                'inventory.data-stock.merk-breakdown',
                'inventory.farmasi-kedaluwarsa.index',
                'inventory.data-inventory.index', // Hanya untuk gudang unit
                'inventory.data-inventory.show', // Hanya untuk gudang unit
                'transaction.retur-barang.index',
                'transaction.retur-barang.show',
                'transaction.retur-barang.create',
                'transaction.retur-barang.store',
                // Tidak termasuk edit, update, destroy, delete - harus di-checklist secara eksplisit
                // Akses Aset & KIR untuk unit kerja mereka sendiri
                'asset.register-aset.index', // View register aset unit mereka
                'asset.register-aset.show', // View detail register aset unit mereka
                'asset.register-aset.edit', // Update register aset unit mereka
                'asset.register-aset.update', // Update register aset unit mereka
                // Tidak termasuk destroy, delete - harus di-checklist secara eksplisit
            ],

            // 3. KEPALA UNIT
            'kepala_unit' => [
                'transaction.permintaan-barang.index',
                'transaction.permintaan-barang.show',
                'transaction.peminjaman-barang.index',
                'transaction.peminjaman-barang.show',
                'transaction.pengembalian-barang.index',
                'transaction.peminjaman-barang.verifikasi-unit-a',
                'transaction.peminjaman-barang.approve-unit-b',
                'transaction.peminjaman-barang.reject-unit-b',
                'transaction.peminjaman-barang.pengembalian',
                'transaction.approval.index', // Bisa melihat daftar approval
                'transaction.approval.show', // Bisa melihat detail approval
                'transaction.approval.mengetahui', // Action khusus untuk mengetahui
                // Akses inventory untuk gudang unit
                'inventory.data-stock.index', // Hanya untuk gudang unit
                'inventory.data-stock.merk-breakdown',
                'inventory.farmasi-kedaluwarsa.index',
                'inventory.data-inventory.index', // Hanya untuk gudang unit
                'inventory.data-inventory.show', // Hanya untuk gudang unit
                'transaction.penerimaan-barang.index',
                'transaction.penerimaan-barang.show',
                'transaction.penerimaan-barang.create',
                'transaction.penerimaan-barang.store',
                // Tidak termasuk edit, update, destroy, delete - harus di-checklist secara eksplisit
                'transaction.retur-barang.index',
                'transaction.retur-barang.show',
                'transaction.retur-barang.create',
                'transaction.retur-barang.store',
                // Tidak termasuk edit, update, destroy, delete - harus di-checklist secara eksplisit
                // Akses Aset & KIR untuk unit kerja mereka sendiri
                'asset.register-aset.index', // View register aset unit mereka
                'asset.register-aset.show', // View detail register aset unit mereka
                'asset.register-aset.edit', // Update register aset unit mereka
                'asset.register-aset.update', // Update register aset unit mereka
                // Tidak termasuk destroy, delete - harus di-checklist secara eksplisit
            ],

            // 4. KASUBBAG TU (verifikasi)
            'kasubbag_tu' => [
                'transaction.permintaan-barang.index',
                'transaction.permintaan-barang.show',
                'transaction.peminjaman-barang.index',
                'transaction.peminjaman-barang.show',
                'transaction.pengembalian-barang.index',
                'transaction.peminjaman-barang.mengetahui-kasubag-tu',
                'transaction.approval.index', // Bisa melihat daftar approval
                'transaction.approval.show', // Bisa melihat detail approval
                'transaction.approval.verifikasi', // Action khusus untuk verifikasi
                'transaction.approval.kembalikan', // Bisa mengembalikan jika tidak lengkap
                // Akses untuk monitoring dan laporan
                'reports.index',
                'reports.show',
                'reports.kartu-stok',
                'reports.kartu-stok.merk-breakdown',
                // Tidak termasuk create, store, edit, update, destroy, delete - harus di-checklist secara eksplisit
                // Akses untuk data inventory dan stock
                'inventory.data-stock.index', // Bisa melihat data stock
                'inventory.data-stock.merk-breakdown',
                'inventory.data-stock.show', // Bisa melihat detail stock
                'inventory.farmasi-kedaluwarsa.index',
                'inventory.data-inventory.index', // Bisa melihat data inventory
                'inventory.data-inventory.show', // Bisa melihat detail inventory
            ],

            // 5. KEPALA PUSAT (PIMPINAN) - approve/reject
            'kepala_pusat' => [
                'transaction.permintaan-barang.index',
                'transaction.permintaan-barang.show',
                'transaction.approval.index',
                'transaction.approval.show',
                'transaction.approval.approve',
                'transaction.approval.reject',
                'transaction.approval.mengetahui',
                // Tidak termasuk destroy, delete - harus di-checklist secara eksplisit
                'reports.index',
                'reports.show',
                'reports.kartu-stok',
                'reports.kartu-stok.merk-breakdown',
                // Tidak termasuk create, store, edit, update, destroy, delete - harus di-checklist secara eksplisit
            ],

            // 6. ADMIN GUDANG / PENGURUS BARANG
            'admin_gudang' => [
                'inventory.data-stock.index',
                'inventory.data-stock.merk-breakdown',
                'inventory.data-stock.show',
                'inventory.data-stock.create',
                'inventory.data-stock.store',
                'inventory.data-stock.edit',
                'inventory.data-stock.update',
                'inventory.data-inventory.index',
                'inventory.data-inventory.show',
                'inventory.data-inventory.create',
                'inventory.data-inventory.store',
                'inventory.data-inventory.edit',
                'inventory.data-inventory.update',
                'inventory.data-inventory.import.*',
                'inventory.farmasi-kedaluwarsa.index',
                // Tidak termasuk destroy, delete - harus di-checklist secara eksplisit
                'transaction.distribusi.index',
                'transaction.distribusi.show',
                // Tidak termasuk create, store, edit, update, destroy, delete - harus di-checklist secara eksplisit
                'transaction.penerimaan-barang.index',
                'transaction.penerimaan-barang.show',
                'transaction.penerimaan-barang.create',
                'transaction.penerimaan-barang.store',
                'transaction.penerimaan-barang.edit',
                'transaction.penerimaan-barang.update',
                // Tidak termasuk destroy, delete - harus di-checklist secara eksplisit
                'transaction.approval.index',
                'transaction.approval.show',
                'transaction.approval.disposisi', // Bisa melihat disposisi
                'transaction.peminjaman-barang.index',
                'transaction.peminjaman-barang.show',
                'transaction.pengembalian-barang.index',
                'transaction.peminjaman-barang.approve-pengurus',
                'transaction.peminjaman-barang.reject-pengurus',
                'transaction.peminjaman-barang.serah-terima',
                'transaction.peminjaman-barang.selesai',
                'asset.register-aset.index',
                'asset.register-aset.show',
                'asset.register-aset.create',
                'asset.register-aset.store',
                'asset.register-aset.edit',
                'asset.register-aset.update',
                // Tidak termasuk destroy, delete - harus di-checklist secara eksplisit
                'reports.stock-gudang',
                'reports.kartu-stok',
                'reports.kartu-stok.merk-breakdown',
                'master.gudang.index',
                'master.gudang.show',
                'master-data.data-barang.index',
                'master-data.data-barang.show',
                'master-data.data-barang.create',
                'master-data.data-barang.store',
                'master-data.data-barang.edit',
                'master-data.data-barang.update',
                // Tidak termasuk destroy, delete - harus di-checklist secara eksplisit
            ],

            // 6b. ADMIN GUDANG PER KATEGORI (filter di controller: aset/persediaan/farmasi hanya akses kategorinya)
            'admin_gudang_aset' => $adminGudangCorePermissions,
            'admin_gudang_persediaan' => $adminGudangCorePermissions,
            'admin_gudang_farmasi' => array_merge(
                ['inventory.farmasi-kedaluwarsa.index'],
                $adminGudangCorePermissions
            ),
            // 6c. ADMIN GUDANG UNIT (hanya akses gudang unit kerjanya, tidak bisa gudang pusat)
            'admin_gudang_unit' => [
                'inventory.data-stock.index', 'inventory.data-stock.merk-breakdown', 'inventory.data-stock.show',
                'inventory.farmasi-kedaluwarsa.index',
                'transaction.penerimaan-barang.index', 'transaction.penerimaan-barang.show', 'transaction.penerimaan-barang.create', 'transaction.penerimaan-barang.store', 'transaction.penerimaan-barang.edit', 'transaction.penerimaan-barang.update',
                'transaction.retur-barang.index', 'transaction.retur-barang.show', 'transaction.retur-barang.create', 'transaction.retur-barang.store',
                'transaction.pengembalian-barang.index',
                'transaction.peminjaman-barang.index', 'transaction.peminjaman-barang.show', 'transaction.peminjaman-barang.serah-terima',
                'asset.register-aset.index', 'asset.register-aset.show', 'asset.register-aset.edit', 'asset.register-aset.update',
                'reports.stock-gudang', 'reports.kartu-stok', 'reports.kartu-stok.merk-breakdown',
            ],

            // 7. UNIT TERKAIT
            'perencanaan' => $approvalDisposisiPermissions,
            'admin_perencanaan' => array_merge($approvalDisposisiPermissions, ['planning.*']),
            'pengadaan' => $approvalDisposisiPermissions,
            'admin_pengadaan_apbd' => array_merge($approvalDisposisiPermissions, ['procurement.*']),
            'admin_pengadaan_blud' => array_merge($approvalDisposisiPermissions, ['procurement.*']),
            'pphp' => [
                'transaction.approval.index',
                'transaction.approval.show',
                'procurement.*',
            ],
            'admin_pphp' => [
                'transaction.approval.index',
                'transaction.approval.show',
                'procurement.*',
            ],
            'keuangan' => $approvalDisposisiPermissions,
            'admin_keuangan' => array_merge($approvalDisposisiPermissions, ['finance.*']),
            'pptk_apbd' => $pptkPermissions,
            'pptk_blud' => $pptkPermissions,
            'admin_pptk_apbd' => $pptkPermissions,
            'admin_pptk_blud' => $pptkPermissions,
        ];
    }
}
