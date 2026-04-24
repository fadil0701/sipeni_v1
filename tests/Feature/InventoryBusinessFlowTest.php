<?php

namespace Tests\Feature;

use App\Enums\DistribusiStatus;
use App\Models\DataInventory;
use App\Models\DataStock;
use App\Models\DetailDistribusi;
use App\Models\MasterSatuan;
use App\Models\PermintaanBarang;
use App\Models\TransaksiDistribusi;
use App\Services\DistribusiService;
use Database\Seeders\ComprehensiveDummySeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class InventoryBusinessFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        $this->seed(ComprehensiveDummySeeder::class);
    }

    public function test_distribusi_kirim_ditolak_ketika_qty_melebihi_stok_inventory(): void
    {
        $inventory = DataInventory::query()
            ->where('jenis_inventory', 'PERSEDIAAN')
            ->where('status_inventory', 'AKTIF')
            ->firstOrFail();

        $permintaan = PermintaanBarang::query()->firstOrFail();
        $satuan = MasterSatuan::query()->firstOrFail();

        $distribusi = TransaksiDistribusi::create([
            'no_sbbk' => 'SBBK-TEST-OVERSTOCK',
            'id_permintaan' => $permintaan->id_permintaan,
            'tanggal_distribusi' => now(),
            'id_gudang_asal' => $inventory->id_gudang,
            'id_gudang_tujuan' => $inventory->id_gudang,
            'id_pegawai_pengirim' => \App\Models\MasterPegawai::query()->firstOrFail()->id,
            'status_distribusi' => DistribusiStatus::Draft,
            'keterangan' => 'Test stok tidak cukup',
        ]);

        DetailDistribusi::create([
            'id_distribusi' => $distribusi->id_distribusi,
            'id_inventory' => $inventory->id_inventory,
            'qty_distribusi' => (float) $inventory->qty_input + 1,
            'id_satuan' => $satuan->id_satuan,
            'harga_satuan' => 1,
            'subtotal' => 1,
            'keterangan' => 'Over qty',
        ]);

        $service = app(DistribusiService::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stok inventory tidak cukup');
        $service->kirim($distribusi->fresh('detailDistribusi.inventory'));
    }

    public function test_reconcile_stock_fix_menyamakan_qty_akhir_dengan_agregasi_inventory(): void
    {
        $stock = DataStock::query()->firstOrFail();

        $expectedQty = (float) DataInventory::query()
            ->where('id_data_barang', $stock->id_data_barang)
            ->where('id_gudang', $stock->id_gudang)
            ->whereIn('jenis_inventory', ['PERSEDIAAN', 'FARMASI'])
            ->where('status_inventory', '!=', 'HABIS')
            ->sum('qty_input');

        DataStock::query()
            ->whereKey($stock->id_stock)
            ->update(['qty_akhir' => $expectedQty + 10]);

        $this->artisan('inventory:reconcile-stock --fix')
            ->assertExitCode(0);

        $updated = DataStock::query()->findOrFail($stock->id_stock);
        $this->assertEquals((float) $expectedQty, (float) $updated->qty_akhir);
    }
}
