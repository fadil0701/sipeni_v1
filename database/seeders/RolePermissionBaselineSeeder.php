<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\WorkflowPermission;
use App\Models\WorkflowStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Spatie\Permission\PermissionRegistrar;

/**
 * Tahap 2 (Governance):
 * - Tetapkan baseline permission minimum per role (tanpa menghapus assignment manual).
 * - Pastikan role-role baru punya permission yang wajar sehingga menu & akses rute konsisten.
 */
class RolePermissionBaselineSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::query()->pluck('id', 'name');
        if ($roles->isEmpty()) {
            $this->command?->warn('Tidak ada role di database; lewati RolePermissionBaselineSeeder.');
            return;
        }

        $allPermissions = Permission::query()->pluck('id', 'name');
        if ($allPermissions->isEmpty()) {
            $this->command?->warn('Tidak ada permission di database; lewati RolePermissionBaselineSeeder.');
            return;
        }

        $byPrefix = function (string $prefix) use ($allPermissions): Collection {
            $ids = [];
            foreach ($allPermissions as $name => $id) {
                if (str_starts_with((string) $name, $prefix)) {
                    $ids[] = (int) $id;
                }
            }
            return collect($ids)->unique()->values();
        };

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

        // Approval baseline untuk unit-unit governance
        $approvalDisposisi = $explicit([
            'transaction.approval.index',
            'transaction.approval.show',
            'transaction.approval.disposisi',
        ]);

        $approvalViewOnly = $explicit([
            'transaction.approval.index',
            'transaction.approval.show',
        ]);

        // Baseline per role (minimum)
        $baseline = [
            // Pengajuan permintaan barang hanya untuk role pemohon (pegawai) + admin.
            'pegawai' => $explicit([
                'transaction.permintaan-barang.ajukan',
            ]),
            'admin_unit' => $explicit([
                'transaction.permintaan-barang.ajukan',
            ]),
            'admin_gudang_pusat' => $byPrefix('inventory.')->merge($byPrefix('transaction.')),
            'perencana' => $approvalDisposisi->merge($byPrefix('planning.')),
            'perencanaan' => $approvalDisposisi,
            'admin_perencanaan' => $approvalDisposisi->merge($byPrefix('planning.')),

            'pengadaan' => $approvalDisposisi,
            // Catatan: pemisahan APBD vs BLUD masih sebatas role; pembatasan data akan mengikuti SOP/fitur.
            'admin_pengadaan_apbd' => $approvalDisposisi->merge($byPrefix('procurement.')),
            'admin_pengadaan_blud' => $approvalDisposisi->merge($byPrefix('procurement.')),

            // PPHP umumnya view + verifikasi dokumen; berikan baseline view-only dulu.
            'pphp' => $approvalViewOnly->merge($byPrefix('procurement.')),
            'admin_pphp' => $approvalViewOnly->merge($byPrefix('procurement.')),

            'keuangan' => $approvalDisposisi,
            'admin_keuangan' => $approvalDisposisi->merge($byPrefix('finance.')),

            // Pengurus Barang: disposisi + oversight pemeliharaan + SR + jadwal + kalibrasi
            'pengurus_barang' => $explicit([
                'transaction.approval.index',
                'transaction.approval.show',
                'transaction.approval.disposisi',
                'transaction.approval.disposisi-pemeliharaan',
                'transaction.approval.mengetahui',
                'maintenance.permintaan-pemeliharaan.index',
                'maintenance.permintaan-pemeliharaan.show',
                'maintenance.daftar-permintaan-pemeliharaan.index',
                'maintenance.service-report.index',
                'maintenance.service-report.show',
                'maintenance.service-report.create',
                'maintenance.service-report.store',
                'maintenance.service-report.edit',
                'maintenance.service-report.update',
                'maintenance.jadwal-maintenance.index',
                'maintenance.jadwal-maintenance.show',
                'maintenance.jadwal-maintenance.create',
                'maintenance.jadwal-maintenance.store',
                'maintenance.jadwal-maintenance.edit',
                'maintenance.jadwal-maintenance.update',
                'maintenance.jadwal-maintenance.destroy',
                'maintenance.jadwal-maintenance.generate-permintaan',
                'maintenance.kalibrasi-aset.index',
                'maintenance.kalibrasi-aset.show',
                'maintenance.kalibrasi-aset.create',
                'maintenance.kalibrasi-aset.store',
                'maintenance.kalibrasi-aset.edit',
                'maintenance.kalibrasi-aset.update',
                'maintenance.kalibrasi-aset.destroy',
            ])->merge($byPrefix('transaction.draft-distribusi.'))
                ->merge($byPrefix('transaction.compile-distribusi.'))
                ->merge($byPrefix('transaction.distribusi.')),

            // Teknisi ATEM / IT: tindak lanjut pemeliharaan (bukan pengajuan unit)
            'teknisi_atem' => $explicit([
                'user.dashboard',
                'maintenance.daftar-permintaan-pemeliharaan.index',
                'maintenance.permintaan-pemeliharaan.show',
                'maintenance.permintaan-pemeliharaan.lanjut-perbaikan',
                'maintenance.service-report.index',
                'maintenance.service-report.show',
                'maintenance.service-report.create',
                'maintenance.service-report.store',
                'maintenance.service-report.edit',
                'maintenance.service-report.update',
                'maintenance.jadwal-maintenance.index',
                'maintenance.jadwal-maintenance.show',
                'maintenance.jadwal-maintenance.create',
                'maintenance.jadwal-maintenance.store',
                'maintenance.jadwal-maintenance.edit',
                'maintenance.jadwal-maintenance.update',
                'maintenance.jadwal-maintenance.generate-permintaan',
                'maintenance.kalibrasi-aset.index',
                'maintenance.kalibrasi-aset.show',
                'maintenance.kalibrasi-aset.create',
                'maintenance.kalibrasi-aset.store',
                'maintenance.kalibrasi-aset.edit',
                'maintenance.kalibrasi-aset.update',
                'asset.register-aset.index',
                'asset.register-aset.show',
            ]),
            'teknisi_it' => $explicit([
                'user.dashboard',
                'maintenance.daftar-permintaan-pemeliharaan.index',
                'maintenance.permintaan-pemeliharaan.show',
                'maintenance.permintaan-pemeliharaan.lanjut-perbaikan',
                'maintenance.service-report.index',
                'maintenance.service-report.show',
                'maintenance.service-report.create',
                'maintenance.service-report.store',
                'maintenance.service-report.edit',
                'maintenance.service-report.update',
                'maintenance.jadwal-maintenance.index',
                'maintenance.jadwal-maintenance.show',
                'maintenance.jadwal-maintenance.create',
                'maintenance.jadwal-maintenance.store',
                'maintenance.jadwal-maintenance.edit',
                'maintenance.jadwal-maintenance.update',
                'maintenance.jadwal-maintenance.generate-permintaan',
                'maintenance.kalibrasi-aset.index',
                'maintenance.kalibrasi-aset.show',
                'maintenance.kalibrasi-aset.create',
                'maintenance.kalibrasi-aset.store',
                'maintenance.kalibrasi-aset.edit',
                'maintenance.kalibrasi-aset.update',
                'asset.register-aset.index',
                'asset.register-aset.show',
            ]),

            // PPTK baseline: monitoring planning/procurement + laporan
            'pptk_apbd' => $byPrefix('planning.')->merge($byPrefix('procurement.'))->merge($byPrefix('reports.')),
            'pptk_blud' => $byPrefix('planning.')->merge($byPrefix('procurement.'))->merge($byPrefix('reports.')),
            'admin_pptk_apbd' => $byPrefix('planning.')->merge($byPrefix('procurement.'))->merge($byPrefix('reports.')),
            'admin_pptk_blud' => $byPrefix('planning.')->merge($byPrefix('procurement.'))->merge($byPrefix('reports.')),
        ];

        $allPermissionIds = $allPermissions->map(fn ($id) => (int) $id)->values()->all();
        foreach (['super_administrator'] as $bypassRoleName) {
            $roleId = $roles[$bypassRoleName] ?? null;
            if (! $roleId) {
                continue;
            }
            $role = Role::query()->find($roleId);
            if (! $role) {
                continue;
            }
            $role->permissions()->syncWithoutDetaching($allPermissionIds);
        }

        $touched = 0;
        foreach ($baseline as $roleName => $permissionIds) {
            $roleId = $roles[$roleName] ?? null;
            if (! $roleId) {
                continue;
            }
            $role = Role::query()->find($roleId);
            if (! $role) {
                continue;
            }

            $ids = $permissionIds->filter(fn ($id) => (int) $id > 0)->unique()->values()->all();
            if ($ids === []) {
                continue;
            }

            // Baseline = minimum; jangan hapus assignment manual.
            $role->permissions()->syncWithoutDetaching($ids);
            $touched++;
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->syncWorkflowForBypassRoles($roles);
        $this->command?->info("✓ Baseline permission diterapkan untuk {$touched} role (tanpa menghapus permission lain).");
    }

    /**
     * @param  Collection<string, int>  $roles
     */
    private function syncWorkflowForBypassRoles(Collection $roles): void
    {
        $statuses = WorkflowStatus::query()->get(['id']);
        if ($statuses->isEmpty()) {
            return;
        }

        foreach (['super_administrator'] as $roleName) {
            $roleId = $roles[$roleName] ?? null;
            if (! $roleId) {
                continue;
            }
            foreach ($statuses as $status) {
                WorkflowPermission::query()->updateOrCreate(
                    [
                        'role_id' => (int) $roleId,
                        'workflow_status_id' => (int) $status->id,
                    ],
                    [
                        'can_create' => true,
                        'can_approve' => true,
                        'can_reject' => true,
                        'can_verify' => true,
                        'can_process' => true,
                        'can_finish' => true,
                    ]
                );
            }
        }
    }
}

