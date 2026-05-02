<?php

namespace Tests\Feature;

use App\Models\DataInventory;
use App\Models\DataStock;
use App\Models\MasterGudang;
use App\Models\MasterPegawai;
use App\Models\ReturBarang;
use App\Models\User;
use Database\Seeders\ComprehensiveDummySeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReturBarangFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->seed(ComprehensiveDummySeeder::class);
    }

    public function test_guest_is_redirected_from_retur_index(): void
    {
        $this->get(route('transaction.retur-barang.index'))
            ->assertRedirect();
    }

    public function test_admin_can_create_retur_from_unit_inventory_and_receive_it(): void
    {
        $admin = User::query()->where('email', 'pusdatinppkp@gmail.com')->firstOrFail();

        // Dummy seeder menyimpan inventory persediaan di gudang PUSAT; untuk retur diperlukan baris inventory di gudang UNIT.
        $templateInv = DataInventory::query()
            ->where('jenis_inventory', 'PERSEDIAAN')
            ->where('status_inventory', 'AKTIF')
            ->where('auto_qr_code', 'INV-DMY-001')
            ->firstOrFail();

        $gudangAsal = MasterGudang::query()
            ->where('jenis_gudang', 'UNIT')
            ->whereNotNull('id_unit_kerja')
            ->orderBy('id_gudang')
            ->firstOrFail();

        $inventory = $templateInv->replicate();
        $inventory->id_gudang = $gudangAsal->id_gudang;
        $inventory->qty_input = 10;
        $inventory->total_harga = (float) $templateInv->harga_satuan * 10;
        $inventory->auto_qr_code = 'INV-TEST-RETUR-'.uniqid('', true);
        $inventory->save();

        $inventory->load('gudang');
        $unitId = (int) $gudangAsal->id_unit_kerja;

        DataStock::query()->updateOrInsert(
            ['id_data_barang' => $inventory->id_data_barang, 'id_gudang' => $gudangAsal->id_gudang],
            [
                'qty_awal' => 0,
                'qty_masuk' => (float) $inventory->qty_input,
                'qty_keluar' => 0,
                'qty_akhir' => (float) $inventory->qty_input,
                'id_satuan' => $inventory->id_satuan,
                'last_updated' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $gudangTujuan = MasterGudang::query()
            ->where('jenis_gudang', 'PUSAT')
            ->where('kategori_gudang', 'PERSEDIAAN')
            ->orderBy('id_gudang')
            ->firstOrFail();

        $pegawai = MasterPegawai::query()
            ->where('id_unit_kerja', $unitId)
            ->firstOrFail();

        $payload = [
            'tanggal_retur' => now()->toDateString(),
            'id_unit_kerja' => $unitId,
            'id_gudang_asal' => $gudangAsal->id_gudang,
            'id_gudang_tujuan' => $gudangTujuan->id_gudang,
            'id_pegawai_pengirim' => $pegawai->id,
            'status_retur' => 'DIAJUKAN',
            'jenis_retur' => 'SISA',
            'alasan_retur' => 'Uji otomatis retur',
            'detail' => [
                [
                    'id_inventory' => $inventory->id_inventory,
                    'qty_retur' => 1,
                    'id_satuan' => $inventory->id_satuan,
                    'alasan_retur_item' => null,
                ],
            ],
        ];

        $this->actingAs($admin)
            ->post(route('transaction.retur-barang.store'), $payload)
            ->assertRedirect(route('transaction.retur-barang.index'))
            ->assertSessionHas('success');

        $retur = ReturBarang::query()->latest('id_retur')->firstOrFail();
        $this->assertSame('DIAJUKAN', $retur->status_retur);
        $this->assertCount(1, $retur->detailRetur);

        $this->actingAs($admin)
            ->post(route('transaction.retur-barang.terima', $retur->id_retur))
            ->assertRedirect(route('transaction.retur-barang.show', $retur->id_retur))
            ->assertSessionHas('success');

        $retur->refresh();
        $this->assertSame('DITERIMA', $retur->status_retur);
    }
}
