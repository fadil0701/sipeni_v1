<?php

namespace Tests\Feature;

use App\Helpers\PermissionHelper;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\ModuleRegistry;
use App\Services\Rbac\RolePermissionResolver;
use App\Support\Admin\SuperAdminGuard;
use App\Support\Admin\SystemRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class RbacArchitectureTest extends TestCase
{
    use RefreshDatabase;

    public function test_module_registry_exposes_features_and_display_groups(): void
    {
        $registry = app(ModuleRegistry::class);

        $this->assertArrayHasKey('aset-tetap-kir', $registry->getModules());
        $this->assertTrue($registry->hasFeature('aset-tetap-kir', 'inventory_effect'));
        $this->assertSame('Inventori', $registry->getDisplayGroupLabelForModule('aset-tetap-kir'));
        $this->assertContains('view_register_aset', $registry->getPermissions('aset-tetap-kir'));
    }

    public function test_role_assignment_grants_granular_permission(): void
    {
        $role = Role::create(['name' => 'rbac_arch_role', 'guard_name' => 'web', 'display_name' => 'Arch']);
        $perm = Permission::firstOrCreate(
            ['name' => 'transaction.permintaan-barang.index', 'guard_name' => 'web'],
            ['display_name' => 'Index', 'module' => 'transaction', 'group' => 'transaction', 'sort_order' => 0],
        );
        $role->givePermissionTo($perm);

        $user = User::factory()->create();
        $user->syncUnifiedRoles([$role->id]);

        $this->assertTrue(PermissionHelper::canAccess($user, 'transaction.permintaan-barang.index'));
    }

    public function test_matrix_expansion_includes_sibling_permissions_in_group(): void
    {
        $resolver = app(RolePermissionResolver::class);

        $p1 = Permission::firstOrCreate(
            ['name' => 'asset.register-aset.index', 'guard_name' => 'web'],
            ['display_name' => 'Index', 'module' => 'asset', 'group' => 'asset', 'sort_order' => 0],
        );
        $p2 = Permission::firstOrCreate(
            ['name' => 'asset.register-aset.show', 'guard_name' => 'web'],
            ['display_name' => 'Show', 'module' => 'asset', 'group' => 'asset', 'sort_order' => 1],
        );

        $assignable = collect([$p1, $p2]);
        $expanded = $resolver->expand([(int) $p1->id], $assignable);

        $this->assertContains((int) $p1->id, $expanded);
        $this->assertContains((int) $p2->id, $expanded);
    }

    public function test_matrix_collapse_reflects_checked_granular_permissions(): void
    {
        $resolver = app(RolePermissionResolver::class);

        $p1 = Permission::firstOrCreate(
            ['name' => 'transaction.permintaan-barang.index', 'guard_name' => 'web'],
            ['display_name' => 'Index', 'module' => 'transaction', 'group' => 'transaction', 'sort_order' => 0],
        );

        $assignable = collect([$p1]);
        $collapsed = $resolver->collapse([(int) $p1->id], $assignable);

        $this->assertArrayHasKey('permintaan-barang', $collapsed);
        $viewCell = $collapsed['permintaan-barang']['view'] ?? [];
        $this->assertContains((int) $p1->id, $viewCell['permission_ids'] ?? []);
        $this->assertTrue($viewCell['all_checked'] ?? false);
    }

    public function test_sidebar_visibility_follows_role_permission(): void
    {
        $role = Role::create(['name' => 'rbac_menu_role', 'guard_name' => 'web', 'display_name' => 'Menu']);
        $perm = Permission::firstOrCreate(
            ['name' => 'transaction.permintaan-barang.index', 'guard_name' => 'web'],
            ['display_name' => 'Index', 'module' => 'transaction', 'group' => 'transaction', 'sort_order' => 0],
        );
        $role->givePermissionTo($perm);

        $user = User::factory()->create();
        $user->syncUnifiedRoles([$role->id]);

        $this->assertTrue(PermissionHelper::canAccess($user, 'transaction.permintaan-barang.index'));
        $this->assertFalse(PermissionHelper::canAccess($user, 'admin.users.index'));
    }

    public function test_system_role_cannot_be_deleted_via_controller(): void
    {
        $role = Role::create([
            'name' => 'super_administrator',
            'guard_name' => 'web',
            'display_name' => 'Super',
            'is_system_role' => true,
            'is_protected' => true,
        ]);

        $this->assertTrue(SystemRole::isProtected($role));

        $admin = User::factory()->create();
        $admin->assignRole($role);

        $response = $this->actingAs($admin)->delete(route('admin.roles.destroy', $role->id));

        $response->assertRedirect(route('admin.roles.index'));
        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }

    public function test_user_without_role_cannot_access_protected_permission(): void
    {
        $user = User::factory()->create();
        $user->syncUnifiedRoles([]);

        $this->assertFalse(PermissionHelper::canAccess($user, 'transaction.permintaan-barang.index'));
    }

    public function test_get_action_permissions_resolves_asset_module_view(): void
    {
        $resolver = app(RolePermissionResolver::class);

        $perm = Permission::firstOrCreate(
            ['name' => 'asset.register-aset.index', 'guard_name' => 'web'],
            ['display_name' => 'Index', 'module' => 'asset', 'group' => 'asset', 'sort_order' => 0],
        );

        $found = $resolver->getActionPermissions(
            'aset-tetap-kir',
            'view',
            Collection::make([$perm])
        );

        $this->assertTrue($found->contains('id', $perm->id));
    }

    public function test_super_admin_guard_detects_last_super_admin_removal(): void
    {
        $role = Role::create([
            'name' => 'super_administrator',
            'guard_name' => 'web',
            'display_name' => 'Super',
            'is_protected' => true,
        ]);

        $user = User::factory()->create(['is_active' => true]);
        $user->syncUnifiedRoles([$role->id]);

        $this->assertSame(1, SuperAdminGuard::countActiveSuperAdministrators());
        $this->assertTrue(SuperAdminGuard::wouldRemoveLastSuperAdministrator($user, []));
    }
}
