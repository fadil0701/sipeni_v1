<?php

namespace Tests\Feature;

use App\Enums\DistribusiStatus;
use App\Enums\PermintaanBarangStatus;
use App\Models\ApprovalLog;
use App\Models\PenerimaanBarang;
use App\Models\PermintaanBarang;
use App\Models\TransaksiDistribusi;
use App\Models\User;
use Database\Seeders\ComprehensiveDummySeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PermintaanBarangEndToEndTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->seed(ComprehensiveDummySeeder::class);
        $this->ensurePusatPersediaanStockForDummyBarang();
    }

    private function ensurePusatPersediaanStockForDummyBarang(): void
    {
        $barangId = (int) DB::table('master_data_barang')->where('kode_data_barang', 'BRG-DMY-001')->value('id_data_barang');
        $gudangPusatId = (int) DB::table('master_gudang')
            ->where('jenis_gudang', 'PUSAT')
            ->where('kategori_gudang', 'PERSEDIAAN')
            ->value('id_gudang');
        $satuanId = (int) DB::table('master_satuan')->value('id_satuan');

        if ($barangId <= 0 || $gudangPusatId <= 0) {
            return;
        }

        $now = now();
        DB::table('data_stock')->updateOrInsert(
            ['id_data_barang' => $barangId, 'id_gudang' => $gudangPusatId],
            [
                'qty_awal' => 100,
                'qty_masuk' => 0,
                'qty_keluar' => 0,
                'qty_akhir' => 100,
                'id_satuan' => $satuanId,
                'last_updated' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    public function test_super_admin_can_create_and_submit_permintaan(): void
    {
        $admin = User::query()->where('email', 'pusdatinppkp@gmail.com')->firstOrFail();
        $permintaan = $this->createDraftPermintaan($admin);

        $this->actingAs($admin)
            ->post(route('transaction.permintaan-barang.ajukan', $permintaan->id_permintaan))
            ->assertRedirect();

        $permintaan->refresh();
        $this->assertSame(PermintaanBarangStatus::Diajukan, $permintaan->status);
    }

    public function test_full_flow_from_permintaan_to_penerimaan(): void
    {
        $admin = User::query()->where('email', 'pusdatinppkp@gmail.com')->firstOrFail();
        $permintaan = $this->createDraftPermintaan($admin);

        $this->actingAs($admin)
            ->post(route('transaction.permintaan-barang.ajukan', $permintaan->id_permintaan))
            ->assertRedirect();

        $step2Log = ApprovalLog::query()
            ->where('modul_approval', 'PERMINTAAN_BARANG')
            ->where('id_referensi', $permintaan->id_permintaan)
            ->where('status', 'MENUNGGU')
            ->whereHas('approvalFlow', fn ($q) => $q->where('step_order', 2))
            ->firstOrFail();

        $this->actingAs($admin)
            ->post(route('transaction.approval.mengetahui', $step2Log->id))
            ->assertRedirect();

        $step3Log = ApprovalLog::query()
            ->where('modul_approval', 'PERMINTAAN_BARANG')
            ->where('id_referensi', $permintaan->id_permintaan)
            ->where('status', 'MENUNGGU')
            ->whereHas('approvalFlow', fn ($q) => $q->where('step_order', 3))
            ->firstOrFail();

        $this->actingAs($admin)
            ->post(route('transaction.approval.verifikasi', $step3Log->id))
            ->assertRedirect();

        $permintaan->refresh();
        $this->assertSame(PermintaanBarangStatus::ProsesDistribusi, $permintaan->status);

        $inventoryId = (int) DB::table('data_inventory')->where('auto_qr_code', 'INV-DMY-001')->value('id_inventory');
        $gudangPusatId = (int) DB::table('master_gudang')
            ->where('jenis_gudang', 'PUSAT')
            ->where('kategori_gudang', 'PERSEDIAAN')
            ->value('id_gudang');
        $gudangUnitId = (int) DB::table('master_gudang')->where('nama_gudang', 'Gudang Unit A Dummy')->value('id_gudang');
        $pegawaiId = (int) DB::table('master_pegawai')->value('id');
        $satuanId = (int) DB::table('master_satuan')->value('id_satuan');

        $this->actingAs($admin)->post(route('transaction.distribusi.store'), [
            'id_permintaan' => $permintaan->id_permintaan,
            'tanggal_distribusi' => now()->toDateString(),
            'id_gudang_asal' => $gudangPusatId,
            'id_gudang_tujuan' => $gudangUnitId,
            'id_pegawai_pengirim' => $pegawaiId,
            'keterangan' => 'E2E distribusi',
            'detail' => [
                [
                    'id_inventory' => $inventoryId,
                    'qty_distribusi' => 1,
                    'id_satuan' => $satuanId,
                    'harga_satuan' => 25000,
                ],
            ],
        ])->assertRedirect(route('transaction.distribusi.index'));

        $distribusi = TransaksiDistribusi::query()->latest('id_distribusi')->first();
        $this->assertNotNull($distribusi);
        $this->assertSame(DistribusiStatus::Draft, $distribusi->status_distribusi);

        $this->actingAs($admin)
            ->post(route('transaction.distribusi.kirim', $distribusi->id_distribusi))
            ->assertRedirect();

        $distribusi->refresh();
        $this->assertSame(DistribusiStatus::Selesai, $distribusi->status_distribusi);

        $penerimaan = PenerimaanBarang::query()
            ->where('id_distribusi', $distribusi->id_distribusi)
            ->first();

        $this->assertNotNull($penerimaan);
        $this->assertSame('MENUNGGU_VERIFIKASI', $penerimaan->status_penerimaan);
    }

    private function createDraftPermintaan(User $admin): PermintaanBarang
    {
        $unitId = (int) DB::table('master_unit_kerja')->where('nama_unit_kerja', 'Unit Kerja A Dummy')->value('id_unit_kerja')
            ?: (int) DB::table('master_unit_kerja')->value('id_unit_kerja');
        $pegawaiId = (int) DB::table('master_pegawai')->where('id_unit_kerja', $unitId)->value('id');
        $barangId = (int) DB::table('master_data_barang')->where('kode_data_barang', 'BRG-DMY-001')->value('id_data_barang')
            ?: (int) DB::table('master_data_barang')->value('id_data_barang');
        $satuanId = (int) DB::table('master_satuan')->value('id_satuan');

        $this->actingAs($admin)->post(route('transaction.permintaan-barang.store'), [
            'id_unit_kerja' => $unitId,
            'id_pemohon' => $pegawaiId,
            'tanggal_permintaan' => now()->toDateString(),
            'tipe_permintaan' => 'RUTIN',
            'jenis_permintaan' => ['PERSEDIAAN'],
            'keterangan' => 'E2E test permintaan',
            'detail' => [
                [
                    'id_data_barang' => $barangId,
                    'qty_diminta' => 1,
                    'id_satuan' => $satuanId,
                ],
            ],
        ])->assertRedirect(route('transaction.permintaan-barang.index'));

        $permintaan = PermintaanBarang::query()->latest('id_permintaan')->first();
        $this->assertNotNull($permintaan);
        $this->assertSame(PermintaanBarangStatus::Draft, $permintaan->status);

        return $permintaan;
    }
}
