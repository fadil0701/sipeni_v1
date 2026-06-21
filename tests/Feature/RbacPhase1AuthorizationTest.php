<?php

namespace Tests\Feature;

use App\Helpers\PermissionHelper;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacPhase1AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_super_administrator_has_enterprise_bypass(): void
    {
        $super = Role::create(['name' => 'super_administrator', 'guard_name' => 'web', 'display_name' => 'Super']);
        $admin = Role::create(['name' => 'administrator', 'guard_name' => 'web', 'display_name' => 'Admin']);
        $legacyAdmin = Role::create(['name' => 'admin', 'guard_name' => 'web', 'display_name' => 'Admin IT']);

        $userSuper = User::factory()->create();
        $userSuper->assignRole($super);

        $userAdmin = User::factory()->create();
        $userAdmin->assignRole($admin);

        $userLegacy = User::factory()->create();
        $userLegacy->assignRole($legacyAdmin);

        $this->assertTrue(PermissionHelper::hasEnterpriseBypassRole($userSuper));
        $this->assertFalse(PermissionHelper::hasEnterpriseBypassRole($userAdmin));
        $this->assertFalse(PermissionHelper::hasEnterpriseBypassRole($userLegacy));
    }

    public function test_can_access_uses_database_only_without_static_fallback(): void
    {
        $role = Role::create(['name' => 'rbac_test_role', 'guard_name' => 'web', 'display_name' => 'Test']);
        $perm = Permission::create([
            'name' => 'transaction.permintaan-barang.index',
            'guard_name' => 'web',
            'display_name' => 'Index',
            'module' => 'transaction',
            'group' => 'transaction',
            'description' => null,
            'sort_order' => 0,
        ]);
        $role->givePermissionTo($perm);

        $user = User::factory()->create();
        $user->assignRole($role);

        $this->assertTrue(PermissionHelper::canAccess($user, 'transaction.permintaan-barang.index'));
        $this->assertFalse(PermissionHelper::canAccess($user, 'transaction.permintaan-barang.destroy'));
    }

    public function test_spatie_native_wildcard_grants_child_permission(): void
    {
        $role = Role::create(['name' => 'rbac_wildcard_role', 'guard_name' => 'web', 'display_name' => 'W']);
        $wildcard = Permission::create([
            'name' => 'planning.*',
            'guard_name' => 'web',
            'display_name' => 'Planning All',
            'module' => 'planning',
            'group' => 'planning',
            'description' => null,
            'sort_order' => 0,
        ]);
        $role->givePermissionTo($wildcard);

        $user = User::factory()->create();
        $user->assignRole($role);

        $this->assertTrue($user->hasPermission('planning.rku.index'));
        $this->assertTrue(PermissionHelper::canAccess($user, 'planning.rku.index'));
    }

    public function test_admin_role_no_longer_auto_passes_has_permission(): void
    {
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web', 'display_name' => 'Admin']);
        $user = User::factory()->create();
        $user->assignRole($adminRole);

        $this->assertFalse($user->hasPermission('transaction.permintaan-barang.index'));
    }

    public function test_no_store_to_create_alias_mapping(): void
    {
        $role = Role::create(['name' => 'rbac_alias_role', 'guard_name' => 'web', 'display_name' => 'A']);
        $create = Permission::create([
            'name' => 'transaction.permintaan-barang.create',
            'guard_name' => 'web',
            'display_name' => 'Create',
            'module' => 'transaction',
            'group' => 'transaction',
            'description' => null,
            'sort_order' => 0,
        ]);
        $role->givePermissionTo($create);

        $user = User::factory()->create();
        $user->assignRole($role);

        $this->assertTrue(PermissionHelper::canAccess($user, 'transaction.permintaan-barang.create'));
        $this->assertFalse(PermissionHelper::canAccess($user, 'transaction.permintaan-barang.store'));
    }
}
