<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Support\Rbac\RoleCompatibility;
use App\Support\Rbac\RbacRoles;
use App\Support\Rbac\UserScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacPhase2UserScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_unit_must_scope_to_unit_kerja(): void
    {
        $role = Role::create(['name' => 'admin_unit', 'guard_name' => 'web', 'display_name' => 'Admin Unit']);
        $user = User::factory()->create();
        $user->assignRole($role);

        $this->assertTrue(UserScope::mustScopeToUnitKerja($user));
        $this->assertFalse(UserScope::canViewCrossUnitData($user));
    }

    public function test_super_administrator_does_not_scope_to_unit(): void
    {
        $role = Role::create(['name' => 'super_administrator', 'guard_name' => 'web', 'display_name' => 'Super']);
        $user = User::factory()->create();
        $user->assignRole($role);

        $this->assertFalse(UserScope::mustScopeToUnitKerja($user));
        $this->assertTrue(UserScope::canViewCrossUnitData($user));
    }

    public function test_legacy_pegawai_role_still_unit_scoped(): void
    {
        $role = Role::create(['name' => 'pegawai', 'guard_name' => 'web', 'display_name' => 'Pegawai']);
        $user = User::factory()->create();
        $user->assignRole($role);

        $this->assertTrue(UserScope::mustScopeToUnitKerja($user));
    }

    public function test_unit_kerja_data_restricted_excludes_warehouse_pusat_role(): void
    {
        $unitRole = Role::create(['name' => 'admin_unit', 'guard_name' => 'web', 'display_name' => 'AU']);
        $warehouseRole = Role::create(['name' => 'admin_gudang_pusat', 'guard_name' => 'web', 'display_name' => 'AGP']);
        $user = User::factory()->create();
        $user->assignRole([$unitRole, $warehouseRole]);

        $this->assertFalse(UserScope::isUnitKerjaDataRestricted($user));
    }

    public function test_normalize_role_names_maps_legacy(): void
    {
        $this->assertSame('admin_unit', RoleCompatibility::canonicalFor('pegawai_unit'));
        $this->assertEquals(
            ['admin_unit', 'perencana'],
            RbacRoles::normalizeRoleNames(['pegawai', 'perencanaan'])
        );
    }
}
