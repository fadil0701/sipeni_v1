<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Services\PanduanPenggunaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanduanPenggunaTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_redirected_from_panduan_index(): void
    {
        $this->get('/panduan')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_panduan_hub(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/panduan')
            ->assertOk()
            ->assertSee('Panduan Pengguna', false);
    }

    public function test_admin_unit_sees_role_guide_on_hub(): void
    {
        $role = Role::create(['name' => 'admin_unit', 'guard_name' => 'web', 'display_name' => 'Admin Unit']);
        $user = User::factory()->create();
        $user->assignRole($role);

        $guides = PanduanPenggunaService::roleGuidesForUser($user);
        $this->assertNotEmpty($guides);
        $slug = $guides[0]['slug'];

        $this->actingAs($user)
            ->get('/panduan')
            ->assertOk()
            ->assertSee(route('panduan.show', $slug), false);
    }

    public function test_can_render_general_chapter(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/panduan/pengenalan')
            ->assertOk()
            ->assertSee('Pengenalan', false);
    }

    public function test_can_render_role_chapter(): void
    {
        $role = Role::create(['name' => 'perencana', 'guard_name' => 'web', 'display_name' => 'Perencana']);
        $user = User::factory()->create();
        $user->assignRole($role);

        $this->actingAs($user)
            ->get('/panduan/role-perencana')
            ->assertOk()
            ->assertSee('Perencana', false);
    }

    public function test_can_render_role_chapter_with_underscore_in_slug(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/panduan/role-super_administrator')
            ->assertOk()
            ->assertSee('Super Administrator', false);
    }

    public function test_can_render_chapter_by_markdown_filename(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/panduan/04-matrik-akses-role.md')
            ->assertOk()
            ->assertSee('Matriks', false);
    }

    public function test_legacy_root_markdown_path_redirects_to_panduan(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/04-matrik-akses-role.md')
            ->assertRedirect('/panduan/matriks-role');
    }

    public function test_unknown_doc_returns_not_found(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/panduan/tidak-ada')
            ->assertNotFound();
    }

    public function test_legacy_pegawai_role_maps_to_admin_unit_guide(): void
    {
        $role = Role::create(['name' => 'pegawai', 'guard_name' => 'web', 'display_name' => 'Pegawai']);
        $user = User::factory()->create();
        $user->assignRole($role);

        $guides = PanduanPenggunaService::roleGuidesForUser($user);

        $this->assertCount(1, $guides);
        $this->assertSame('role-admin_unit', $guides[0]['slug']);
    }

    public function test_dashboard_shows_panduan_shortcuts_for_role(): void
    {
        $role = Role::create(['name' => 'pengadaan', 'guard_name' => 'web', 'display_name' => 'Pengadaan']);
        $user = User::factory()->create();
        $user->assignRole($role);

        $this->actingAs($user)
            ->get(route('user.dashboard'))
            ->assertOk()
            ->assertSee('Panduan Pengguna', false)
            ->assertSee(route('panduan.show', 'role-pengadaan'), false);
    }
}
