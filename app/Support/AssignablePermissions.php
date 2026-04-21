<?php

namespace App\Support;

use App\Helpers\PermissionHelper;
use App\Models\Module;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Menentukan permission & modul menu yang boleh diberikan ke role/user lain
 * oleh pengguna yang sedang login (delegasi), selaras dengan PermissionHelper::canAccess.
 */
final class AssignablePermissions
{
    /**
     * Admin sistem dianggap dapat mengatur semua permission yang terdaftar di database.
     */
    public static function editorMayAssignAll(User $editor): bool
    {
        return $editor->hasRole('admin');
    }

    /**
     * ID permission yang boleh di-assign (untuk checkbox form role).
     *
     * @return array<int, int>
     */
    public static function assignablePermissionIds(User $editor): array
    {
        if (self::editorMayAssignAll($editor)) {
            return Permission::query()->orderBy('id')->pluck('id')->all();
        }

        $ids = [];
        foreach (Permission::query()->orderBy('id')->cursor() as $permission) {
            if (PermissionHelper::canAccess($editor, $permission->name)) {
                $ids[] = (int) $permission->id;
            }
        }

        return $ids;
    }

    /**
     * Nama modul (kolom permissions.module) yang masih punya minimal satu permission yang boleh di-assign.
     * Dipakai untuk filter checklist modul sidebar di Manajemen User.
     *
     * @return array<int, string>
     */
    public static function assignablePermissionModuleKeys(User $editor): array
    {
        $ids = self::assignablePermissionIds($editor);
        if ($ids === []) {
            return [];
        }

        return Permission::query()
            ->whereIn('id', $ids)
            ->distinct()
            ->pluck('module')
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Modul menu (tabel modules) yang boleh dicentang untuk user lain — hanya nama yang ada di tabel modules.
     *
     * @return array<int, string>
     */
    public static function assignableModuleNamesForUserForm(User $editor): array
    {
        $keys = self::assignablePermissionModuleKeys($editor);

        return Module::query()
            ->whereIn('name', $keys)
            ->orderBy('sort_order')
            ->pluck('name')
            ->all();
    }

    /**
     * Permission dikelompokkan per module, hanya yang boleh di-assign.
     */
    public static function assignablePermissionsGroupedByModule(User $editor): Collection
    {
        $ids = self::assignablePermissionIds($editor);
        if ($ids === []) {
            return collect();
        }

        return Permission::query()
            ->whereIn('id', $ids)
            ->orderBy('module')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('module');
    }

    /**
     * Permission pada role yang tidak boleh diubah oleh editor (tampil read-only).
     *
     * @return Collection<int, Permission>
     */
    public static function lockedPermissionsOnRole(User $editor, Collection $rolePermissions): Collection
    {
        if (self::editorMayAssignAll($editor)) {
            return collect();
        }

        $assignable = array_flip(self::assignablePermissionIds($editor));

        return $rolePermissions->filter(fn (Permission $p) => ! isset($assignable[(int) $p->id]))->values();
    }
}
