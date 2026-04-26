<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\RegisterAset;
use App\Models\User;
use Database\Seeders\ComprehensiveDummySeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RegisterAsetKirFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        $this->seed(ComprehensiveDummySeeder::class);
    }

    public function test_store_register_aset_redirects_to_kir_create_and_uses_distinct_nomor_register(): void
    {
        $admin = User::query()->where('email', 'pusdatinppkp@gmail.com')->firstOrFail();

        $existingAssetInventory = DB::table('data_inventory')
            ->where('jenis_inventory', 'ASET')
            ->orderBy('id_inventory')
            ->first();
        $this->assertNotNull($existingAssetInventory);

        $newItemId = DB::table('inventory_item')->insertGetId([
            'id_inventory' => $existingAssetInventory->id_inventory,
            'kode_register' => 'ITM-TEST-' . now()->format('YmdHis'),
            'no_seri' => 'NS-TEST-' . now()->format('His'),
            'kondisi_item' => 'BAIK',
            'status_item' => 'AKTIF',
            'id_gudang' => $existingAssetInventory->id_gudang,
            'id_ruangan' => null,
            'qr_code' => 'QR-TEST-' . now()->format('His'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $unitKerjaId = DB::table('master_unit_kerja')->orderBy('id_unit_kerja')->value('id_unit_kerja');
        $this->assertNotNull($unitKerjaId);

        $response = $this->actingAs($admin)->post(route('asset.register-aset.store'), [
            'id_item' => $newItemId,
            'id_unit_kerja' => $unitKerjaId,
            'kondisi_aset' => 'BAIK',
            'status_aset' => 'AKTIF',
            'tanggal_perolehan' => now()->toDateString(),
        ]);

        $register = RegisterAset::query()->where('id_item', $newItemId)->firstOrFail();
        $inventoryItem = InventoryItem::query()->findOrFail($newItemId);

        $response->assertRedirect(route('asset.kartu-inventaris-ruangan.create', [
            'id_register_aset' => $register->id_register_aset,
        ]));
        $this->assertNotEquals($inventoryItem->kode_register, $register->nomor_register);
        $this->assertNull($register->id_ruangan);
    }
}

