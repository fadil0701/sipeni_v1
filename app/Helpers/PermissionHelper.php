<?php

namespace App\Helpers;

use App\Support\Rbac\StaticRolePermissionMap;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\WildcardPermission;

class PermissionHelper
{
    /** TTL cache struktur menu sidebar + nama permission (jam); invalidasi lewat forget/bump generation */
    private const ACCESSIBLE_MENUS_TTL_HOURS = 24;

    private const CACHE_KEY_MENU_GENERATION = 'sidebar_accessible_menus_generation';

    /** @var array<string, bool> */
    private static array $canAccessMemo = [];

    /** @var array<int, list<string>> */
    private static array $permissionNamesMemo = [];

    /** @var array<int, array<string, mixed>> */
    private static array $wildcardIndexMemo = [];

    public static function hasEnterpriseBypassRole(mixed $user): bool
    {
        if (! $user) {
            return false;
        }

        $bypassRoles = config('sipeni.rbac.bypass_roles', ['super_administrator']);

        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole($bypassRoles)) {
            return true;
        }

        return (bool) ($user->is_superadmin ?? false);
    }

    /**
     * Naikkan versi cache menu + permission names global — dipakai saat permission suatu role berubah
     * sehingga semua user membangun ulang menu/permission di request berikutnya.
     */
    public static function bumpAccessibleMenusCacheGeneration(): void
    {
        $cache = self::cacheStore();
        $k = self::CACHE_KEY_MENU_GENERATION;
        if (! $cache->has($k)) {
            $cache->put($k, 1);
        }
        $cache->increment($k);
        self::$permissionNamesMemo = [];
        self::$wildcardIndexMemo = [];
        self::$canAccessMemo = [];
    }

    /**
     * Hapus cache menu + permission names untuk satu user — dipakai saat role/modul user diubah
     * (tanpa mengubah permission role secara global).
     */
    public static function forgetAccessibleMenusCacheForUser(int $userId): void
    {
        $cache = self::cacheStore();
        $cache->forget(self::accessibleMenusCacheKey($userId));
        $cache->forget(self::permissionNamesCacheKey($userId));
        unset(self::$permissionNamesMemo[$userId], self::$wildcardIndexMemo[$userId]);
        foreach (array_keys(self::$canAccessMemo) as $memoKey) {
            if (str_starts_with($memoKey, $userId.':')) {
                unset(self::$canAccessMemo[$memoKey]);
            }
        }
    }

    /**
     * Warm cache nama permission (string) tanpa hydrate model Permission.
     * Dipanggil dari middleware agar request berikutnya / sidebar tidak memuat ratusan Eloquent.
     */
    public static function warmPermissionCache(mixed $user): void
    {
        if (! $user || self::hasEnterpriseBypassRole($user)) {
            return;
        }

        self::permissionNamesForUser($user);
    }

    /**
     * @return list<string>
     */
    public static function permissionNamesForUser(mixed $user): array
    {
        $userId = isset($user->id) ? (int) $user->id : 0;
        if ($userId <= 0) {
            return [];
        }

        if (isset(self::$permissionNamesMemo[$userId])) {
            return self::$permissionNamesMemo[$userId];
        }

        /** @var list<string> $names */
        $names = self::cacheStore()->remember(
            self::permissionNamesCacheKey($userId),
            date('Y-m-d H:i:s', strtotime('+'.self::ACCESSIBLE_MENUS_TTL_HOURS.' hours')),
            static function () use ($user): array {
                return self::loadPermissionNamesFromDatabase($user);
            }
        );

        return self::$permissionNamesMemo[$userId] = $names;
    }

    /**
     * Cek apakah user punya permission (exact / wildcard Spatie) via cache nama — tanpa Eloquent Permission.
     */
    public static function ownsPermission(mixed $user, string $permission): bool
    {
        if (! $user) {
            return false;
        }

        if (self::hasEnterpriseBypassRole($user)) {
            return true;
        }

        $names = self::permissionNamesForUser($user);
        if ($names === []) {
            return false;
        }

        if (in_array($permission, $names, true)) {
            return true;
        }

        $userId = (int) $user->id;
        $index = self::$wildcardIndexMemo[$userId] ??= self::buildWildcardIndex($names);
        $guard = 'web';

        return (new WildcardPermission($user))->implies($permission, $guard, $index);
    }

    private static function accessibleMenusCacheKey(int $userId): string
    {
        $generation = (int) self::cacheStore()->get(self::CACHE_KEY_MENU_GENERATION, 1);

        return 'sidebar_accessible_menus:v'.$generation.':u'.$userId;
    }

    private static function permissionNamesCacheKey(int $userId): string
    {
        $generation = (int) self::cacheStore()->get(self::CACHE_KEY_MENU_GENERATION, 1);

        return 'user_permission_names:v'.$generation.':u'.$userId;
    }

    /**
     * @return list<string>
     */
    private static function loadPermissionNamesFromDatabase(mixed $user): array
    {
        if (! method_exists($user, 'relationLoaded')) {
            return [];
        }

        if (! $user->relationLoaded('roles')) {
            $user->load('roles:id,name,guard_name');
        }

        $roleIds = $user->roles->pluck('id')->map(fn ($id) => (int) $id)->filter()->values()->all();
        $names = [];

        if ($roleIds !== []) {
            $names = DB::table('permissions')
                ->join('permission_role', 'permissions.id', '=', 'permission_role.permission_id')
                ->whereIn('permission_role.role_id', $roleIds)
                ->distinct()
                ->orderBy('permissions.name')
                ->pluck('permissions.name')
                ->all();
        }

        // Direct permissions (jarang dipakai di SI-MANTIK, tetap didukung)
        $direct = DB::table('permissions')
            ->join('model_has_permissions', 'permissions.id', '=', 'model_has_permissions.permission_id')
            ->where('model_has_permissions.model_id', (int) $user->id)
            ->where('model_has_permissions.model_type', $user::class)
            ->distinct()
            ->pluck('permissions.name')
            ->all();

        if ($direct !== []) {
            $names = array_values(array_unique(array_merge($names, $direct)));
            sort($names);
        }

        return array_map('strval', $names);
    }

    /**
     * Bangun index wildcard Spatie dari daftar nama (tanpa model Permission).
     *
     * @param  list<string>  $names
     * @return array<string, mixed>
     */
    private static function buildWildcardIndex(array $names): array
    {
        $builder = new class extends WildcardPermission
        {
            public function __construct()
            {
                // record tidak dipakai untuk buildIndex
            }

            /**
             * @param  list<string>  $names
             * @return array<string, mixed>
             */
            public function fromNames(array $names, string $guard): array
            {
                $index = [];
                foreach ($names as $name) {
                    $index[$guard] = $this->buildIndex(
                        $index[$guard] ?? [],
                        explode(self::PART_DELIMITER, $name),
                        $name,
                    );
                }

                return $index;
            }
        };

        return $builder->fromNames($names, 'web');
    }

    /**
     * @deprecated Hanya referensi seeder/migrasi. Authorization runtime memakai database.
     *
     * @return array<string, list<string>>
     */
    public static function getRolePermissions(): array
    {
        return StaticRolePermissionMap::all();
    }


    /**
     * Cek akses permission — sumber utama: database (Spatie + wildcard native) via cache nama.
     */
    public static function canAccess(mixed $user, string $permission): bool
    {
        if (! $user) {
            return false;
        }

        $userId = isset($user->id) ? (int) $user->id : 0;
        $memoKey = $userId.':'.$permission;
        if (array_key_exists($memoKey, self::$canAccessMemo)) {
            return self::$canAccessMemo[$memoKey];
        }

        if (self::hasEnterpriseBypassRole($user)) {
            return self::$canAccessMemo[$memoKey] = true;
        }

        foreach (self::derivedPermissionParents($permission) as $parent) {
            if (self::ownsPermission($user, $parent)) {
                return self::$canAccessMemo[$memoKey] = true;
            }
        }

        return self::$canAccessMemo[$memoKey] = self::ownsPermission($user, $permission);
    }

    /**
     * Turunan route yang boleh diakses jika parent permission ada (bukan alias CRUD).
     *
     * @return list<string>
     */
    private static function derivedPermissionParents(string $permission): array
    {
        return match ($permission) {
            'inventory.data-stock.merk-breakdown' => ['inventory.data-stock.index'],
            'reports.kartu-stok.merk-breakdown' => ['reports.kartu-stok'],
            'inventory.farmasi-kedaluwarsa.export' => ['inventory.farmasi-kedaluwarsa.index'],
            default => [],
        };
    }

    /**
     * Get accessible menu items for user based on permissions (not roles)
     * Menu akan muncul jika user memiliki permission yang sesuai
     * user_modules hanya fallback legacy (config sipeni.rbac.legacy_user_modules_fallback)
     *
     * Hasil di-cache per user (key memuat generasi global) agar sidebar tidak
     * membangun ulang pohon menu + pengecekan permission pada setiap request view.
     */
    public static function getAccessibleMenus(mixed $user): array
    {
        $userId = isset($user->id) ? (int) $user->id : 0;

        return self::cacheStore()->remember(
            self::accessibleMenusCacheKey($userId),
            date('Y-m-d H:i:s', strtotime('+'.self::ACCESSIBLE_MENUS_TTL_HOURS.' hours')),
            static function () use ($user) {
                return self::computeAccessibleMenus($user);
            }
        );
    }

    /**
     * Bangun struktur menu yang boleh diakses (tanpa cache — dipanggil dari dalam Cache::remember).
     */
    private static function computeAccessibleMenus(mixed $user): array
    {
        $hasEnterpriseBypass = self::hasEnterpriseBypassRole($user);

        // Mapping route ke permission name
        $menus = [
            'dashboard' => [
                'route' => 'user.dashboard',
                'permission' => 'user.dashboard', // Default permission untuk dashboard
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
                    'farmasi-kedaluwarsa' => ['route' => 'inventory.farmasi-kedaluwarsa.index', 'permission' => 'inventory.farmasi-kedaluwarsa.index'],
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
                    'rku-input' => ['route' => 'planning.rku.create', 'permission' => 'planning.rku.create'],
                    'rku-daftar' => ['route' => 'planning.rku.index', 'permission' => 'planning.rku.index'],
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
                'permission' => 'reports.index',
            ],
            'admin' => [
                'route' => null,
                'permission' => 'admin.*',
                'submenus' => [
                    'roles' => ['route' => 'admin.roles.index', 'permission' => 'admin.roles.index'],
                    'users' => ['route' => 'admin.users.index', 'permission' => 'admin.users.index'],
                    'print-templates' => ['route' => 'admin.print-templates.index', 'permission' => 'admin.print-templates.index'],
                ],
            ],
        ];

        // Filter menus berdasarkan permission user (bukan role) dan modules user
        $accessibleMenus = [];

        foreach ($menus as $key => $menu) {
            $requiredPermission = $menu['permission'] ?? null;

            // Untuk menu dengan submenu, cek apakah ada submenu yang accessible
            if (isset($menu['submenus'])) {
                $accessibleSubmenus = [];
                foreach ($menu['submenus'] as $subKey => $submenu) {
                    if ($subKey === 'print-templates' && ! self::featurePrintTemplatesEnabled()) {
                        continue;
                    }
                    $subPermission = $submenu['permission'] ?? null;
                    if ($hasEnterpriseBypass || ($subPermission && self::canAccess($user, $subPermission))) {
                        $accessibleSubmenus[$subKey] = $submenu;
                    }
                }

                // Menu parent muncul jika ada minimal 1 submenu yang accessible
                // Atau jika user punya permission untuk menu parent (wildcard)
                if (! empty($accessibleSubmenus) || $hasEnterpriseBypass || ($requiredPermission && self::canAccess($user, $requiredPermission))) {
                    $accessibleMenu = $menu;
                    $accessibleMenu['submenus'] = $accessibleSubmenus;
                    $accessibleMenus[$key] = $accessibleMenu;
                }
            } else {
                // Menu tanpa submenu: cek permission langsung
                // Dashboard selalu accessible untuk semua user yang login
                if ($key === 'dashboard' || $hasEnterpriseBypass || ($requiredPermission && self::canAccess($user, $requiredPermission))) {
                    $accessibleMenus[$key] = $menu;
                }
            }
        }

        return $accessibleMenus;
    }

    private static function cacheStore()
    {
        return \call_user_func('\\app', 'cache');
    }

    private static function featurePrintTemplatesEnabled(): bool
    {
        return (bool) \call_user_func('\\config', 'sipeni.feature_print_templates', false);
    }
}
