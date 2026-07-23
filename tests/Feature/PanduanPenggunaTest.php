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

    private function makeAdministrator(): User
    {
        $role = Role::firstOrCreate(
            ['name' => 'administrator', 'guard_name' => 'web'],
            ['display_name' => 'Administrator']
        );
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_guest_redirected_from_panduan_index(): void
    {
        $this->get('/panduan')->assertRedirect('/login');
    }

    public function test_non_administrator_cannot_view_panduan_hub(): void
    {
        $role = Role::firstOrCreate(
            ['name' => 'admin_unit', 'guard_name' => 'web'],
            ['display_name' => 'Admin Unit']
        );
        $user = User::factory()->create();
        $user->assignRole($role);

        $this->actingAs($user)
            ->get('/panduan')
            ->assertForbidden();
    }

    public function test_administrator_can_view_panduan_hub(): void
    {
        $user = $this->makeAdministrator();

        $this->actingAs($user)
            ->get('/panduan')
            ->assertOk()
            ->assertSee('Panduan Pengguna', false);
    }

    public function test_administrator_sees_role_guide_links_on_hub(): void
    {
        $admin = $this->makeAdministrator();
        $unitRole = Role::firstOrCreate(
            ['name' => 'admin_unit', 'guard_name' => 'web'],
            ['display_name' => 'Admin Unit']
        );
        $admin->assignRole($unitRole);

        $guides = PanduanPenggunaService::roleGuidesForUser($admin);
        $this->assertNotEmpty($guides);
        $slug = $guides[0]['slug'];

        $this->actingAs($admin)
            ->get('/panduan')
            ->assertOk()
            ->assertSee(route('panduan.show', $slug), false);
    }

    public function test_administrator_can_render_general_chapter(): void
    {
        $user = $this->makeAdministrator();

        $this->actingAs($user)
            ->get('/panduan/pengenalan')
            ->assertOk()
            ->assertSee('Pengenalan', false);
    }

    public function test_administrator_can_render_role_permission_chapter(): void
    {
        $user = $this->makeAdministrator();

        $this->actingAs($user)
            ->get('/panduan/role-permission-user')
            ->assertOk()
            ->assertSee('Mengelola Role', false);
    }

    public function test_administrator_can_render_role_chapter(): void
    {
        $user = $this->makeAdministrator();

        $this->actingAs($user)
            ->get('/panduan/role-perencana')
            ->assertOk()
            ->assertSee('Perencana', false);
    }

    public function test_administrator_can_render_teknisi_role_chapter(): void
    {
        $user = $this->makeAdministrator();

        $this->actingAs($user)
            ->get('/panduan/role-teknisi_it')
            ->assertOk()
            ->assertSee('Teknisi IT', false);
    }

    public function test_can_render_role_chapter_with_underscore_in_slug(): void
    {
        $user = $this->makeAdministrator();

        $this->actingAs($user)
            ->get('/panduan/role-super_administrator')
            ->assertOk()
            ->assertSee('Super Administrator', false);
    }

    public function test_can_render_chapter_by_markdown_filename(): void
    {
        $user = $this->makeAdministrator();

        $this->actingAs($user)
            ->get('/panduan/04-matrik-akses-role.md')
            ->assertOk()
            ->assertSee('Matriks', false);
    }

    public function test_legacy_root_markdown_path_redirects_to_panduan(): void
    {
        $user = $this->makeAdministrator();

        $this->actingAs($user)
            ->get('/04-matrik-akses-role.md')
            ->assertRedirect('/panduan/matriks-role');
    }

    public function test_unknown_doc_returns_not_found(): void
    {
        $user = $this->makeAdministrator();

        $this->actingAs($user)
            ->get('/panduan/tidak-ada')
            ->assertNotFound();
    }

    public function test_legacy_pegawai_role_maps_to_admin_unit_guide(): void
    {
        $role = Role::firstOrCreate(
            ['name' => 'pegawai', 'guard_name' => 'web'],
            ['display_name' => 'Pegawai']
        );
        $user = User::factory()->create();
        $user->assignRole($role);

        $guides = PanduanPenggunaService::roleGuidesForUser($user);

        $this->assertCount(1, $guides);
        $this->assertSame('role-admin_unit', $guides[0]['slug']);
    }

    public function test_non_administrator_dashboard_does_not_show_panduan_link(): void
    {
        $role = Role::firstOrCreate(
            ['name' => 'pengadaan', 'guard_name' => 'web'],
            ['display_name' => 'Pengadaan']
        );
        $user = User::factory()->create();
        $user->assignRole($role);

        $this->actingAs($user)
            ->get(route('user.dashboard'))
            ->assertOk()
            ->assertDontSee('Semua panduan', false)
            ->assertDontSee('>Panduan Pengguna<', false);
    }

    public function test_administrator_dashboard_shows_panduan_card(): void
    {
        $user = $this->makeAdministrator();

        $this->actingAs($user)
            ->get(route('user.dashboard'))
            ->assertOk()
            ->assertSee('Semua panduan', false)
            ->assertSee(route('panduan.index'), false);
    }

    public function test_user_can_access_helper(): void
    {
        $admin = $this->makeAdministrator();
        $this->assertTrue(PanduanPenggunaService::userCanAccess($admin));

        $other = User::factory()->create();
        $this->assertFalse(PanduanPenggunaService::userCanAccess($other));
    }
}
