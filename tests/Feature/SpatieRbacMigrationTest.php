<?php

namespace Tests\Feature;

use App\Helpers\PermissionHelper;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpatieRbacMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_sync_roles_and_spatie_permission_check(): void
    {
        $role = Role::create([
            'name' => 'test_role_spatie',
            'guard_name' => 'web',
            'display_name' => 'Test Role',
            'description' => null,
        ]);

        $perm = Permission::create([
            'name' => 'test.route.example',
            'guard_name' => 'web',
            'display_name' => 'Test',
            'module' => 'test',
            'group' => 'test',
            'description' => null,
            'sort_order' => 0,
        ]);

        $role->givePermissionTo($perm);

        $user = User::factory()->create();
        $user->syncRoles([$role->id]);

        $this->assertTrue($user->hasRole('test_role_spatie'));
        $this->assertTrue($user->checkPermissionTo('test.route.example'));
        $this->assertTrue(PermissionHelper::canAccess($user, 'test.route.example'));
    }

    public function test_spatie_wildcard_permission_on_role_grants_access(): void
    {
        $role = Role::create([
            'name' => 'test_role_wildcard',
            'guard_name' => 'web',
            'display_name' => 'Wildcard Role',
            'description' => null,
        ]);

        $wildcard = Permission::create([
            'name' => 'inventory.test-wildcard.*',
            'guard_name' => 'web',
            'display_name' => 'Wildcard',
            'module' => 'inventory',
            'group' => 'inventory',
            'description' => null,
            'sort_order' => 0,
        ]);

        $role->givePermissionTo($wildcard);

        $user = User::factory()->create();
        $user->syncRoles([$role->id]);

        $this->assertTrue($user->hasPermission('inventory.test-wildcard.index'));
        $this->assertTrue(PermissionHelper::canAccess($user, 'inventory.test-wildcard.index'));
    }
}
