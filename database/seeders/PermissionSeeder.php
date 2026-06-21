<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Dashboard
            [
                'name' => 'user.dashboard',
                'display_name' => 'View Dashboard',
                'module' => 'dashboard',
                'group' => 'dashboard',
                'description' => 'Akses ke dashboard user',
                'sort_order' => 1,
            ],

            // Master Manajemen
            [
                'name' => 'master-manajemen.master-pegawai.index',
                'display_name' => 'View Master Pegawai',
                'module' => 'master-manajemen',
                'group' => 'master-manajemen.master-pegawai',
                'description' => 'Melihat daftar master pegawai',
                'sort_order' => 10,
            ],
            [
                'name' => 'master-manajemen.master-pegawai.create',
                'display_name' => 'Create Master Pegawai',
                'module' => 'master-manajemen',
                'group' => 'master-manajemen.master-pegawai',
                'description' => 'Membuat master pegawai baru',
                'sort_order' => 11,
            ],
            [
                'name' => 'master-manajemen.master-pegawai.edit',
                'display_name' => 'Edit Master Pegawai',
                'module' => 'master-manajemen',
                'group' => 'master-manajemen.master-pegawai',
                'description' => 'Mengedit master pegawai',
                'sort_order' => 12,
            ],
            [
                'name' => 'master-manajemen.master-pegawai.delete',
                'display_name' => 'Delete Master Pegawai',
                'module' => 'master-manajemen',
                'group' => 'master-manajemen.master-pegawai',
                'description' => 'Menghapus master pegawai',
                'sort_order' => 13,
            ],
            [
                'name' => 'master-manajemen.master-jabatan.index',
                'display_name' => 'View Master Jabatan',
                'module' => 'master-manajemen',
                'group' => 'master-manajemen.master-jabatan',
                'description' => 'Melihat daftar master jabatan',
                'sort_order' => 20,
            ],
            [
                'name' => 'master-manajemen.master-jabatan.create',
                'display_name' => 'Create Master Jabatan',
                'module' => 'master-manajemen',
                'group' => 'master-manajemen.master-jabatan',
                'description' => 'Membuat master jabatan baru',
                'sort_order' => 21,
            ],
            [
                'name' => 'master-manajemen.master-jabatan.edit',
                'display_name' => 'Edit Master Jabatan',
                'module' => 'master-manajemen',
                'group' => 'master-manajemen.master-jabatan',
                'description' => 'Mengedit master jabatan',
                'sort_order' => 22,
            ],
            [
                'name' => 'master-manajemen.master-jabatan.delete',
                'display_name' => 'Delete Master Jabatan',
                'module' => 'master-manajemen',
                'group' => 'master-manajemen.master-jabatan',
                'description' => 'Menghapus master jabatan',
                'sort_order' => 23,
            ],
            [
                'name' => 'master.unit-kerja.*',
                'display_name' => 'Master Unit Kerja (All)',
                'module' => 'master-manajemen',
                'group' => 'master.unit-kerja',
                'description' => 'Akses penuh ke master unit kerja',
                'sort_order' => 30,
            ],
            [
                'name' => 'master.gudang.*',
                'display_name' => 'Master Gudang (All)',
                'module' => 'master-manajemen',
                'group' => 'master.gudang',
                'description' => 'Akses penuh ke master gudang',
                'sort_order' => 40,
            ],
            [
                'name' => 'master.ruangan.*',
                'display_name' => 'Master Ruangan (All)',
                'module' => 'master-manajemen',
                'group' => 'master.ruangan',
                'description' => 'Akses penuh ke master ruangan',
                'sort_order' => 50,
            ],

            // Inventory
            [
                'name' => 'inventory.data-stock.index',
                'display_name' => 'View Data Stock',
                'module' => 'inventory',
                'group' => 'inventory.data-stock',
                'description' => 'Melihat data stock gudang',
                'sort_order' => 100,
            ],
            [
                'name' => 'inventory.data-stock.merk-breakdown',
                'display_name' => 'Rincian Stok per Merk',
                'module' => 'inventory',
                'group' => 'inventory.data-stock',
                'description' => 'Melihat pecahan stok per merk untuk satu barang dan gudang',
                'sort_order' => 101,
            ],
            [
                'name' => 'inventory.farmasi-kedaluwarsa.index',
                'display_name' => 'Reminder Kedaluwarsa Farmasi',
                'module' => 'inventory',
                'group' => 'inventory.farmasi-kedaluwarsa',
                'description' => 'Melihat KPI dan daftar batch farmasi berdasarkan tanggal kedaluwarsa',
                'sort_order' => 102,
            ],
            [
                'name' => 'inventory.farmasi-kedaluwarsa.export',
                'display_name' => 'Ekspor Reminder Kedaluwarsa Farmasi (CSV)',
                'module' => 'inventory',
                'group' => 'inventory.farmasi-kedaluwarsa',
                'description' => 'Mengunduh daftar reminder kedaluwarsa farmasi sebagai CSV',
                'sort_order' => 103,
            ],
            [
                'name' => 'inventory.data-inventory.index',
                'display_name' => 'View Data Inventory',
                'module' => 'inventory',
                'group' => 'inventory.data-inventory',
                'description' => 'Melihat daftar data inventory',
                'sort_order' => 110,
            ],
            [
                'name' => 'inventory.data-inventory.create',
                'display_name' => 'Create Data Inventory',
                'module' => 'inventory',
                'group' => 'inventory.data-inventory',
                'description' => 'Membuat data inventory baru',
                'sort_order' => 111,
            ],
            [
                'name' => 'inventory.data-inventory.edit',
                'display_name' => 'Edit Data Inventory',
                'module' => 'inventory',
                'group' => 'inventory.data-inventory',
                'description' => 'Mengedit data inventory',
                'sort_order' => 112,
            ],
            [
                'name' => 'inventory.data-inventory.delete',
                'display_name' => 'Delete Data Inventory',
                'module' => 'inventory',
                'group' => 'inventory.data-inventory',
                'description' => 'Menghapus data inventory',
                'sort_order' => 113,
            ],
            [
                'name' => 'inventory.data-inventory.import.*',
                'display_name' => 'Import Data Inventory',
                'module' => 'inventory',
                'group' => 'inventory.data-inventory',
                'description' => 'Mengunggah data inventory melalui berkas (Excel)',
                'sort_order' => 114,
            ],
            [
                'name' => 'inventory.inventory-item.*',
                'display_name' => 'Inventory Item (All)',
                'module' => 'inventory',
                'group' => 'inventory.inventory-item',
                'description' => 'Akses penuh ke inventory item',
                'sort_order' => 120,
            ],

            // Permintaan Barang
            [
                'name' => 'transaction.permintaan-barang.index',
                'display_name' => 'View Permintaan Barang',
                'module' => 'permintaan',
                'group' => 'transaction.permintaan-barang',
                'description' => 'Melihat daftar permintaan barang',
                'sort_order' => 200,
            ],
            [
                'name' => 'transaction.permintaan-barang.create',
                'display_name' => 'Create Permintaan Barang',
                'module' => 'permintaan',
                'group' => 'transaction.permintaan-barang',
                'description' => 'Membuat permintaan barang baru',
                'sort_order' => 201,
            ],
            [
                'name' => 'transaction.permintaan-barang.edit',
                'display_name' => 'Edit Permintaan Barang',
                'module' => 'permintaan',
                'group' => 'transaction.permintaan-barang',
                'description' => 'Mengedit permintaan barang',
                'sort_order' => 202,
            ],
            [
                'name' => 'transaction.permintaan-barang.show',
                'display_name' => 'View Detail Permintaan Barang',
                'module' => 'permintaan',
                'group' => 'transaction.permintaan-barang',
                'description' => 'Melihat detail permintaan barang',
                'sort_order' => 203,
            ],

            // Approval
            [
                'name' => 'transaction.approval.index',
                'display_name' => 'View Approval',
                'module' => 'approval',
                'group' => 'transaction.approval',
                'description' => 'Melihat daftar approval',
                'sort_order' => 210,
            ],
            [
                'name' => 'transaction.approval.show',
                'display_name' => 'View Detail Approval',
                'module' => 'approval',
                'group' => 'transaction.approval',
                'description' => 'Melihat detail approval',
                'sort_order' => 211,
            ],
            [
                'name' => 'transaction.approval.mengetahui',
                'display_name' => 'Mengetahui Approval',
                'module' => 'approval',
                'group' => 'transaction.approval',
                'description' => 'Memberi status mengetahui pada approval',
                'sort_order' => 212,
            ],
            [
                'name' => 'transaction.approval.verifikasi',
                'display_name' => 'Verifikasi Approval',
                'module' => 'approval',
                'group' => 'transaction.approval',
                'description' => 'Memverifikasi approval',
                'sort_order' => 213,
            ],
            [
                'name' => 'transaction.approval.approve',
                'display_name' => 'Approve Request',
                'module' => 'approval',
                'group' => 'transaction.approval',
                'description' => 'Menyetujui permintaan',
                'sort_order' => 214,
            ],
            [
                'name' => 'transaction.approval.reject',
                'display_name' => 'Reject Request',
                'module' => 'approval',
                'group' => 'transaction.approval',
                'description' => 'Menolak permintaan',
                'sort_order' => 215,
            ],

            // Distribusi (pisah)
            [
                'name' => 'transaction.distribusi.*',
                'display_name' => 'Distribusi Barang (All)',
                'module' => 'distribusi',
                'group' => 'transaction.distribusi',
                'description' => 'Akses penuh ke distribusi barang',
                'sort_order' => 220,
            ],

            // Penerimaan Barang (pisah)
            [
                'name' => 'transaction.penerimaan-barang.*',
                'display_name' => 'Penerimaan Barang (All)',
                'module' => 'penerimaan-barang',
                'group' => 'transaction.penerimaan-barang',
                'description' => 'Akses penuh ke penerimaan barang',
                'sort_order' => 230,
            ],

            // Retur Barang (pisah)
            [
                'name' => 'transaction.retur.*',
                'display_name' => 'Retur Barang (All)',
                'module' => 'retur-barang',
                'group' => 'transaction.retur',
                'description' => 'Akses penuh ke retur barang',
                'sort_order' => 240,
            ],

            // Draft Distribusi (pisah, bukan Transaction)
            [
                'name' => 'transaction.draft-distribusi.index',
                'display_name' => 'View Draft Distribusi',
                'module' => 'draft-distribusi',
                'group' => 'transaction.draft-distribusi',
                'description' => 'Melihat daftar draft distribusi',
                'sort_order' => 250,
            ],
            [
                'name' => 'transaction.draft-distribusi.create',
                'display_name' => 'Create Draft Distribusi',
                'module' => 'draft-distribusi',
                'group' => 'transaction.draft-distribusi',
                'description' => 'Membuat draft distribusi',
                'sort_order' => 251,
            ],
            [
                'name' => 'transaction.draft-distribusi.show',
                'display_name' => 'View Detail Draft Distribusi',
                'module' => 'draft-distribusi',
                'group' => 'transaction.draft-distribusi',
                'description' => 'Melihat detail draft distribusi',
                'sort_order' => 252,
            ],
            [
                'name' => 'transaction.draft-distribusi.*',
                'display_name' => 'Draft Distribusi (All)',
                'module' => 'draft-distribusi',
                'group' => 'transaction.draft-distribusi',
                'description' => 'Akses penuh ke draft distribusi',
                'sort_order' => 253,
            ],

            // Compile Distribusi (pisah, bukan Transaction)
            [
                'name' => 'transaction.compile-distribusi.index',
                'display_name' => 'View Compile Distribusi',
                'module' => 'compile-distribusi',
                'group' => 'transaction.compile-distribusi',
                'description' => 'Melihat daftar compile distribusi',
                'sort_order' => 260,
            ],
            [
                'name' => 'transaction.compile-distribusi.create',
                'display_name' => 'Create Compile Distribusi',
                'module' => 'compile-distribusi',
                'group' => 'transaction.compile-distribusi',
                'description' => 'Membuat compile distribusi',
                'sort_order' => 261,
            ],
            [
                'name' => 'transaction.compile-distribusi.*',
                'display_name' => 'Compile Distribusi (All)',
                'module' => 'compile-distribusi',
                'group' => 'transaction.compile-distribusi',
                'description' => 'Akses penuh ke compile distribusi',
                'sort_order' => 262,
            ],

            // Asset & KIR
            [
                'name' => 'asset.register-aset.*',
                'display_name' => 'Register Aset (All)',
                'module' => 'asset',
                'group' => 'asset.register-aset',
                'description' => 'Akses penuh ke register aset',
                'sort_order' => 300,
            ],

            // Reports
            [
                'name' => 'reports.*',
                'display_name' => 'Reports (All)',
                'module' => 'reports',
                'group' => 'reports',
                'description' => 'Akses penuh ke semua laporan',
                'sort_order' => 400,
            ],
            [
                'name' => 'reports.stock-gudang',
                'display_name' => 'View Stock Gudang Report',
                'module' => 'reports',
                'group' => 'reports',
                'description' => 'Melihat laporan stock gudang',
                'sort_order' => 401,
            ],
            [
                'name' => 'reports.kartu-stok',
                'display_name' => 'View Kartu Stok',
                'module' => 'reports',
                'group' => 'reports',
                'description' => 'Melihat laporan kartu stok',
                'sort_order' => 402,
            ],
            [
                'name' => 'reports.kartu-stok.merk-breakdown',
                'display_name' => 'Rincian Kartu Stok per Merk',
                'module' => 'reports',
                'group' => 'reports',
                'description' => 'Melihat pecahan stok per merk dari kartu stok',
                'sort_order' => 403,
            ],

            // Planning - RKU
            [
                'name' => 'planning.rku.index',
                'display_name' => 'View RKU List',
                'module' => 'planning',
                'group' => 'planning.rku',
                'description' => 'Melihat daftar RKU',
                'sort_order' => 200,
            ],
            [
                'name' => 'planning.rku.view_all',
                'display_name' => 'View All RKU',
                'module' => 'planning',
                'group' => 'planning.rku',
                'description' => 'Melihat semua RKU dari semua unit kerja',
                'sort_order' => 201,
            ],
            [
                'name' => 'planning.rku.create',
                'display_name' => 'Create RKU',
                'module' => 'planning',
                'group' => 'planning.rku',
                'description' => 'Membuat RKU baru',
                'sort_order' => 202,
            ],
            [
                'name' => 'planning.rku.update',
                'display_name' => 'Update RKU',
                'module' => 'planning',
                'group' => 'planning.rku',
                'description' => 'Mengedit RKU',
                'sort_order' => 203,
            ],
            [
                'name' => 'planning.rku.update_all',
                'display_name' => 'Update All RKU',
                'module' => 'planning',
                'group' => 'planning.rku',
                'description' => 'Mengedit semua RKU dari semua unit kerja',
                'sort_order' => 204,
            ],
            [
                'name' => 'planning.rku.delete',
                'display_name' => 'Delete RKU',
                'module' => 'planning',
                'group' => 'planning.rku',
                'description' => 'Menghapus RKU',
                'sort_order' => 205,
            ],
            [
                'name' => 'planning.rku.delete_all',
                'display_name' => 'Delete All RKU',
                'module' => 'planning',
                'group' => 'planning.rku',
                'description' => 'Menghapus semua RKU dari semua unit kerja',
                'sort_order' => 206,
            ],
            [
                'name' => 'planning.rku.submit',
                'display_name' => 'Submit RKU',
                'module' => 'planning',
                'group' => 'planning.rku',
                'description' => 'Mengajukan RKU untuk approval',
                'sort_order' => 207,
            ],
            [
                'name' => 'planning.rku.submit_all',
                'display_name' => 'Submit All RKU',
                'module' => 'planning',
                'group' => 'planning.rku',
                'description' => 'Mengajukan semua RKU untuk approval',
                'sort_order' => 208,
            ],
            [
                'name' => 'planning.rku.approve',
                'display_name' => 'Approve RKU',
                'module' => 'planning',
                'group' => 'planning.rku',
                'description' => 'Menyetujui/mereview RKU',
                'sort_order' => 209,
            ],
            [
                'name' => 'planning.rku.approve_all',
                'display_name' => 'Approve All RKU',
                'module' => 'planning',
                'group' => 'planning.rku',
                'description' => 'Menyetujui semua RKU',
                'sort_order' => 210,
            ],
            [
                'name' => 'planning.rku.reject',
                'display_name' => 'Reject RKU',
                'module' => 'planning',
                'group' => 'planning.rku',
                'description' => 'Menolak RKU',
                'sort_order' => 211,
            ],
            [
                'name' => 'planning.rku.cancel',
                'display_name' => 'Cancel RKU',
                'module' => 'planning',
                'group' => 'planning.rku',
                'description' => 'Membatalkan pengajuan RKU',
                'sort_order' => 212,
            ],
            [
                'name' => 'planning.rku.cancel_all',
                'display_name' => 'Cancel All RKU',
                'module' => 'planning',
                'group' => 'planning.rku',
                'description' => 'Membatalkan semua pengajuan RKU',
                'sort_order' => 213,
            ],
            [
                'name' => 'planning.rku.lock',
                'display_name' => 'Lock RKU',
                'module' => 'planning',
                'group' => 'planning.rku',
                'description' => 'Mengunci RKU',
                'sort_order' => 214,
            ],
            [
                'name' => 'planning.rku.unlock',
                'display_name' => 'Unlock RKU',
                'module' => 'planning',
                'group' => 'planning.rku',
                'description' => 'Membuka kunci RKU',
                'sort_order' => 215,
            ],
            [
                'name' => 'planning.rku.unapprove',
                'display_name' => 'Unapprove RKU',
                'module' => 'planning',
                'group' => 'planning.rku',
                'description' => 'Membatalkan persetujuan RKU',
                'sort_order' => 216,
            ],
            [
                'name' => 'planning.rku.rekap',
                'display_name' => 'View Rekap RKU',
                'module' => 'planning',
                'group' => 'planning.rku',
                'description' => 'Melihat rekap perencanaan tahunan',
                'sort_order' => 217,
            ],
            [
                'name' => 'planning.rku.export',
                'display_name' => 'Export RKU',
                'module' => 'planning',
                'group' => 'planning.rku',
                'description' => 'Mengekspor data RKU',
                'sort_order' => 218,
            ],
            [
                'name' => 'planning.rku.manage',
                'display_name' => 'Manage RKU',
                'module' => 'planning',
                'group' => 'planning.rku',
                'description' => 'Pengelolaan lengkap RKU',
                'sort_order' => 219,
            ],

            // Admin
            [
                'name' => 'admin.roles.*',
                'display_name' => 'Role Management (All)',
                'module' => 'admin',
                'group' => 'admin.roles',
                'description' => 'Akses penuh ke manajemen role',
                'sort_order' => 500,
            ],
            [
                'name' => 'admin.users.*',
                'display_name' => 'User Management (All)',
                'module' => 'admin',
                'group' => 'admin.users',
                'description' => 'Akses penuh ke manajemen user',
                'sort_order' => 510,
            ],
            [
                'name' => 'admin.print-templates.*',
                'display_name' => 'Template Cetak (All)',
                'module' => 'admin',
                'group' => 'admin.print-templates',
                'description' => 'Mengelola template HTML untuk dokumen cetak',
                'sort_order' => 520,
            ],
        ];

        foreach ($permissions as $permission) {
            $row = array_merge(['guard_name' => 'web'], $permission);
            Permission::updateOrCreate(
                ['name' => $permission['name'], 'guard_name' => 'web'],
                $row
            );
        }

        // Perbaiki permission yang masih module=transaction (data lama): sesuaikan dengan modul terbaru (tanpa Transaction)
        $transactionModuleMap = [
            'permintaan' => ['transaction.permintaan-barang'],
            'approval' => ['transaction.approval'],
            'distribusi' => ['transaction.distribusi'],
            'penerimaan-barang' => ['transaction.penerimaan-barang'],
            'retur-barang' => ['transaction.retur'],
            'draft-distribusi' => ['transaction.draft-distribusi'],
            'compile-distribusi' => ['transaction.compile-distribusi'],
        ];
        foreach (Permission::where('module', 'transaction')->get() as $p) {
            foreach ($transactionModuleMap as $newModule => $prefixes) {
                foreach ($prefixes as $prefix) {
                    if (str_starts_with($p->name, $prefix)) {
                        $p->update(['module' => $newModule]);
                        break 2;
                    }
                }
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
