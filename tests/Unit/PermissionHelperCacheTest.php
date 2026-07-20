<?php

namespace Tests\Unit;

use App\Helpers\PermissionHelper;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PermissionHelperCacheTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        PermissionHelper::bumpAccessibleMenusCacheGeneration();
    }

    public function test_owns_permission_uses_exact_and_wildcard_without_static_map(): void
    {
        $role = Role::create(['name' => 'cache_test_role', 'guard_name' => 'web', 'display_name' => 'Cache']);
        $exact = Permission::query()->firstOrCreate(
            ['name' => 'cache.test.inventory.index', 'guard_name' => 'web'],
            [
                'display_name' => 'Cache Inventory Index',
                'module' => 'cache',
                'group' => 'cache',
                'description' => null,
                'sort_order' => 0,
            ]
        );
        $wildcard = Permission::query()->firstOrCreate(
            ['name' => 'cache.test.planning.*', 'guard_name' => 'web'],
            [
                'display_name' => 'Cache Planning All',
                'module' => 'cache',
                'group' => 'cache',
                'description' => null,
                'sort_order' => 0,
            ]
        );
        $role->givePermissionTo([$exact, $wildcard]);

        $user = User::factory()->create();
        $user->assignRole($role);
        $user->load('roles:id,name,guard_name');

        PermissionHelper::forgetAccessibleMenusCacheForUser($user->id);

        $this->assertTrue(PermissionHelper::ownsPermission($user, 'cache.test.inventory.index'));
        $this->assertTrue(PermissionHelper::ownsPermission($user, 'cache.test.planning.rku.index'));
        $this->assertTrue(PermissionHelper::canAccess($user, 'cache.test.planning.rku.create'));
        $this->assertFalse(PermissionHelper::ownsPermission($user, 'admin.roles.index'));
        $this->assertFalse($user->roles->first()?->relationLoaded('permissions'));
    }

    public function test_enterprise_bypass_skips_permission_name_load(): void
    {
        $role = Role::create(['name' => 'super_administrator', 'guard_name' => 'web', 'display_name' => 'Super']);
        $user = User::factory()->create();
        $user->assignRole($role);
        $user->load('roles:id,name,guard_name');

        PermissionHelper::warmPermissionCache($user);

        $this->assertTrue(PermissionHelper::hasEnterpriseBypassRole($user));
        $this->assertTrue(PermissionHelper::canAccess($user, 'anything.goes'));
        $this->assertFalse($user->roles->first()?->relationLoaded('permissions'));
    }
}
