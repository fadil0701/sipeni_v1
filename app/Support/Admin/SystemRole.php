<?php

namespace App\Support\Admin;

use App\Models\Role;
use App\Support\Rbac\RoleCompatibility;

final class SystemRole
{
    /**
     * Role names that cannot be deleted via admin UI.
     *
     * @return list<string>
     */
    public static function protectedNames(): array
    {
        return config('sipeni.rbac.bypass_roles', ['super_administrator']);
    }

    public static function isProtected(Role $role): bool
    {
        if ((bool) ($role->is_protected ?? false)) {
            return true;
        }

        return in_array($role->name, self::protectedNames(), true);
    }

    public static function isSystemRole(Role $role): bool
    {
        if ((bool) ($role->is_system_role ?? false)) {
            return true;
        }

        return in_array($role->name, RoleCompatibility::CANONICAL_ROLES, true);
    }
}
