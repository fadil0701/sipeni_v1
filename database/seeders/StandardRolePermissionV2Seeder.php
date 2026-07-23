<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\PermissionRegistrar;

class StandardRolePermissionV2Seeder extends Seeder
{
    public function run(): void
    {
        // Ensure all route-name permissions exist before assigning
        Artisan::call('permission:sync-routes', ['--force' => true]);

        $roles = Role::query()->pluck('id', 'name');
        if ($roles->isEmpty()) {
            $this->command?->warn('No roles found; skipping StandardRolePermissionV2Seeder.');
            return;
        }

        $allPermissions = Permission::query()->pluck('id', 'name');
        if ($allPermissions->isEmpty()) {
            $this->command?->warn('No permissions found; skipping StandardRolePermissionV2Seeder.');
            return;
        }

        // helper: filter by prefix (route-name format)
        $byPrefix = function (string $prefix) use ($allPermissions): Collection {
            $ids = [];
            foreach ($allPermissions as $name => $id) {
                if (str_starts_with((string) $name, $prefix)) {
                    $ids[] = (int) $id;
                }
            }
            return collect($ids)->unique()->values();
        };

        // helper: resolve explicit permission names (route-name format)
        $explicit = function (array $names) use ($allPermissions): Collection {
            $ids = [];
            foreach ($names as $name) {
                $id = $allPermissions[(string) $name] ?? null;
                if ($id) {
                    $ids[] = (int) $id;
                }
            }
            return collect($ids)->unique()->values();
        };

        $allPermissionIds = $allPermissions->map(fn ($id) => (int) $id)->values()->all();

        $permissionMap = [

            'super_administrator' => $allPermissionIds,

            // ===== kepala_pusat: approval + laporan =====
            'kepala_pusat' => $explicit([
                'user.dashboard',
                'transaction.approval.index', 'transaction.approval.show',
                'transaction.approval.approve', 'transaction.approval.reject', 'transaction.approval.mengetahui',
                'transaction.draft-distribusi.index', 'transaction.draft-distribusi.show',
                'maintenance.permintaan-pemeliharaan.index', 'maintenance.permintaan-pemeliharaan.show',
                'reports.index', 'reports.kartu-stok', 'reports.kartu-stok.merk-breakdown',
                'reports.stock-gudang', 'reports.stock-gudang.export',
                'reports.transaksi-summary', 'reports.aset-summary', 'reports.maintenance-summary',
                'reports.maintenance-summary.export',
            ]),

            // ===== kasubbag_tu: verifikasi + monitoring =====
            'kasubbag_tu' => $explicit([
                'user.dashboard',
                'transaction.approval.index', 'transaction.approval.show',
                'transaction.approval.verifikasi', 'transaction.approval.mengetahui',
                'transaction.approval.kembalikan',
                'inventory.data-stock.index', 'inventory.data-stock.merk-breakdown',
                'inventory.farmasi-kedaluwarsa.index',
                'inventory.data-inventory.index', 'inventory.data-inventory.show',
                'transaction.draft-distribusi.index', 'transaction.draft-distribusi.show',
                'transaction.distribusi.index', 'transaction.distribusi.show',
                'reports.index', 'reports.kartu-stok', 'reports.kartu-stok.merk-breakdown',
            ]),

            // ===== kepala_unit: approval unit sendiri =====
            'kepala_unit' => $explicit([
                'user.dashboard',
                'transaction.permintaan-barang.index', 'transaction.permintaan-barang.show',
                'transaction.peminjaman-barang.index', 'transaction.peminjaman-barang.show',
                'transaction.peminjaman-barang.verifikasi-unit-a', 'transaction.peminjaman-barang.approve-unit-b',
                'transaction.peminjaman-barang.approve-pengurus',
                'transaction.approval.index', 'transaction.approval.show', 'transaction.approval.mengetahui',
                'maintenance.permintaan-pemeliharaan.index', 'maintenance.permintaan-pemeliharaan.show',
                'inventory.data-stock.index',
                'inventory.data-inventory.index', 'inventory.data-inventory.show',
                'asset.register-aset.index', 'asset.register-aset.show',
                'transaction.penerimaan-barang.index', 'transaction.penerimaan-barang.show',
                'transaction.retur-barang.index', 'transaction.retur-barang.show',
                'transaction.draft-distribusi.index', 'transaction.draft-distribusi.show',
                'transaction.distribusi.index', 'transaction.distribusi.show',
            ]),

            // ===== admin_unit: full unit operation =====
            'admin_unit' => $explicit([
                'user.dashboard',
                'user.assets.index', 'user.assets.show',
                'user.requests.index', 'user.requests.create', 'user.requests.store', 'user.requests.show',
                'transaction.permintaan-barang.index', 'transaction.permintaan-barang.create',
                'transaction.permintaan-barang.store', 'transaction.permintaan-barang.edit',
                'transaction.permintaan-barang.update', 'transaction.permintaan-barang.show',
                'transaction.permintaan-barang.ajukan',
                'transaction.peminjaman-barang.index', 'transaction.peminjaman-barang.create',
                'transaction.peminjaman-barang.store', 'transaction.peminjaman-barang.show',
                'transaction.pengembalian-barang.index',
                'transaction.peminjaman-barang.pengembalian.create', 'transaction.peminjaman-barang.pengembalian',
                'inventory.data-stock.index',
                'inventory.data-inventory.index', 'inventory.data-inventory.show',
                'inventory.farmasi-kedaluwarsa.index',
                'asset.register-aset.index', 'asset.register-aset.show',
                'transaction.penerimaan-barang.index', 'transaction.penerimaan-barang.show',
                'transaction.penerimaan-barang.edit', 'transaction.penerimaan-barang.update',
                'transaction.penerimaan-barang.verify',
                'transaction.retur-barang.index', 'transaction.retur-barang.show',
                'transaction.retur-barang.create', 'transaction.retur-barang.store',
                'transaction.retur-barang.edit', 'transaction.retur-barang.update',
                'transaction.retur-barang.destroy', 'transaction.retur-barang.ajukan',
                'planning.rku.index', 'planning.rku.create', 'planning.rku.store',
                'planning.rku.edit', 'planning.rku.update', 'planning.rku.submit',
                'maintenance.permintaan-pemeliharaan.index',
                'maintenance.permintaan-pemeliharaan.create', 'maintenance.permintaan-pemeliharaan.store',
                'maintenance.permintaan-pemeliharaan.edit', 'maintenance.permintaan-pemeliharaan.update',
                'maintenance.permintaan-pemeliharaan.show',
                'maintenance.permintaan-pemeliharaan.ajukan',
                'reports.kartu-stok',
            ]),

            // ===== perencana: planning full + approval =====
            'perencana' => $explicit([
                'user.dashboard',
                'planning.rku.index', 'planning.rku.create', 'planning.rku.store',
                'planning.rku.edit', 'planning.rku.update', 'planning.rku.destroy',
                'planning.rku.submit', 'planning.rku.approve', 'planning.rku.reject',
                'planning.rku.cancel', 'planning.rku.revise',
                'planning.rekap-tahunan',
                'transaction.approval.index', 'transaction.approval.show', 'transaction.approval.disposisi',
                'master.program.index', 'master.kegiatan.index', 'master.sub-kegiatan.index',
                'reports.index', 'reports.kartu-stok', 'reports.stock-gudang',
                'reports.transaksi-summary',
            ]),

            // ===== pengadaan: procurement + approval =====
            'pengadaan' => $explicit([
                'user.dashboard',
                'procurement.paket-pengadaan.index', 'procurement.paket-pengadaan.show',
                'procurement.paket-pengadaan.create', 'procurement.paket-pengadaan.store',
                'procurement.paket-pengadaan.edit', 'procurement.paket-pengadaan.update',
                'procurement.paket-pengadaan.destroy',
                'procurement.proses-pengadaan.index', 'procurement.proses-pengadaan.show',
                'transaction.approval.index', 'transaction.approval.show', 'transaction.approval.disposisi',
                'reports.index',
            ]),

            // ===== keuangan: finance + approval =====
            'keuangan' => $explicit([
                'user.dashboard',
                'finance.pembayaran.index', 'finance.pembayaran.show',
                'finance.pembayaran.create', 'finance.pembayaran.store',
                'finance.pembayaran.edit', 'finance.pembayaran.update',
                'finance.pembayaran.destroy',
                'transaction.approval.index', 'transaction.approval.show', 'transaction.approval.disposisi',
                'reports.index',
            ]),

            // ===== pptk_apbd: monitoring planning + procurement + laporan =====
            'pptk_apbd' => $explicit([
                'user.dashboard',
                'planning.rku.index', 'planning.rku.show', 'planning.rekap-tahunan',
                'planning.rku.approve', 'planning.rku.reject', 'planning.rku.cancel',
                'procurement.paket-pengadaan.index', 'procurement.paket-pengadaan.show',
                'procurement.proses-pengadaan.index', 'procurement.proses-pengadaan.show',
                'reports.index', 'reports.kartu-stok', 'reports.stock-gudang',
                'reports.transaksi-summary',
            ]),

            // ===== pptk_blud: same as pptk_apbd =====
            'pptk_blud' => $explicit([
                'user.dashboard',
                'planning.rku.index', 'planning.rku.show', 'planning.rekap-tahunan',
                'planning.rku.approve', 'planning.rku.reject', 'planning.rku.cancel',
                'procurement.paket-pengadaan.index', 'procurement.paket-pengadaan.show',
                'procurement.proses-pengadaan.index', 'procurement.proses-pengadaan.show',
                'reports.index', 'reports.kartu-stok', 'reports.stock-gudang',
                'reports.transaksi-summary',
            ]),

            // ===== pengurus_barang: inventory + distribusi + aset + pemeliharaan + laporan =====
            'pengurus_barang' => $byPrefix('inventory.')
                ->merge($byPrefix('asset.'))
                ->merge($byPrefix('master-data.'))
                ->merge($byPrefix('transaction.permintaan-barang.'))
                ->merge($byPrefix('transaction.draft-distribusi.'))
                ->merge($byPrefix('transaction.compile-distribusi.'))
                ->merge($byPrefix('transaction.distribusi.'))
                ->merge($byPrefix('transaction.penerimaan-barang.'))
                ->merge($byPrefix('transaction.retur-barang.'))
                ->merge($byPrefix('transaction.approval.'))
                ->merge($byPrefix('transaction.peminjaman-barang.'))
                ->merge($byPrefix('maintenance.permintaan-pemeliharaan.'))
                ->merge($byPrefix('maintenance.service-report.'))
                ->merge($byPrefix('maintenance.jadwal-maintenance.'))
                ->merge($byPrefix('maintenance.kalibrasi-aset.'))
                ->merge($byPrefix('reports.'))
                ->merge($explicit([
                    'user.dashboard',
                    'maintenance.daftar-permintaan-pemeliharaan.index',
                    'master.gudang.index', 'master.gudang.show', 'master.gudang.create',
                    'master.gudang.store', 'master.gudang.edit', 'master.gudang.update',
                    'master.gudang.destroy',
                    'master.ruangan.index', 'master.ruangan.show',
                ])),

            // ===== teknisi_atem / teknisi_it: operasional pemeliharaan =====
            'teknisi_atem' => $byPrefix('maintenance.service-report.')
                ->merge($byPrefix('maintenance.jadwal-maintenance.'))
                ->merge($byPrefix('maintenance.kalibrasi-aset.'))
                ->merge($explicit([
                    'user.dashboard',
                    'maintenance.daftar-permintaan-pemeliharaan.index',
                    'maintenance.permintaan-pemeliharaan.show',
                    'maintenance.permintaan-pemeliharaan.lanjut-perbaikan',
                    'asset.register-aset.index',
                    'asset.register-aset.show',
                ])),
            'teknisi_it' => $byPrefix('maintenance.service-report.')
                ->merge($byPrefix('maintenance.jadwal-maintenance.'))
                ->merge($byPrefix('maintenance.kalibrasi-aset.'))
                ->merge($explicit([
                    'user.dashboard',
                    'maintenance.daftar-permintaan-pemeliharaan.index',
                    'maintenance.permintaan-pemeliharaan.show',
                    'maintenance.permintaan-pemeliharaan.lanjut-perbaikan',
                    'asset.register-aset.index',
                    'asset.register-aset.show',
                ])),

            // ===== admin_gudang_pusat: inventory full + distribusi penuh =====
            'admin_gudang_pusat' => $byPrefix('inventory.')
                ->merge($byPrefix('asset.'))
                ->merge($byPrefix('master-data.'))
                ->merge($byPrefix('transaction.permintaan-barang.'))
                ->merge($byPrefix('transaction.draft-distribusi.'))
                ->merge($byPrefix('transaction.compile-distribusi.'))
                ->merge($byPrefix('transaction.distribusi.'))
                ->merge($byPrefix('transaction.penerimaan-barang.'))
                ->merge($byPrefix('transaction.retur-barang.'))
                ->merge($byPrefix('transaction.approval.'))
                ->merge($byPrefix('transaction.peminjaman-barang.'))
                ->merge($byPrefix('reports.'))
                ->merge($explicit([
                    'user.dashboard',
                    'master.gudang.index', 'master.gudang.show', 'master.gudang.create',
                    'master.gudang.store', 'master.gudang.edit', 'master.gudang.update',
                    'master.gudang.destroy',
                    'master.ruangan.index', 'master.ruangan.show',
                ])),

            // ===== admin_gudang_aset: inventory aset + aset register =====
            'admin_gudang_aset' => $explicit([
                'user.dashboard',
                'inventory.data-stock.index', 'inventory.data-stock.show',
                'inventory.data-stock.merk-breakdown',
                'inventory.data-inventory.index', 'inventory.data-inventory.create',
                'inventory.data-inventory.store', 'inventory.data-inventory.edit',
                'inventory.data-inventory.update', 'inventory.data-inventory.show',
                'inventory.data-inventory.destroy',
                'inventory.data-inventory.import.index', 'inventory.data-inventory.import.import',
                'inventory.data-inventory.import.template',
                'asset.register-aset.index', 'asset.register-aset.create', 'asset.register-aset.store',
                'asset.register-aset.edit', 'asset.register-aset.update',
                'asset.register-aset.show', 'asset.register-aset.destroy',
                'asset.register-aset.unit-kerja.show',
                'asset.kartu-inventaris-ruangan.index', 'asset.kartu-inventaris-ruangan.show',
                'asset.kartu-inventaris-ruangan.create', 'asset.kartu-inventaris-ruangan.store',
                'asset.kartu-inventaris-ruangan.edit', 'asset.kartu-inventaris-ruangan.update',
                'asset.kartu-inventaris-ruangan.destroy',
                'asset.mutasi-aset.index', 'asset.mutasi-aset.create', 'asset.mutasi-aset.store',
                'transaction.penerimaan-barang.index', 'transaction.penerimaan-barang.show',
                'transaction.penerimaan-barang.edit', 'transaction.penerimaan-barang.update',
                'transaction.penerimaan-barang.verify',
                'transaction.retur-barang.index', 'transaction.retur-barang.show',
                'master-data.aset.index', 'master-data.aset.show',
                'master-data.data-barang.index', 'master-data.data-barang.show',
                'master.gudang.index', 'master.gudang.show',
                'reports.kartu-stok', 'reports.stock-gudang',
            ]),

            // ===== admin_gudang_persediaan: inventory persediaan =====
            'admin_gudang_persediaan' => $explicit([
                'user.dashboard',
                'inventory.data-stock.index', 'inventory.data-stock.show',
                'inventory.data-stock.merk-breakdown',
                'inventory.data-inventory.index', 'inventory.data-inventory.create',
                'inventory.data-inventory.store', 'inventory.data-inventory.edit',
                'inventory.data-inventory.update', 'inventory.data-inventory.show',
                'inventory.data-inventory.destroy',
                'inventory.data-inventory.import.index', 'inventory.data-inventory.import.import',
                'inventory.data-inventory.import.template',
                'inventory.stock-adjustment.index', 'inventory.stock-adjustment.create',
                'inventory.stock-adjustment.store', 'inventory.stock-adjustment.show',
                'inventory.stock-adjustment.edit', 'inventory.stock-adjustment.update',
                'inventory.stock-adjustment.ajukan',
                'transaction.penerimaan-barang.index', 'transaction.penerimaan-barang.show',
                'transaction.penerimaan-barang.edit', 'transaction.penerimaan-barang.update',
                'transaction.penerimaan-barang.verify',
                'transaction.retur-barang.index', 'transaction.retur-barang.show',
                'master-data.data-barang.index', 'master-data.data-barang.show',
                'master.gudang.index', 'master.gudang.show',
                'reports.kartu-stok', 'reports.stock-gudang',
            ]),

            // ===== admin_gudang_farmasi: inventory farmasi + kedaluwarsa =====
            'admin_gudang_farmasi' => $explicit([
                'user.dashboard',
                'inventory.data-stock.index', 'inventory.data-stock.show',
                'inventory.data-stock.merk-breakdown',
                'inventory.data-inventory.index', 'inventory.data-inventory.create',
                'inventory.data-inventory.store', 'inventory.data-inventory.edit',
                'inventory.data-inventory.update', 'inventory.data-inventory.show',
                'inventory.data-inventory.destroy',
                'inventory.data-inventory.import.index', 'inventory.data-inventory.import.import',
                'inventory.data-inventory.import.template',
                'inventory.farmasi-kedaluwarsa.index', 'inventory.farmasi-kedaluwarsa.export',
                'inventory.stock-adjustment.index', 'inventory.stock-adjustment.create',
                'inventory.stock-adjustment.store', 'inventory.stock-adjustment.show',
                'inventory.stock-adjustment.edit', 'inventory.stock-adjustment.update',
                'inventory.stock-adjustment.ajukan',
                'transaction.penerimaan-barang.index', 'transaction.penerimaan-barang.show',
                'transaction.penerimaan-barang.edit', 'transaction.penerimaan-barang.update',
                'transaction.penerimaan-barang.verify',
                'transaction.retur-barang.index', 'transaction.retur-barang.show',
                'master-data.data-barang.index', 'master-data.data-barang.show',
                'master.gudang.index', 'master.gudang.show',
                'reports.kartu-stok', 'reports.stock-gudang',
            ]),
        ];

        $touched = 0;
        foreach ($permissionMap as $roleName => $permissionIds) {
            $roleId = $roles[$roleName] ?? null;
            if (!$roleId) {
                $this->command?->warn("Role '{$roleName}' not found; skipping.");
                continue;
            }
            $role = Role::query()->find($roleId);
            if (!$role) {
                continue;
            }

            $ids = collect($permissionIds)->filter(fn ($id) => (int) $id > 0)->unique()->values()->all();
            if ($ids === []) {
                continue;
            }

            $role->permissions()->syncWithoutDetaching($ids);
            $touched++;
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->command?->info("Standard role-permissions applied to {$touched} roles.");
    }
}
