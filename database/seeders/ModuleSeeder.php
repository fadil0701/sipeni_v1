<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            [
                'name' => 'master-manajemen',
                'display_name' => 'Master Manajemen',
                'description' => 'Pengelolaan master data manajemen (Unit Kerja, Lokasi, Ruangan, Gudang, Program, Kegiatan, Sub Kegiatan)',
                'icon' => 'building-office',
                'sort_order' => 10,
            ],
            [
                'name' => 'master-data',
                'display_name' => 'Master Data',
                'description' => 'Pengelolaan master data barang (Aset, Kode Barang, Kategori, Jenis, Sub Jenis, Data Barang, Satuan, Sumber Anggaran)',
                'icon' => 'database',
                'sort_order' => 20,
            ],
            [
                'name' => 'inventory',
                'display_name' => 'Inventory',
                'description' => 'Pengelolaan inventory dan stock gudang',
                'icon' => 'archive-box',
                'sort_order' => 100,
            ],
            [
                'name' => 'transaction',
                'display_name' => 'Transaksi',
                'description' => 'Pengelolaan transaksi (Permintaan, Approval, Distribusi, Penerimaan, Retur)',
                'icon' => 'arrow-path',
                'sort_order' => 200,
            ],
            [
                'name' => 'asset',
                'display_name' => 'Aset & KIR',
                'description' => 'Pengelolaan register aset dan kartu inventaris ruangan',
                'icon' => 'document-duplicate',
                'sort_order' => 300,
            ],
            [
                'name' => 'maintenance',
                'display_name' => 'Pemeliharaan',
                'description' => 'Pengelolaan pemeliharaan dan kalibrasi aset',
                'icon' => 'wrench-screwdriver',
                'sort_order' => 400,
            ],
            [
                'name' => 'planning',
                'display_name' => 'Perencanaan',
                'description' => 'Pengelolaan perencanaan kebutuhan unit (RKU)',
                'icon' => 'calendar',
                'sort_order' => 500,
            ],
            [
                'name' => 'procurement',
                'display_name' => 'Pengadaan',
                'description' => 'Pengelolaan pengadaan barang dan jasa',
                'icon' => 'shopping-cart',
                'sort_order' => 600,
            ],
            [
                'name' => 'finance',
                'display_name' => 'Keuangan',
                'description' => 'Pengelolaan keuangan dan pembayaran',
                'icon' => 'currency-dollar',
                'sort_order' => 700,
            ],
            [
                'name' => 'reports',
                'display_name' => 'Laporan',
                'description' => 'Laporan dan monitoring',
                'icon' => 'chart-bar',
                'sort_order' => 800,
            ],
        ];

        foreach ($modules as $module) {
            Module::updateOrCreate(
                ['name' => $module['name']],
                $module
            );
        }
    }
}
