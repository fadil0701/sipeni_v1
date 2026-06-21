<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPasswordPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_store_rejects_weak_password(): void
    {
        $admin = $this->superAdminUser();
        $role = Role::query()->where('name', 'admin_unit')->where('guard_name', 'web')->firstOrFail();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'User Baru',
            'email' => 'user-baru-policy@test.local',
            'password' => 'short1A!',
            'password_confirmation' => 'short1A!',
            'is_active' => '1',
            'role_ids' => [$role->id],
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'user-baru-policy@test.local']);
    }

    public function test_user_store_accepts_strong_password(): void
    {
        $admin = $this->superAdminUser();
        $role = Role::query()->where('name', 'admin_unit')->where('guard_name', 'web')->firstOrFail();
        $password = 'ValidPass1!Word';

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'User Kuat',
            'email' => 'user-kuat-policy@test.local',
            'password' => $password,
            'password_confirmation' => $password,
            'is_active' => '1',
            'role_ids' => [$role->id],
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['email' => 'user-kuat-policy@test.local']);
    }

    private function superAdminUser(): User
    {
        $this->seed();

        return User::query()->where('email', 'pusdatinppkp@gmail.com')->firstOrFail();
    }
}
