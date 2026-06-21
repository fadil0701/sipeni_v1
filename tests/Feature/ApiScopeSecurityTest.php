<?php

namespace Tests\Feature;

use App\Models\PermintaanBarang;
use App\Models\User;
use Database\Seeders\ComprehensiveDummySeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApiScopeSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->seed(ComprehensiveDummySeeder::class);
    }

    public function test_unit_user_cannot_lookup_master_data_for_other_unit(): void
    {
        $pegawaiUnit = DB::table('master_pegawai')
            ->join('users', 'master_pegawai.user_id', '=', 'users.id')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->whereNotNull('master_pegawai.user_id')
            ->whereNotNull('master_pegawai.id_unit_kerja')
            ->where('roles.name', 'pegawai')
            ->select('master_pegawai.*')
            ->first();
        $this->assertNotNull($pegawaiUnit);

        $otherUnitId = (int) DB::table('master_unit_kerja')
            ->where('id_unit_kerja', '!=', $pegawaiUnit->id_unit_kerja)
            ->orderBy('id_unit_kerja')
            ->value('id_unit_kerja');
        $this->assertGreaterThan(0, $otherUnitId);

        $user = User::query()->findOrFail($pegawaiUnit->user_id);

        $this->actingAs($user)
            ->getJson(route('api.master.pegawai-by-unit', ['id_unit_kerja' => $otherUnitId]))
            ->assertForbidden();
    }

    public function test_unit_user_cannot_view_permintaan_from_other_unit(): void
    {
        $pegawaiUnit = DB::table('master_pegawai')
            ->join('users', 'master_pegawai.user_id', '=', 'users.id')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->whereNotNull('master_pegawai.user_id')
            ->whereNotNull('master_pegawai.id_unit_kerja')
            ->where('roles.name', 'pegawai')
            ->select('master_pegawai.*')
            ->first();
        $this->assertNotNull($pegawaiUnit);

        $otherPermintaanId = PermintaanBarang::query()
            ->where('id_unit_kerja', '!=', $pegawaiUnit->id_unit_kerja)
            ->value('id_permintaan');
        $this->assertNotNull($otherPermintaanId);

        $user = User::query()->findOrFail($pegawaiUnit->user_id);

        $this->actingAs($user)
            ->get(route('transaction.permintaan-barang.show', $otherPermintaanId))
            ->assertForbidden();
    }
}
