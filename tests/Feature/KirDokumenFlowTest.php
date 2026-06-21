<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\ComprehensiveDummySeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class KirDokumenFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        $this->seed(ComprehensiveDummySeeder::class);
    }

    public function test_kir_dokumen_download_mode_returns_html_attachment(): void
    {
        $admin = User::query()->where('email', 'pusdatinppkp@gmail.com')->firstOrFail();
        $unitKerjaId = (int) DB::table('register_aset')->whereNotNull('id_unit_kerja')->value('id_unit_kerja');

        $response = $this->actingAs($admin)->get(route('asset.kartu-inventaris-ruangan.dokumen-unit', [
            'id_unit_kerja' => $unitKerjaId,
            'download' => 1,
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
        $response->assertHeader('Content-Disposition');
        $response->assertSee('KARTU INVENTARIS RUANGAN');
    }

    public function test_kir_dokumen_print_mode_renders_page_content(): void
    {
        $admin = User::query()->where('email', 'pusdatinppkp@gmail.com')->firstOrFail();
        $unitKerjaId = (int) DB::table('register_aset')->whereNotNull('id_unit_kerja')->value('id_unit_kerja');

        $response = $this->actingAs($admin)->get(route('asset.kartu-inventaris-ruangan.dokumen-unit', [
            'id_unit_kerja' => $unitKerjaId,
            'print' => 1,
        ]));

        $response->assertOk();
        $response->assertSee('KARTU INVENTARIS RUANGAN');
        $response->assertSee('window.print()');
    }

    public function test_pegawai_cannot_access_kir_dokumen_for_other_unit(): void
    {
        // Cari pegawai non-admin — role unit-scoped (admin_unit, kepala_unit, pegawai)
        // agar UserScope::mustScopeToUnitKerja() true, sehingga controller memblokir akses lintas unit.
        $pegawaiUnit = DB::table('master_pegawai')
            ->join('users', 'master_pegawai.user_id', '=', 'users.id')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->whereNotNull('master_pegawai.user_id')
            ->whereNotNull('master_pegawai.id_unit_kerja')
            ->where('roles.name', '!=', 'super_administrator')
            ->select('master_pegawai.*')
            ->orderBy('master_pegawai.id')
            ->first();
        $this->assertNotNull($pegawaiUnit);

        $targetUnitId = (int) DB::table('master_unit_kerja')
            ->where('id_unit_kerja', '!=', $pegawaiUnit->id_unit_kerja)
            ->orderBy('id_unit_kerja')
            ->value('id_unit_kerja');
        $this->assertGreaterThan(0, $targetUnitId);

        $pegawaiUser = User::query()->findOrFail($pegawaiUnit->user_id);
        $response = $this->actingAs($pegawaiUser)
            ->get(route('asset.kartu-inventaris-ruangan.dokumen-unit', [
                'id_unit_kerja' => $targetUnitId,
            ]));

        $response->assertForbidden();
    }
}

