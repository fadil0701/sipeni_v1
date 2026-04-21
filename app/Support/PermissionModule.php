<?php

namespace App\Support;

/**
 * Label dan urutan modul untuk permission (kolom permissions.module).
 * Dipakai agar Manajemen Role, halaman detail, dan sync route konsisten.
 */
final class PermissionModule
{
    /**
     * Kunci = nilai permissions.module di database (harus sama dengan hasil sync route).
     */
    public const LABELS = [
        'dashboard' => 'Dashboard & portal unit',
        'master-manajemen' => 'Master organisasi (pegawai, jabatan, unit, gudang, program)',
        'master-data' => 'Master barang & klasifikasi',
        'inventory' => 'Inventori, stok, item, penyesuaian',
        'transaction' => 'Transaksi (permintaan, approval, distribusi, penerimaan, retur, pemakaian)',
        'maintenance' => 'Pemeliharaan, kalibrasi, jadwal',
        'asset' => 'Aset tetap, register aset, KIR, mutasi',
        'planning' => 'Perencanaan & RKU',
        'procurement' => 'Pengadaan',
        'finance' => 'Keuangan & pembayaran',
        'reports' => 'Laporan',
        'admin' => 'Admin (user & role)',
        'api' => 'API bantu (dropdown form, dll.)',
    ];

    /** Urutan tampilan grup permission di form role */
    private const ORDER = [
        'dashboard',
        'master-manajemen',
        'master-data',
        'inventory',
        'transaction',
        'maintenance',
        'asset',
        'planning',
        'procurement',
        'finance',
        'reports',
        'admin',
        'api',
    ];

    public static function label(string $module): string
    {
        return self::LABELS[$module] ?? self::humanize($module);
    }

    private static function humanize(string $module): string
    {
        return (string) str($module)->replace(['-', '_'], ' ')->title();
    }

    /**
     * @param  array<int, string>  $keys
     * @return array<int, string>
     */
    public static function sortModuleKeys(array $keys): array
    {
        $order = array_flip(self::ORDER);
        usort($keys, function (string $a, string $b) use ($order): int {
            $ia = $order[$a] ?? 999;
            $ib = $order[$b] ?? 999;

            return $ia === $ib ? strcmp($a, $b) : $ia <=> $ib;
        });

        return $keys;
    }

    /**
     * Normalisasi segmen pertama route name ke kunci permissions.module
     * (selaras dengan permission:sync-routes).
     */
    public static function moduleKeyFromRoutePrefix(string $firstSegment): string
    {
        return match ($firstSegment) {
            'user' => 'dashboard',
            'master-manajemen' => 'master-manajemen',
            'master' => 'master-manajemen',
            'master-data' => 'master-data',
            'inventory' => 'inventory',
            'transaction' => 'transaction',
            'maintenance' => 'maintenance',
            'asset' => 'asset',
            'planning' => 'planning',
            'procurement' => 'procurement',
            'finance' => 'finance',
            'admin' => 'admin',
            'reports' => 'reports',
            'api' => 'api',
            default => $firstSegment,
        };
    }
}
