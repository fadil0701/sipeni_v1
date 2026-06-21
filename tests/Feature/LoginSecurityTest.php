<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\Admin\SuperAdminGuard;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'inactive@test.local',
            'password' => Hash::make('ValidPass1!Word'),
            'is_active' => false,
        ]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'ValidPass1!Word',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_active_user_can_login(): void
    {
        $user = User::query()->where('email', 'pusdatinppkp@gmail.com')->firstOrFail();

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'Admin@12345678',
        ]);

        $response->assertRedirect(route('user.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_is_rate_limited_after_repeated_failures(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login'), [
                'email' => 'attacker@test.local',
                'password' => 'wrong-password',
            ])->assertSessionHasErrors('email');
        }

        $response = $this->post(route('login'), [
            'email' => 'attacker@test.local',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(429);
    }

    public function test_get_logout_route_is_not_available(): void
    {
        $user = User::query()->where('email', 'pusdatinppkp@gmail.com')->firstOrFail();

        $this->actingAs($user)->get('/logout')->assertMethodNotAllowed();
    }
}
