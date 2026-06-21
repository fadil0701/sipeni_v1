<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use App\Support\Audit\AuditDataSanitizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditTrailV1Test extends TestCase
{
    use RefreshDatabase;

    private function userWithPermission(string $permissionName): User
    {
        $role = Role::create([
            'name' => 'audit_test_role_'.uniqid(),
            'guard_name' => 'web',
            'display_name' => 'Audit Test',
        ]);

        $perm = Permission::firstOrCreate(
            ['name' => $permissionName, 'guard_name' => 'web'],
            ['display_name' => 'Perm', 'module' => 'admin', 'group' => 'admin', 'sort_order' => 0],
        );
        $role->givePermissionTo($perm);

        $user = User::factory()->create();
        $user->syncUnifiedRoles([$role->id]);

        return $user;
    }

    private function superAdminUser(): User
    {
        $role = Role::firstOrCreate(
            ['name' => 'super_administrator', 'guard_name' => 'web'],
            ['display_name' => 'Super Administrator', 'is_system_role' => true, 'is_protected' => true],
        );

        $user = User::factory()->create();
        $user->syncUnifiedRoles([$role->id]);

        return $user;
    }

    public function test_user_update_creates_audit_log(): void
    {
        $editor = $this->superAdminUser();
        $target = User::factory()->create(['name' => 'Before Name']);

        $role = Role::create(['name' => 'assign_role_'.uniqid(), 'guard_name' => 'web', 'display_name' => 'R']);
        $target->syncUnifiedRoles([$role->id]);

        $this->actingAs($editor)->put(route('admin.users.update', $target->id), [
            'name' => 'After Name',
            'email' => $target->email,
            'is_active' => 1,
            'role_ids' => [$role->id],
        ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('activity_logs', [
            'module_key' => AuditLogService::MODULE_USER_MANAGEMENT,
            'action' => 'updated',
            'entity_type' => User::class,
            'entity_id' => $target->id,
        ]);
    }

    public function test_role_update_creates_audit_log(): void
    {
        $editor = $this->superAdminUser();
        $role = Role::create([
            'name' => 'role_audit_'.uniqid(),
            'guard_name' => 'web',
            'display_name' => 'Old Label',
            'level_akses' => 'unit',
            'is_active' => true,
        ]);

        $this->actingAs($editor)->put(route('admin.roles.update', $role->id), [
            'name' => $role->name,
            'display_name' => 'New Label',
            'level_akses' => 'unit',
            'is_active' => 1,
            'permissions' => [],
        ])->assertRedirect(route('admin.roles.index'));

        $this->assertDatabaseHas('activity_logs', [
            'module_key' => AuditLogService::MODULE_USER_MANAGEMENT,
            'action' => 'updated',
            'entity_type' => Role::class,
            'entity_id' => $role->id,
        ]);
    }

    public function test_old_and_new_values_store_changed_fields_only(): void
    {
        $user = User::factory()->create(['email' => 'same@test.local']);

        $log = AuditLogService::logUpdate(
            module: AuditLogService::MODULE_USER_MANAGEMENT,
            entity: $user,
            old: ['name' => 'A', 'email' => 'same@test.local'],
            new: ['name' => 'B', 'email' => 'same@test.local'],
        );

        $this->assertSame(['name' => 'A'], $log->old_values);
        $this->assertSame(['name' => 'B'], $log->new_values);
    }

    public function test_sensitive_fields_are_redacted(): void
    {
        $sanitized = AuditDataSanitizer::sanitizeArray([
            'name' => 'User',
            'password' => 'secret123',
            'remember_token' => 'tok',
        ]);

        $this->assertSame('[redacted]', $sanitized['password']);
        $this->assertSame('[redacted]', $sanitized['remember_token']);
        $this->assertSame('User', $sanitized['name']);
    }

    public function test_module_key_uses_registry_module(): void
    {
        $log = AuditLogService::logAction(
            module: AuditLogService::MODULE_USER_MANAGEMENT,
            action: 'test',
            description: 'registry module',
        );

        $this->assertSame('manajemen-user-role', $log->module_key);
    }

    public function test_audit_viewer_requires_authorization(): void
    {
        $guest = $this->get(route('admin.audit-trail.index'));
        $guest->assertRedirect('/login');

        $user = User::factory()->create();
        $this->actingAs($user)->get(route('admin.audit-trail.index'))->assertForbidden();

        $viewer = $this->userWithPermission('admin.audit-trail.index');
        $this->actingAs($viewer)->get(route('admin.audit-trail.index'))->assertOk();
    }

    public function test_user_delete_is_audited(): void
    {
        $editor = $this->superAdminUser();
        $target = User::factory()->create();
        $role = Role::create(['name' => 'del_role_'.uniqid(), 'guard_name' => 'web', 'display_name' => 'R']);
        $target->syncUnifiedRoles([$role->id]);

        $this->actingAs($editor)->delete(route('admin.users.destroy', $target->id))
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'deleted',
            'entity_type' => User::class,
            'entity_id' => $target->id,
        ]);
    }

    public function test_permission_matrix_update_is_logged(): void
    {
        $role = Role::create([
            'name' => 'matrix_role_'.uniqid(),
            'guard_name' => 'web',
            'display_name' => 'Matrix',
            'level_akses' => 'pusat',
            'is_active' => true,
        ]);

        AuditLogService::logAction(
            module: AuditLogService::MODULE_USER_MANAGEMENT,
            action: 'permission_matrix_updated',
            description: 'Role permission matrix updated',
            entity: $role,
            old: ['permission_ids' => [1, 2]],
            new: ['permission_ids' => [1, 2, 3]],
        );

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'permission_matrix_updated',
            'entity_type' => Role::class,
            'entity_id' => $role->id,
        ]);
    }
}
