<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class PermissionHelper
{
    /** TTL cache struktur menu sidebar (jam); invalidasi lewat forget/bump generation */
    private const ACCESSIBLE_MENUS_TTL_HOURS = 24;

    private const CACHE_KEY_MENU_GENERATION = 'sidebar_accessible_menus_generation';

    /**
     * Naikkan versi cache menu global — dipakai saat permission suatu role berubah
     * sehingga semua user membangun ulang menu di request berikutnya.
     */
    public static function bumpAccessibleMenusCacheGeneration(): void
    {
        $k = self::CACHE_KEY_MENU_GENERATION;
        if (! Cache::has($k)) {
            Cache::put($k, 1);
        }
        Cache::increment($k);
    }

    /**
     * Hapus cache menu untuk satu user — dipakai saat role/modul user diubah
     * (tanpa mengubah permission role secara global).
     */
    public static function forgetAccessibleMenusCacheForUser(int $userId): void
    {
        Cache::forget(self::accessibleMenusCacheKey($userId));
    }

    private static function accessibleMenusCacheKey(int $userId): string
    {
        $generation = (int) Cache::get(self::CACHE_KEY_MENU_GENERATION, 1);

        return 'sidebar_accessible_menus:v'.$generation.':u'.$userId;
    }

    /**
     * Mapping role ke permission/modul yang bisa diakses
     */
    public static function getRolePermissions(): array
    {
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
                // Tidak termasuk create, store, edit, update, destroy, delete - harus di-checklist secara eksplisit
                // Akses untuk data inventory dan stock
                'inventory.data-stock.index', // Bisa melihat data stock
                'inventory.data-stock.show', // Bisa melihat detail stock
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
                // Tidak termasuk create, store, edit, update, destroy, delete - harus di-checklist secara eksplisit
            ],
            
            // 6. ADMIN GUDANG / PENGURUS BARANG
            'admin_gudang' => [
                'inventory.data-stock.index',
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
            'admin_gudang_aset' => [
                'inventory.data-stock.index', 'inventory.data-stock.show', 'inventory.data-stock.create', 'inventory.data-stock.store', 'inventory.data-stock.edit', 'inventory.data-stock.update',
                'inventory.data-inventory.index', 'inventory.data-inventory.show', 'inventory.data-inventory.create', 'inventory.data-inventory.store', 'inventory.data-inventory.edit', 'inventory.data-inventory.update',
                'transaction.distribusi.index', 'transaction.distribusi.show', 'transaction.penerimaan-barang.index', 'transaction.penerimaan-barang.show', 'transaction.penerimaan-barang.create', 'transaction.penerimaan-barang.store', 'transaction.penerimaan-barang.edit', 'transaction.penerimaan-barang.update',
                'transaction.approval.index', 'transaction.approval.show', 'transaction.approval.disposisi',
                'asset.register-aset.index', 'asset.register-aset.show', 'asset.register-aset.create', 'asset.register-aset.store', 'asset.register-aset.edit', 'asset.register-aset.update',
                'reports.stock-gudang', 'master.gudang.index', 'master.gudang.show',
                'master-data.data-barang.index', 'master-data.data-barang.show', 'master-data.data-barang.create', 'master-data.data-barang.store', 'master-data.data-barang.edit', 'master-data.data-barang.update',
            ],
            'admin_gudang_persediaan' => [
                'inventory.data-stock.index', 'inventory.data-stock.show', 'inventory.data-stock.create', 'inventory.data-stock.store', 'inventory.data-stock.edit', 'inventory.data-stock.update',
                'inventory.data-inventory.index', 'inventory.data-inventory.show', 'inventory.data-inventory.create', 'inventory.data-inventory.store', 'inventory.data-inventory.edit', 'inventory.data-inventory.update',
                'transaction.distribusi.index', 'transaction.distribusi.show', 'transaction.penerimaan-barang.index', 'transaction.penerimaan-barang.show', 'transaction.penerimaan-barang.create', 'transaction.penerimaan-barang.store', 'transaction.penerimaan-barang.edit', 'transaction.penerimaan-barang.update',
                'transaction.approval.index', 'transaction.approval.show', 'transaction.approval.disposisi',
                'asset.register-aset.index', 'asset.register-aset.show', 'asset.register-aset.create', 'asset.register-aset.store', 'asset.register-aset.edit', 'asset.register-aset.update',
                'reports.stock-gudang', 'master.gudang.index', 'master.gudang.show',
                'master-data.data-barang.index', 'master-data.data-barang.show', 'master-data.data-barang.create', 'master-data.data-barang.store', 'master-data.data-barang.edit', 'master-data.data-barang.update',
            ],
            'admin_gudang_farmasi' => [
                'inventory.data-stock.index', 'inventory.data-stock.show', 'inventory.data-stock.create', 'inventory.data-stock.store', 'inventory.data-stock.edit', 'inventory.data-stock.update',
                'inventory.data-inventory.index', 'inventory.data-inventory.show', 'inventory.data-inventory.create', 'inventory.data-inventory.store', 'inventory.data-inventory.edit', 'inventory.data-inventory.update',
                'transaction.distribusi.index', 'transaction.distribusi.show', 'transaction.penerimaan-barang.index', 'transaction.penerimaan-barang.show', 'transaction.penerimaan-barang.create', 'transaction.penerimaan-barang.store', 'transaction.penerimaan-barang.edit', 'transaction.penerimaan-barang.update',
                'transaction.approval.index', 'transaction.approval.show', 'transaction.approval.disposisi',
                'asset.register-aset.index', 'asset.register-aset.show', 'asset.register-aset.create', 'asset.register-aset.store', 'asset.register-aset.edit', 'asset.register-aset.update',
                'reports.stock-gudang', 'master.gudang.index', 'master.gudang.show',
                'master-data.data-barang.index', 'master-data.data-barang.show', 'master-data.data-barang.create', 'master-data.data-barang.store', 'master-data.data-barang.edit', 'master-data.data-barang.update',
            ],
            // 6c. ADMIN GUDANG UNIT (hanya akses gudang unit kerjanya, tidak bisa gudang pusat)
            'admin_gudang_unit' => [
                'inventory.data-stock.index', 'inventory.data-stock.show',
                'inventory.data-inventory.index', 'inventory.data-inventory.show',
                'transaction.penerimaan-barang.index', 'transaction.penerimaan-barang.show', 'transaction.penerimaan-barang.create', 'transaction.penerimaan-barang.store', 'transaction.penerimaan-barang.edit', 'transaction.penerimaan-barang.update',
                'transaction.retur-barang.index', 'transaction.retur-barang.show', 'transaction.retur-barang.create', 'transaction.retur-barang.store',
                'transaction.pengembalian-barang.index',
                'transaction.peminjaman-barang.index', 'transaction.peminjaman-barang.show', 'transaction.peminjaman-barang.serah-terima',
                'asset.register-aset.index', 'asset.register-aset.show', 'asset.register-aset.edit', 'asset.register-aset.update',
                'reports.stock-gudang', 'master.gudang.index', 'master.gudang.show',
            ],
            
            // 7. UNIT TERKAIT
            'perencanaan' => [
                'transaction.approval.index',
                'transaction.approval.show',
                'transaction.approval.disposisi',
            ],
            'pengadaan' => [
                'transaction.approval.index',
                'transaction.approval.show',
                'transaction.approval.disposisi',
            ],
            'keuangan' => [
                'transaction.approval.index',
                'transaction.approval.show',
                'transaction.approval.disposisi',
            ],
        ];
    }

    /**
     * Check if user can access a route
     * Priority: Database permissions > Static permissions
     * Mendukung wildcard permission dan mapping sederhana
     * Mapping: create -> store, edit -> update
     */
    public static function canAccess(User $user, string $permission): bool
    {
        // Admin selalu bisa akses semua
        if ($user->hasRole('admin')) {
            return true;
        }

        // First, check database permissions (dynamic)
        if ($user->hasPermission($permission)) {
            return true;
        }

        // Mapping permission yang disederhanakan
        // store -> create, update -> edit, destroy -> delete
        $parts = explode('.', $permission);
        $action = count($parts) > 1 ? end($parts) : null;
        $resourceName = count($parts) > 1 ? implode('.', array_slice($parts, 0, -1)) : null;
        
        if (count($parts) > 1) {
            
            // Jika permission adalah 'store', cek apakah user punya 'create'
            if ($action === 'store') {
                $createPermission = $resourceName . '.create';
                if ($user->hasPermission($createPermission)) {
                    return true;
                }
            }
            
            // Jika permission adalah 'update', cek apakah user punya 'edit'
            if ($action === 'update') {
                $editPermission = $resourceName . '.edit';
                if ($user->hasPermission($editPermission)) {
                    return true;
                }
            }
            
            // Jika permission adalah 'destroy', cek apakah user punya 'delete'
            if ($action === 'destroy') {
                $deletePermission = $resourceName . '.delete';
                if ($user->hasPermission($deletePermission)) {
                    return true;
                }
            }
            
            // Jika permission adalah 'delete', cek apakah user punya 'destroy'
            if ($action === 'delete') {
                $destroyPermission = $resourceName . '.destroy';
                if ($user->hasPermission($destroyPermission)) {
                    return true;
                }
            }
        }

        // Check wildcard permission di database
        // Misalnya: jika user punya 'master-data.aset.*', maka bisa akses 'master-data.aset.create', 'master-data.aset.store', dll
        // KECUALI untuk action sensitif seperti destroy dan delete - harus permission spesifik
        $sensitiveActions = ['destroy', 'delete'];
        $isSensitiveAction = $action && in_array($action, $sensitiveActions);
        
        if (count($parts) > 1 && !$isSensitiveAction) {
            // Coba dengan wildcard untuk resource level
            $resourceWildcard = implode('.', array_slice($parts, 0, -1)) . '.*';
            if ($user->hasPermission($resourceWildcard)) {
                return true;
            }
            
            // Coba dengan wildcard untuk module level
            $moduleWildcard = $parts[0] . '.*';
            if ($user->hasPermission($moduleWildcard)) {
                return true;
            }
        }

        // Fallback to static permissions (for backward compatibility)
        // KECUALI untuk action sensitif seperti destroy dan delete - tidak boleh menggunakan wildcard
        $rolePermissions = self::getRolePermissions();
        $userRoles = $user->roles->pluck('name')->toArray();

        foreach ($userRoles as $role) {
            if (!isset($rolePermissions[$role])) {
                continue;
            }

            $permissions = $rolePermissions[$role];
            
            foreach ($permissions as $allowedPermission) {
                // Exact match
                if ($allowedPermission === $permission) {
                    return true;
                }
                
                // Wildcard match (e.g., 'inventory.*' matches 'inventory.data-stock.index')
                // TAPI tidak untuk action sensitif seperti destroy dan delete
                if (str_ends_with($allowedPermission, '.*') && !$isSensitiveAction) {
                    $prefix = str_replace('.*', '', $allowedPermission);
                    if (str_starts_with($permission, $prefix . '.')) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Get accessible menu items for user based on permissions (not roles)
     * Menu akan muncul jika user memiliki permission yang sesuai
     * Menu juga difilter berdasarkan modules yang di-assign ke user
     *
     * Hasil di-cache per user (key memuat generasi global) agar sidebar tidak
     * membangun ulang pohon menu + pengecekan permission pada setiap request view.
     */
    public static function getAccessibleMenus(User $user): array
    {
        $user->loadMissing('roles', 'modules');

        return Cache::remember(
            self::accessibleMenusCacheKey($user->id),
            now()->addHours(self::ACCESSIBLE_MENUS_TTL_HOURS),
            static function () use ($user) {
                return self::computeAccessibleMenus($user);
            }
        );
    }

    /**
     * Bangun struktur menu yang boleh diakses (tanpa cache — dipanggil dari dalam Cache::remember).
     */
    private static function computeAccessibleMenus(User $user): array
    {
        if (! $user->relationLoaded('modules')) {
            $user->load('modules');
        }

        $userModules = $user->modules->pluck('name')->toArray();
        
        // Mapping route ke permission name
        $menus = [
            'dashboard' => [
                'route' => 'user.dashboard', 
                'permission' => 'user.dashboard' // Default permission untuk dashboard
            ],
            'master-manajemen' => [
                'route' => null,
                'permission' => 'master-manajemen.*', // Menu muncul jika punya permission master-manajemen.*
                'submenus' => [
                    'master-pegawai' => ['route' => 'master-manajemen.master-pegawai.index', 'permission' => 'master-manajemen.master-pegawai.index'],
                    'master-jabatan' => ['route' => 'master-manajemen.master-jabatan.index', 'permission' => 'master-manajemen.master-jabatan.index'],
                    'unit-kerja' => ['route' => 'master.unit-kerja.index', 'permission' => 'master.unit-kerja.index'],
                    'gudang' => ['route' => 'master.gudang.index', 'permission' => 'master.gudang.index'],
                    'ruangan' => ['route' => 'master.ruangan.index', 'permission' => 'master.ruangan.index'],
                    'program' => ['route' => 'master.program.index', 'permission' => 'master.program.index'],
                    'kegiatan' => ['route' => 'master.kegiatan.index', 'permission' => 'master.kegiatan.index'],
                    'sub-kegiatan' => ['route' => 'master.sub-kegiatan.index', 'permission' => 'master.sub-kegiatan.index'],
                ],
            ],
            'master-data' => [
                'route' => null,
                'permission' => 'master-data.*',
                'submenus' => [
                    'aset' => ['route' => 'master-data.aset.index', 'permission' => 'master-data.aset.index'],
                    'kode-barang' => ['route' => 'master-data.kode-barang.index', 'permission' => 'master-data.kode-barang.index'],
                    'kategori-barang' => ['route' => 'master-data.kategori-barang.index', 'permission' => 'master-data.kategori-barang.index'],
                    'jenis-barang' => ['route' => 'master-data.jenis-barang.index', 'permission' => 'master-data.jenis-barang.index'],
                    'subjenis-barang' => ['route' => 'master-data.subjenis-barang.index', 'permission' => 'master-data.subjenis-barang.index'],
                    'data-barang' => ['route' => 'master-data.data-barang.index', 'permission' => 'master-data.data-barang.index'],
                    'satuan' => ['route' => 'master-data.satuan.index', 'permission' => 'master-data.satuan.index'],
                    'sumber-anggaran' => ['route' => 'master-data.sumber-anggaran.index', 'permission' => 'master-data.sumber-anggaran.index'],
                ],
            ],
            'inventory' => [
                'route' => null,
                'permission' => 'inventory.*', // Menu muncul jika punya permission inventory.* atau salah satu submenu
                'submenus' => [
                    'data-stock' => ['route' => 'inventory.data-stock.index', 'permission' => 'inventory.data-stock.index'],
                    'data-inventory' => ['route' => 'inventory.data-inventory.index', 'permission' => 'inventory.data-inventory.index'],
                    'stock-adjustment' => ['route' => 'inventory.stock-adjustment.index', 'permission' => 'inventory.stock-adjustment.index'],
                ],
            ],
            'permintaan' => [
                'route' => null,
                'permission' => null,
                'submenus' => [
                    'permintaan-barang' => ['route' => 'transaction.permintaan-barang.index', 'permission' => 'transaction.permintaan-barang.index'],
                    'peminjaman-barang' => ['route' => 'transaction.peminjaman-barang.index', 'permission' => 'transaction.peminjaman-barang.index'],
                    'permintaan-pemeliharaan' => ['route' => 'maintenance.permintaan-pemeliharaan.index', 'permission' => 'maintenance.permintaan-pemeliharaan.index'],
                    'permintaan-pengadaan-barang' => ['route' => 'planning.rku.index', 'permission' => 'planning.rku.index'],
                ],
            ],
            'approval' => [
                'route' => null,
                'permission' => null,
                'submenus' => [
                    'approval-permintaan-barang' => ['route' => 'transaction.approval.index', 'permission' => 'transaction.approval.index'],
                    // Note: Route untuk approval pemeliharaan dan pengadaan belum dibuat, menggunakan route sementara
                    'approval-permintaan-pemeliharaan' => ['route' => 'maintenance.permintaan-pemeliharaan.index', 'permission' => 'maintenance.permintaan-pemeliharaan.index'],
                    'approval-permintaan-pengadaan-barang' => ['route' => 'planning.rku.index', 'permission' => 'planning.rku.index'],
                ],
            ],
            'pengurus-barang' => [
                'route' => null,
                'permission' => null,
                'submenus' => [
                    'proses-disposisi' => ['route' => 'transaction.draft-distribusi.index', 'permission' => 'transaction.draft-distribusi.index'],
                    'compile-sbbk' => ['route' => 'transaction.compile-distribusi.index', 'permission' => 'transaction.compile-distribusi.index'],
                    'distribusi' => ['route' => 'transaction.distribusi.index', 'permission' => 'transaction.distribusi.index'],
                    'penerimaan-barang' => ['route' => 'transaction.penerimaan-barang.index', 'permission' => 'transaction.penerimaan-barang.index'],
                    'retur-barang' => ['route' => 'transaction.retur-barang.index', 'permission' => 'transaction.retur-barang.index'],
                    'pengembalian-barang' => ['route' => 'transaction.pengembalian-barang.index', 'permission' => 'transaction.pengembalian-barang.index'],
                ],
            ],
            'aset-kir' => [
                'route' => null,
                'permission' => 'asset.register-aset.index',
                'submenus' => [
                    'register-aset' => ['route' => 'asset.register-aset.index', 'permission' => 'asset.register-aset.index'],
                    'kartu-inventaris-ruangan' => ['route' => 'asset.kartu-inventaris-ruangan.index', 'permission' => 'asset.kartu-inventaris-ruangan.index'],
                    'mutasi-aset' => ['route' => 'asset.mutasi-aset.index', 'permission' => 'asset.mutasi-aset.index'],
                ],
            ],
            'planning' => [
                'route' => null,
                'permission' => 'planning.*',
                'submenus' => [
                    'rku' => ['route' => 'planning.rku.index', 'permission' => 'planning.rku.index'],
                    'rekap-tahunan' => ['route' => 'planning.rekap-tahunan', 'permission' => 'planning.rekap-tahunan'],
                ],
            ],
            'procurement' => [
                'route' => null,
                'permission' => 'procurement.*',
                'submenus' => [
                    'proses-pengadaan' => ['route' => 'procurement.proses-pengadaan.index', 'permission' => 'procurement.proses-pengadaan.index'],
                    'paket-pengadaan' => ['route' => 'procurement.paket-pengadaan.index', 'permission' => 'procurement.paket-pengadaan.index'],
                ],
            ],
            'finance' => [
                'route' => null,
                'permission' => 'finance.*',
                'submenus' => [
                    'pembayaran' => ['route' => 'finance.pembayaran.index', 'permission' => 'finance.pembayaran.index'],
                ],
            ],
            'maintenance' => [
                'route' => null,
                'permission' => 'maintenance.*',
                'submenus' => [
                    'jadwal-maintenance' => ['route' => 'maintenance.jadwal-maintenance.index', 'permission' => 'maintenance.jadwal-maintenance.index'],
                    'kalibrasi-aset' => ['route' => 'maintenance.kalibrasi-aset.index', 'permission' => 'maintenance.kalibrasi-aset.index'],
                    'service-report' => ['route' => 'maintenance.service-report.index', 'permission' => 'maintenance.service-report.index'],
                ],
            ],
            'laporan' => [
                'route' => 'reports.index', 
                'permission' => 'reports.index'
            ],
            'admin' => [
                'route' => null,
                'permission' => 'admin.*',
                'submenus' => [
                    'roles' => ['route' => 'admin.roles.index', 'permission' => 'admin.roles.index'],
                    'users' => ['route' => 'admin.users.index', 'permission' => 'admin.users.index'],
                ],
            ],
        ];

        // Filter menus berdasarkan permission user (bukan role) dan modules user
        $accessibleMenus = [];

        foreach ($menus as $key => $menu) {
            $requiredPermission = $menu['permission'] ?? null;
            
            // Cek apakah menu sesuai dengan modules user
            // Mapping menu key ke module name
            $menuModuleMap = [
                'master-manajemen' => 'master-manajemen',
                'master-data' => 'master-data',
                'inventory' => 'inventory',
                'permintaan' => null,
                'approval' => null,
                'pengurus-barang' => null,
                'aset-kir' => 'asset',
                'planning' => 'planning',
                'procurement' => 'procurement',
                'finance' => 'finance',
                'maintenance' => 'maintenance',
                'laporan' => 'reports',
            ];
            
            $menuModule = $menuModuleMap[$key] ?? null;
            
            // Jika menu punya module mapping dan user tidak punya module tersebut, skip
            if ($menuModule && !empty($userModules) && !in_array($menuModule, $userModules)) {
                continue; // Skip menu jika user tidak punya module
            }
            
            // Untuk menu dengan submenu, cek apakah ada submenu yang accessible
            if (isset($menu['submenus'])) {
                $accessibleSubmenus = [];
                foreach ($menu['submenus'] as $subKey => $submenu) {
                    $subPermission = $submenu['permission'] ?? null;
                    if ($subPermission && self::canAccess($user, $subPermission)) {
                        $accessibleSubmenus[$subKey] = $submenu;
                    }
                }
                
                // Menu parent muncul jika ada minimal 1 submenu yang accessible
                // Atau jika user punya permission untuk menu parent (wildcard)
                if (!empty($accessibleSubmenus) || ($requiredPermission && self::canAccess($user, $requiredPermission))) {
                    $accessibleMenu = $menu;
                    $accessibleMenu['submenus'] = $accessibleSubmenus;
                    $accessibleMenus[$key] = $accessibleMenu;
                }
            } else {
                // Menu tanpa submenu: cek permission langsung
                // Dashboard selalu accessible untuk semua user yang login
                if ($key === 'dashboard' || ($requiredPermission && self::canAccess($user, $requiredPermission))) {
                    $accessibleMenus[$key] = $menu;
                }
            }
        }

        return $accessibleMenus;
    }
}

