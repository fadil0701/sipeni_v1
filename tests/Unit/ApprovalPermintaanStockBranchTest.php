<?php

namespace Tests\Unit;

use App\Enums\PermintaanBarangStatus;
use App\Models\ApprovalFlowDefinition;
use App\Models\ApprovalLog;
use App\Models\PermintaanBarang;
use App\Models\Role;
use App\Models\User;
use App\Services\ApprovalPermintaanService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApprovalPermintaanStockBranchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->seed(\Database\Seeders\ComprehensiveDummySeeder::class);
    }

    public function test_verifikasi_with_stock_creates_gudang_disposisi_not_pengadaan(): void
    {
        $admin = User::query()->where('email', 'pusdatinppkp@gmail.com')->firstOrFail();
        $permintaan = $this->createSubmittedPermintaan($admin, withStock: true);

        $step3 = $this->pendingStep($permintaan->id_permintaan, 3);
        app(ApprovalPermintaanService::class)->verifikasi($step3->id, $admin, []);

        $permintaan->refresh();
        $this->assertSame(PermintaanBarangStatus::ProsesDistribusi, $permintaan->status);

        $roleNames = $this->pendingStep4RoleNames($permintaan->id_permintaan);
        $this->assertContains('admin_gudang_persediaan', $roleNames);
        $this->assertNotContains('pengadaan', $roleNames);
        $this->assertNotContains('kepala_pusat', $roleNames);
    }

    public function test_verifikasi_without_stock_requires_kepala_pusat_before_pengadaan(): void
    {
        $admin = User::query()->where('email', 'pusdatinppkp@gmail.com')->firstOrFail();
        $permintaan = $this->createSubmittedPermintaan($admin, withStock: false);

        $step3 = $this->pendingStep($permintaan->id_permintaan, 3);
        app(ApprovalPermintaanService::class)->verifikasi($step3->id, $admin, []);

        $permintaan->refresh();
        $this->assertSame(PermintaanBarangStatus::Diverifikasi, $permintaan->status);

        $roleNames = $this->pendingStep4RoleNames($permintaan->id_permintaan);
        $this->assertSame(['kepala_pusat'], $roleNames);

        $kepalaLog = ApprovalLog::query()
            ->where('modul_approval', 'PERMINTAAN_BARANG')
            ->where('id_referensi', $permintaan->id_permintaan)
            ->where('status', 'MENUNGGU')
            ->whereHas('approvalFlow.role', fn ($q) => $q->where('name', 'kepala_pusat'))
            ->firstOrFail();

        app(ApprovalPermintaanService::class)->approve($kepalaLog->id, $admin, 'OK pengadaan');

        $permintaan->refresh();
        $this->assertSame(PermintaanBarangStatus::MenungguPengadaan, $permintaan->status);

        $afterApproveRoles = $this->pendingStep4RoleNames($permintaan->id_permintaan);
        $this->assertContains('pengadaan', $afterApproveRoles);
        $this->assertNotContains('kepala_pusat', $afterApproveRoles);
    }

    private function createSubmittedPermintaan(User $admin, bool $withStock): PermintaanBarang
    {
        $unitId = (int) DB::table('master_unit_kerja')->value('id_unit_kerja');
        $pegawaiId = (int) DB::table('master_pegawai')->where('id_unit_kerja', $unitId)->value('id')
            ?: (int) DB::table('master_pegawai')->value('id');
        $barangId = (int) DB::table('master_data_barang')->where('kode_data_barang', 'BRG-DMY-001')->value('id_data_barang')
            ?: (int) DB::table('master_data_barang')->value('id_data_barang');
        $satuanId = (int) DB::table('master_satuan')->value('id_satuan');
        $gudangPusatId = (int) DB::table('master_gudang')
            ->where('jenis_gudang', 'PUSAT')
            ->where('kategori_gudang', 'PERSEDIAAN')
            ->value('id_gudang');

        // Store memvalidasi stok: selalu buat dengan stok tersedia dulu.
        $now = now();
        if ($barangId > 0 && $gudangPusatId > 0) {
            DB::table('data_stock')->updateOrInsert(
                ['id_data_barang' => $barangId, 'id_gudang' => $gudangPusatId],
                [
                    'qty_awal' => 50,
                    'qty_masuk' => 0,
                    'qty_keluar' => 0,
                    'qty_akhir' => 50,
                    'id_satuan' => $satuanId,
                    'last_updated' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $this->actingAs($admin)->post(route('transaction.permintaan-barang.store'), [
            'id_unit_kerja' => $unitId,
            'id_pemohon' => $pegawaiId,
            'tanggal_permintaan' => now()->toDateString(),
            'tipe_permintaan' => 'RUTIN',
            'jenis_permintaan' => ['PERSEDIAAN'],
            'keterangan' => 'Stock branch test',
            'detail' => [
                [
                    'id_data_barang' => $barangId,
                    'qty_diminta' => 1,
                    'id_satuan' => $satuanId,
                ],
            ],
        ])->assertRedirect();

        $permintaan = PermintaanBarang::query()->latest('id_permintaan')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('transaction.permintaan-barang.ajukan', $permintaan->id_permintaan))
            ->assertRedirect();

        $step2 = $this->pendingStep($permintaan->id_permintaan, 2);
        app(ApprovalPermintaanService::class)->mengetahui($step2->id, $admin, null);

        // Setelah diajukan: kosongkan stok pusat untuk menguji jalur pengadaan.
        if (! $withStock && $barangId > 0) {
            $gudangPusatIds = DB::table('master_gudang')
                ->where('jenis_gudang', 'PUSAT')
                ->whereIn('kategori_gudang', ['FARMASI', 'PERSEDIAAN'])
                ->pluck('id_gudang')
                ->all();
            if ($gudangPusatIds !== []) {
                DB::table('data_stock')
                    ->where('id_data_barang', $barangId)
                    ->whereIn('id_gudang', $gudangPusatIds)
                    ->update(['qty_akhir' => 0, 'updated_at' => now()]);
            }
        }

        return $permintaan->fresh();
    }

    private function pendingStep(int $idPermintaan, int $stepOrder): ApprovalLog
    {
        return ApprovalLog::query()
            ->where('modul_approval', 'PERMINTAAN_BARANG')
            ->where('id_referensi', $idPermintaan)
            ->where('status', 'MENUNGGU')
            ->whereHas('approvalFlow', fn ($q) => $q->where('step_order', $stepOrder))
            ->firstOrFail();
    }

    /** @return list<string> */
    private function pendingStep4RoleNames(int $idPermintaan): array
    {
        return ApprovalLog::query()
            ->where('modul_approval', 'PERMINTAAN_BARANG')
            ->where('id_referensi', $idPermintaan)
            ->where('status', 'MENUNGGU')
            ->whereHas('approvalFlow', fn ($q) => $q->where('step_order', 4))
            ->with('approvalFlow.role')
            ->get()
            ->map(fn ($log) => $log->approvalFlow?->role?->name)
            ->filter()
            ->values()
            ->all();
    }
}
