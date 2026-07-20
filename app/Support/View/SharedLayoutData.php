<?php

namespace App\Support\View;

use App\Helpers\PermissionHelper;
use App\Models\User;

/**
 * Data layout (sidebar, user context) — dihitung sekali per HTTP request.
 */
final class SharedLayoutData
{
    /** @var array<string, mixed>|null */
    private static ?array $resolved = null;

    /**
     * @return array<string, mixed>
     */
    public static function resolve(?User $user = null): array
    {
        if (self::$resolved !== null) {
            return self::$resolved;
        }

        if (! $user instanceof User) {
            return self::$resolved = [
                'currentUser' => null,
                'accessibleMenus' => [],
                'userRoles' => [],
                'userRoleIds' => [],
                'userPrimaryRole' => null,
            ];
        }

        if (! $user->relationLoaded('roles')) {
            $user->load('roles:id,name,guard_name');
        }

        $roles = $user->roles;

        return self::$resolved = [
            'currentUser' => $user,
            'accessibleMenus' => PermissionHelper::getAccessibleMenus($user),
            'userRoles' => $roles->pluck('name')->all(),
            'userRoleIds' => $roles->pluck('id')->map(fn ($id) => (int) $id)->all(),
            'userPrimaryRole' => $roles->first(),
        ];
    }

    public static function flush(): void
    {
        self::$resolved = null;
    }
}
