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

    public function test_kir_dokumen_includes_latest_pemeliharaan_and_kalibrasi_dates(): void
    {
        $admin = User::query()->where('email', 'pusdatinppkp@gmail.com')->firstOrFail();

        $kir = DB::table('kartu_inventaris_ruangan')
            ->join('register_aset', 'kartu_inventaris_ruangan.id_register_aset', '=', 'register_aset.id_register_aset')
            ->whereNotNull('register_aset.id_unit_kerja')
            ->whereNotNull('register_aset.id_ruangan')
            ->select(
                'kartu_inventaris_ruangan.id_kir',
                'kartu_inventaris_ruangan.id_register_aset',
                'register_aset.id_unit_kerja',
                'register_aset.nomor_register'
            )
            ->orderBy('kartu_inventaris_ruangan.id_kir')
            ->first();
        $this->assertNotNull($kir);

        $tanggalPemeliharaan = now()->subDays(10)->toDateString();
        $tanggalKalibrasi = now()->subDays(5)->toDateString();

        DB::table('riwayat_pemeliharaan')->insert([
            'id_register_aset' => $kir->id_register_aset,
            'tanggal_pemeliharaan' => $tanggalPemeliharaan,
            'jenis_pemeliharaan' => 'PERBAIKAN',
            'status' => 'SELESAI',
            'keterangan' => 'Uji KIR tanggal pemeliharaan',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('kalibrasi_aset')->insert([
            'no_kalibrasi' => 'KAL/TEST/'.now()->format('YmdHis'),
            'id_register_aset' => $kir->id_register_aset,
            'tanggal_kalibrasi' => $tanggalKalibrasi,
            'tanggal_berlaku' => $tanggalKalibrasi,
            'tanggal_kadaluarsa' => now()->addYear()->toDateString(),
            'status_kalibrasi' => 'VALID',
            'created_by' => $admin->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('asset.kartu-inventaris-ruangan.dokumen-unit', [
            'id_unit_kerja' => $kir->id_unit_kerja,
            'print' => 1,
        ]));

        $response->assertOk();
        $response->assertSee(\Carbon\Carbon::parse($tanggalPemeliharaan)->format('d/m/Y'), false);
        $response->assertSee(\Carbon\Carbon::parse($tanggalKalibrasi)->format('d/m/Y'), false);
        if (! empty($kir->nomor_register)) {
            $response->assertSee($kir->nomor_register, false);
        }
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

