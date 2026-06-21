<?php

/**
 * Refactor otomatis pola scope role → UserScope / RbacRoles (Tahap 2).
 * Jalankan: php scripts/rbac_phase2_controller_refactor.php
 */

$base = dirname(__DIR__);
$dirs = [$base.'/app/Http/Controllers', $base.'/app/Helpers', $base.'/app/Services'];

$replacements = [
    "\$user->hasAnyRole(['kepala_unit', 'pegawai', 'admin_gudang_unit']) && ! \$user->hasRole('admin')" => 'UserScope::mustScopeToUnitKerja($user)',
    "\$user->hasAnyRole(['kepala_unit', 'pegawai', 'admin_gudang_unit']) && !\$user->hasRole('admin')" => 'UserScope::mustScopeToUnitKerja($user)',
    "\$user->hasAnyRole(['kepala_unit', 'pegawai']) && ! \$user->hasRole('admin')" => 'UserScope::mustScopeToUnitKerja($user)',
    "\$user->hasAnyRole(['kepala_unit', 'pegawai']) && !\$user->hasRole('admin')" => 'UserScope::mustScopeToUnitKerja($user)',
    "\$user->hasAnyRole(['kepala_unit', 'pegawai']) && ! \$user->hasAnyRole(['admin', 'admin_gudang', 'pengurus_barang'])" => 'UserScope::mustScopeToUnitKerja($user)',
    "! \$user->hasRole('admin')" => '! UserScope::canViewCrossUnitData($user)',
    "!$user->hasRole('admin')" => '!UserScope::canViewCrossUnitData($user)',
    "\$user->hasRole('admin')" => 'UserScope::canViewCrossUnitData($user)',
    "\$user->hasAnyRole(['admin', 'admin_gudang'])" => '(UserScope::canViewCrossUnitData($user) || RbacRoles::userHasWarehousePusatAccess($user))',
    "! \$user->hasAnyRole(['admin', 'admin_gudang'])" => '! (UserScope::canViewCrossUnitData($user) || RbacRoles::userHasWarehousePusatAccess($user))',
    "!$user->hasAnyRole(['admin', 'admin_gudang'])" => '!(UserScope::canViewCrossUnitData($user) || RbacRoles::userHasWarehousePusatAccess($user))',
    "\$user->hasAnyRole(['admin', 'kepala_pusat'])" => '(UserScope::canViewCrossUnitData($user) || $user->hasRole(\'kepala_pusat\'))',
    "! \$user->hasAnyRole(['admin', 'kepala_pusat'])" => '! (UserScope::canViewCrossUnitData($user) || $user->hasRole(\'kepala_pusat\'))',
    "\$user->hasAnyRole(['admin', 'kepala_unit'])" => '(UserScope::canViewCrossUnitData($user) || $user->hasRole(\'kepala_unit\'))',
    "! \$user->hasAnyRole(['admin', 'kepala_unit'])" => '! (UserScope::canViewCrossUnitData($user) || $user->hasRole(\'kepala_unit\'))',
    "['kepala_unit', 'pegawai', 'admin_gudang_unit']" => "RbacRoles::UNIT_SCOPED",
    "['kepala_unit', 'pegawai']" => "['admin_unit', 'kepala_unit', 'pegawai']",
];

$useBlock = "use App\\Support\\Rbac\\RbacRoles;\nuse App\\Support\\Rbac\\UserScope;\n";

$touched = 0;
foreach ($dirs as $dir) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($it as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        $path = $file->getPathname();
        $content = file_get_contents($path);
        $original = $content;
        foreach ($replacements as $from => $to) {
            $content = str_replace($from, $to, $content);
        }
        if ($content !== $original) {
            if (str_contains($content, 'UserScope::') && ! str_contains($content, 'use App\\Support\\Rbac\\UserScope')) {
                $content = preg_replace(
                    '/(namespace App\\\\[^;]+;)\n/',
                    "$1\n\n".$useBlock,
                    $content,
                    1
                );
            }
            file_put_contents($path, $content);
            $touched++;
            echo "Updated: {$path}\n";
        }
    }
}

echo "Done. Files touched: {$touched}\n";
