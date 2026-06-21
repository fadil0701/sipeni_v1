<?php

namespace App\Support\Admin;

use App\Helpers\PermissionHelper;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class SuperAdminGuard
{
    /**
     * @return list<string>
     */
    public static function protectedRoleNames(): array
    {
        return config('sipeni.rbac.bypass_roles', ['super_administrator']);
    }

    public static function editorCanAssignRole(User $editor, Role $role): bool
    {
        if (in_array($role->name, self::protectedRoleNames(), true)) {
            return PermissionHelper::hasEnterpriseBypassRole($editor);
        }

        return true;
    }

    /**
     * @param  array<int>  $roleIds
     */
    public static function validateRoleAssignment(User $editor, array $roleIds): ?string
    {
        if ($roleIds === []) {
            return null;
        }

        $roles = Role::query()->whereIn('id', $roleIds)->get();
        foreach ($roles as $role) {
            if (! self::editorCanAssignRole($editor, $role)) {
                $label = $role->display_name ?? $role->name;

                return 'Anda tidak memiliki izin untuk menetapkan role: '.$label.'.';
            }
        }

        return null;
    }

    public static function superAdminRoleName(): string
    {
        $names = config('sipeni.rbac.bypass_roles', ['super_administrator']);

        return (string) ($names[0] ?? 'super_administrator');
    }

    public static function countActiveSuperAdministrators(): int
    {
        $roleName = self::superAdminRoleName();

        return User::query()
            ->where('is_active', true)
            ->whereHas('roles', fn (Builder $q) => $q->where('name', $roleName))
            ->count();
    }

    public static function wouldRemoveLastSuperAdministrator(User $user, array $newRoleIds): bool
    {
        $superRole = Role::query()->where('name', self::superAdminRoleName())->first();
        if (! $superRole) {
            return false;
        }

        $userHasSuper = $user->roles()->where('roles.id', $superRole->id)->exists();
        if (! $userHasSuper || ! ($user->is_active ?? true)) {
            return false;
        }

        $stillHasSuper = in_array((int) $superRole->id, array_map('intval', $newRoleIds), true);
        if ($stillHasSuper) {
            return false;
        }

        return self::countActiveSuperAdministrators() <= 1;
    }
}
