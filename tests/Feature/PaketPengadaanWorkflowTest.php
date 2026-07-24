<?php

namespace Tests\Feature;

use App\Enums\PermintaanBarangStatus;
use App\Models\MasterSubKegiatan;
use App\Models\PengadaanPaket;
use App\Models\PermintaanBarang;
use App\Models\User;
use Database\Seeders\ComprehensiveDummySeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaketPengadaanWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->seed(ComprehensiveDummySeeder::class);
    }

    public function test_process_marks_paket_diproses_and_permintaan_proses_pengadaan(): void
    {
        $admin = User::query()->where('email', 'pusdatinppkp@gmail.com')->firstOrFail();
        [$paket, $permintaan] = $this->createPaketLinkedToPermintaan('DIAJUKAN', PermintaanBarangStatus::MenungguPengadaan);

        $this->actingAs($admin)
            ->post(route('procurement.paket-pengadaan.process', $paket->id_paket))
            ->assertRedirect(route('procurement.paket-pengadaan.show', $paket->id_paket));

        $paket->refresh();
        $permintaan->refresh();

        $this->assertSame('DIPROSES', $paket->status_paket);
        $this->assertSame(PermintaanBarangStatus::ProsesPengadaan, $permintaan->status);
    }

    public function test_mark_barang_tersedia_resumes_permintaan_to_proses_distribusi(): void
    {
        $admin = User::query()->where('email', 'pusdatinppkp@gmail.com')->firstOrFail();
        [$paket, $permintaan] = $this->createPaketLinkedToPermintaan('DIPROSES', PermintaanBarangStatus::ProsesPengadaan);

        $this->actingAs($admin)
            ->post(route('procurement.paket-pengadaan.mark-barang-tersedia', $paket->id_paket))
            ->assertRedirect(route('procurement.paket-pengadaan.show', $paket->id_paket));

        $paket->refresh();
        $permintaan->refresh();

        $this->assertSame('SELESAI', $paket->status_paket);
        $this->assertSame(PermintaanBarangStatus::ProsesDistribusi, $permintaan->status);
    }

    public function test_user_requests_index_redirects_to_transaction_permintaan(): void
    {
        $admin = User::query()->where('email', 'pusdatinppkp@gmail.com')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('user.requests.index'))
            ->assertRedirect(route('transaction.permintaan-barang.index'));
    }

    /**
     * @return array{0: PengadaanPaket, 1: PermintaanBarang}
     */
    private function createPaketLinkedToPermintaan(string $statusPaket, PermintaanBarangStatus $statusPermintaan): array
    {
        $permintaan = PermintaanBarang::factory()->create([
            'status' => $statusPermintaan,
        ]);

        $subKegiatanId = MasterSubKegiatan::query()->value('id_sub_kegiatan');
        $this->assertNotNull($subKegiatanId);

        $paket = PengadaanPaket::query()->create([
            'id_permintaan' => $permintaan->id_permintaan,
            'id_sub_kegiatan' => $subKegiatanId,
            'id_rku' => null,
            'no_paket' => 'TST-PGD/'.now()->format('YmdHis'),
            'nama_paket' => 'Paket uji workflow',
            'deskripsi_paket' => 'Test',
            'metode_pengadaan' => 'PEMILIHAN_LANGSUNG',
            'nilai_paket' => 0,
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => null,
            'status_paket' => $statusPaket,
            'keterangan' => null,
        ]);

        return [$paket, $permintaan];
    }
}
