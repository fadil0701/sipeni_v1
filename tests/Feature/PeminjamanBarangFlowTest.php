<?php

namespace Tests\Feature;

use App\Models\MasterDataBarang;
use App\Models\MasterGudang;
use App\Models\MasterPegawai;
use App\Models\PeminjamanBarang;
use App\Models\User;
use Database\Seeders\ComprehensiveDummySeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeminjamanBarangFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->seed(ComprehensiveDummySeeder::class);
    }

    public function test_admin_can_submit_multi_item_loan_and_complete_cross_unit_flow(): void
    {
        $admin = User::query()->where('email', 'pusdatinppkp@gmail.com')->firstOrFail();
        $payload = $this->buildPayload(PeminjamanBarang::TUJUAN_ANTAR_UNIT_KERJA);

        $submit = $this->actingAs($admin)
            ->post(route('transaction.peminjaman-barang.store'), $payload);
        $submit->assertRedirect(route('transaction.peminjaman-barang.index'));

        $peminjaman = PeminjamanBarang::query()->latest('id_peminjaman')->firstOrFail();
        $this->assertSame(PeminjamanBarang::STATUS_DIAJUKAN, $peminjaman->status);
        $this->assertCount(2, $peminjaman->details);

        $this->actingAs($admin)->post(route('transaction.peminjaman-barang.verifikasi-unit-a', $peminjaman->id_peminjaman))
            ->assertSessionHas('success');
        $peminjaman->refresh();
        $this->assertSame(PeminjamanBarang::STATUS_DIVERIFIKASI_UNIT_A, $peminjaman->status);

        $this->actingAs($admin)->post(route('transaction.peminjaman-barang.approve-pengurus', $peminjaman->id_peminjaman))
            ->assertSessionHas('success');
        $peminjaman->refresh();
        $this->assertSame(PeminjamanBarang::STATUS_MENUNGGU_PERSETUJUAN_UNIT_B, $peminjaman->status);

        $this->actingAs($admin)->post(route('transaction.peminjaman-barang.approve-unit-b', $peminjaman->id_peminjaman))
            ->assertSessionHas('success');
        $peminjaman->refresh();
        $this->assertSame(PeminjamanBarang::STATUS_DISETUJUI_PENGURUS, $peminjaman->status);

        $this->actingAs($admin)->post(route('transaction.peminjaman-barang.mengetahui-kasubag-tu', $peminjaman->id_peminjaman))
            ->assertSessionHas('success');
        $peminjaman->refresh();
        $this->assertSame(PeminjamanBarang::STATUS_DIKETAHUI_KASUBAG_TU, $peminjaman->status);

        $this->actingAs($admin)->post(route('transaction.peminjaman-barang.serah-terima', $peminjaman->id_peminjaman), [
            'kondisi_serah' => 'Baik',
        ])->assertSessionHas('success');
        $peminjaman->refresh();
        $this->assertSame(PeminjamanBarang::STATUS_SERAH_TERIMA, $peminjaman->status);

        $pegawaiPeminjam = MasterPegawai::query()
            ->where('id_unit_kerja', $peminjaman->id_unit_peminjam)
            ->whereNotNull('user_id')
            ->firstOrFail();
        $pegawaiUser = User::query()->findOrFail($pegawaiPeminjam->user_id);
        $itemsPayload = $peminjaman->details->map(function ($detail) {
            return [
                'id_detail_peminjaman' => $detail->id_detail_peminjaman,
                'kondisi_kembali' => 'Baik',
            ];
        })->values()->all();
        $this->actingAs($pegawaiUser)->post(route('transaction.peminjaman-barang.pengembalian', $peminjaman->id_peminjaman), [
            'items' => $itemsPayload,
        ])->assertSessionHas('success');
        $peminjaman->refresh();
        $this->assertSame(PeminjamanBarang::STATUS_PENGEMBALIAN, $peminjaman->status);

        $this->actingAs($admin)->post(route('transaction.peminjaman-barang.selesai', $peminjaman->id_peminjaman))
            ->assertSessionHas('success');
        $peminjaman->refresh();
        $this->assertSame(PeminjamanBarang::STATUS_SELESAI, $peminjaman->status);
    }

    public function test_admin_can_complete_gudang_pusat_flow_without_unit_pemilik_approval(): void
    {
        $admin = User::query()->where('email', 'pusdatinppkp@gmail.com')->firstOrFail();
        $payload = $this->buildPayload(PeminjamanBarang::TUJUAN_GUDANG_PUSAT);

        $this->actingAs($admin)->post(route('transaction.peminjaman-barang.store'), $payload)
            ->assertRedirect(route('transaction.peminjaman-barang.index'));
        $peminjaman = PeminjamanBarang::query()->latest('id_peminjaman')->firstOrFail();

        $this->actingAs($admin)->post(route('transaction.peminjaman-barang.verifikasi-unit-a', $peminjaman->id_peminjaman));
        $peminjaman->refresh();
        $this->assertSame(PeminjamanBarang::STATUS_DIVERIFIKASI_UNIT_A, $peminjaman->status);

        $this->actingAs($admin)->post(route('transaction.peminjaman-barang.approve-pengurus', $peminjaman->id_peminjaman));
        $peminjaman->refresh();
        $this->assertSame(PeminjamanBarang::STATUS_DISETUJUI_PENGURUS, $peminjaman->status);
    }

    private function buildPayload(string $tujuan): array
    {
        $pemohon = MasterPegawai::query()->whereNotNull('id_unit_kerja')->firstOrFail();
        $barang = MasterDataBarang::query()->orderBy('id_data_barang')->take(2)->get();
        $this->assertCount(2, $barang, 'Data barang minimal 2 item untuk uji multi-item.');

        $payload = [
            'id_pemohon_manual' => $pemohon->id,
            'id_unit_peminjam' => $pemohon->id_unit_kerja,
            'tujuan_peminjaman' => $tujuan,
            'tanggal_pinjam' => now()->toDateString(),
            'tanggal_rencana_kembali' => now()->addDays(5)->toDateString(),
            'alasan' => 'Pengujian alur peminjaman multi-item secara end-to-end.',
            'items' => [
                [
                    'id_data_barang' => $barang[0]->id_data_barang,
                    'id_satuan' => $barang[0]->id_satuan,
                    'qty_pinjam' => 1,
                    'keterangan_detail' => 'Item pertama',
                ],
                [
                    'id_data_barang' => $barang[1]->id_data_barang,
                    'id_satuan' => $barang[1]->id_satuan,
                    'qty_pinjam' => 2,
                    'keterangan_detail' => 'Item kedua',
                ],
            ],
        ];

        if ($tujuan === PeminjamanBarang::TUJUAN_ANTAR_UNIT_KERJA) {
            $payload['id_unit_pemilik'] = MasterPegawai::query()
                ->whereNotNull('id_unit_kerja')
                ->where('id_unit_kerja', '!=', $pemohon->id_unit_kerja)
                ->value('id_unit_kerja');
            $this->assertNotNull($payload['id_unit_pemilik'], 'Tidak ada unit pemilik berbeda untuk skenario antar unit.');
        } else {
            $payload['id_gudang_pusat'] = MasterGudang::query()
                ->where('jenis_gudang', 'PUSAT')
                ->value('id_gudang');
            $this->assertNotNull($payload['id_gudang_pusat'], 'Gudang pusat tidak ditemukan untuk skenario gudang pusat.');
        }

        return $payload;
    }
}

