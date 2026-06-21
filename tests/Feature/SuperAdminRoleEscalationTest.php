<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminRoleEscalationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_it_cannot_assign_super_administrator_role(): void
    {
        $superRole = Role::query()->where('name', 'super_administrator')->firstOrFail();
        $adminIt = User::query()->where('email', 'admin-it@sipeni.local')->firstOrFail();
        $target = User::factory()->create([
            'email' => 'target@test.local',
            'password' => Hash::make('Password@123'),
            'is_active' => true,
        ]);
        $target->syncUnifiedRoles([]);

        $response = $this->actingAs($adminIt)->put(route('admin.users.update', $target->id), [
            'name' => $target->name,
            'email' => $target->email,
            'is_active' => '1',
            'role_ids' => [(string) $superRole->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertFalse($target->fresh()->hasRole('super_administrator'));
    }

    public function test_super_administrator_can_assign_super_administrator_role(): void
    {
        $superRole = Role::query()->where('name', 'super_administrator')->firstOrFail();
        $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
        $superAdmin = User::query()->where('email', 'pusdatinppkp@gmail.com')->firstOrFail();
        $target = User::factory()->create([
            'email' => 'promoted@test.local',
            'password' => Hash::make('Password@123'),
            'is_active' => true,
        ]);
        $target->syncUnifiedRoles([$adminRole->id]);

        $response = $this->actingAs($superAdmin)->put(route('admin.users.update', $target->id), [
            'name' => $target->name,
            'email' => $target->email,
            'is_active' => '1',
            'role_ids' => [(string) $superRole->id],
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');
        $this->assertTrue($target->fresh()->hasRole('super_administrator'));
    }

    public function test_super_admin_guard_blocks_non_super_editor(): void
    {
        $superRole = Role::query()->where('name', 'super_administrator')->firstOrFail();
        $adminIt = User::query()->where('email', 'admin-it@sipeni.local')->firstOrFail();

        $message = SuperAdminGuard::validateRoleAssignment($adminIt, [(int) $superRole->id]);

        $this->assertNotNull($message);
        $this->assertStringContainsString('Super Administrator', $message);
    }
}
