<?php

namespace Tests\Feature;

use App\Models\JadwalMaintenance;
use App\Models\KartuInventarisRuangan;
use App\Models\KalibrasiAset;
use App\Models\MasterPegawai;
use App\Models\PermintaanPemeliharaan;
use App\Models\RegisterAset;
use App\Models\ServiceReport;
use App\Models\User;
use Database\Seeders\ComprehensiveDummySeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MaintenanceFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        $this->seed(ComprehensiveDummySeeder::class);
    }

    public function test_admin_can_generate_routine_request_from_active_schedule(): void
    {
        $admin = User::query()->where('email', 'pusdatinppkp@gmail.com')->firstOrFail();
        $register = $this->findRegisterWithKirAndPemohon();

        $jadwal = JadwalMaintenance::query()->create([
            'id_register_aset' => $register->id_register_aset,
            'jenis_maintenance' => 'RUTIN',
            'periode' => 'BULANAN',
            'interval_hari' => null,
            'tanggal_mulai' => now()->subMonth()->toDateString(),
            'tanggal_selanjutnya' => now()->subDay()->toDateString(),
            'status' => 'AKTIF',
            'keterangan' => 'Jadwal otomatis test',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)
            ->post(route('maintenance.jadwal-maintenance.generate-permintaan', ['id' => $jadwal->id_jadwal]));

        $jadwal->refresh();
        $permintaan = PermintaanPemeliharaan::query()
            ->where('id_register_aset', $register->id_register_aset)
            ->latest('id_permintaan_pemeliharaan')
            ->first();

        $this->assertNotNull($permintaan);
        $this->assertSame('DISETUJUI', $permintaan->status_permintaan);
        $this->assertSame('RUTIN', $permintaan->jenis_pemeliharaan);
        $this->assertNotNull($jadwal->tanggal_terakhir);
        $this->assertNotNull($jadwal->tanggal_selanjutnya);
        $response->assertRedirect(route('maintenance.service-report.create', [
            'permintaan_id' => $permintaan->id_permintaan_pemeliharaan,
        ]));
    }

    public function test_service_report_completion_updates_asset_and_creates_history(): void
    {
        $admin = User::query()->where('email', 'pusdatinppkp@gmail.com')->firstOrFail();
        $register = $this->findRegisterWithKirAndPemohon();
        $pemohon = $this->findPemohonByUnit($register->id_unit_kerja);

        $permintaan = PermintaanPemeliharaan::query()->create([
            'no_permintaan_pemeliharaan' => 'PMH/' . now()->year . '/9001',
            'id_register_aset' => $register->id_register_aset,
            'id_unit_kerja' => $register->id_unit_kerja,
            'id_pemohon' => $pemohon->id,
            'tanggal_permintaan' => now()->toDateString(),
            'jenis_pemeliharaan' => 'PERBAIKAN',
            'prioritas' => 'SEDANG',
            'status_permintaan' => 'DISETUJUI',
            'deskripsi_kerusakan' => 'Tes perbaikan',
            'keterangan' => null,
        ]);

        $createResponse = $this->actingAs($admin)->post(route('maintenance.service-report.store'), [
            'id_permintaan_pemeliharaan' => $permintaan->id_permintaan_pemeliharaan,
            'tanggal_service' => now()->toDateString(),
            'tanggal_selesai' => now()->toDateString(),
            'jenis_service' => 'PERBAIKAN',
            'kondisi_setelah_service' => 'BAIK',
            'biaya_service' => 100000,
            'biaya_sparepart' => 50000,
            'keterangan' => 'Selesai perbaikan',
        ]);
        $createResponse->assertRedirect(route('maintenance.service-report.index'));

        $service = ServiceReport::query()
            ->where('id_permintaan_pemeliharaan', $permintaan->id_permintaan_pemeliharaan)
            ->firstOrFail();

        $updateResponse = $this->actingAs($admin)->put(route('maintenance.service-report.update', $service->id_service_report), [
            'tanggal_service' => now()->toDateString(),
            'tanggal_selesai' => now()->toDateString(),
            'jenis_service' => 'PERBAIKAN',
            'status_service' => 'SELESAI',
            'kondisi_setelah_service' => 'BAIK',
            'vendor' => 'Vendor Test',
            'teknisi' => 'Teknisi Test',
            'biaya_service' => 100000,
            'biaya_sparepart' => 50000,
            'keterangan' => 'Final selesai',
        ]);
        $updateResponse->assertRedirect(route('maintenance.service-report.index'));

        $permintaan->refresh();
        $register->refresh();

        $this->assertSame('SELESAI', $permintaan->status_permintaan);
        $this->assertSame('BAIK', $register->kondisi_aset);
        $this->assertDatabaseHas('riwayat_pemeliharaan', [
            'id_service_report' => $service->id_service_report,
            'id_register_aset' => $register->id_register_aset,
            'status' => 'SELESAI',
        ]);
    }

    public function test_admin_cannot_generate_routine_request_if_asset_not_placed_in_kir(): void
    {
        $admin = User::query()->where('email', 'pusdatinppkp@gmail.com')->firstOrFail();
        $register = $this->findRegisterWithKirAndPemohon();

        KartuInventarisRuangan::query()
            ->where('id_register_aset', $register->id_register_aset)
            ->delete();

        $jadwal = JadwalMaintenance::query()->create([
            'id_register_aset' => $register->id_register_aset,
            'jenis_maintenance' => 'RUTIN',
            'periode' => 'BULANAN',
            'tanggal_mulai' => now()->subMonth()->toDateString(),
            'tanggal_selanjutnya' => now()->subDay()->toDateString(),
            'status' => 'AKTIF',
            'keterangan' => 'Jadwal rutin tanpa KIR',
            'created_by' => $admin->id,
        ]);

        $beforeCount = PermintaanPemeliharaan::query()->count();

        $response = $this->actingAs($admin)
            ->from(route('maintenance.jadwal-maintenance.index'))
            ->post(route('maintenance.jadwal-maintenance.generate-permintaan', ['id' => $jadwal->id_jadwal]));

        $response->assertRedirect(route('maintenance.jadwal-maintenance.index'));
        $response->assertSessionHas('error');
        $this->assertSame($beforeCount, PermintaanPemeliharaan::query()->count());
    }

    public function test_maintenance_routes_require_authentication_and_reject_user_without_role(): void
    {
        $guestResponse = $this->get(route('maintenance.permintaan-pemeliharaan.index'));
        $guestResponse->assertRedirect(route('login'));

        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('maintenance.permintaan-pemeliharaan.index'));
        $response->assertForbidden();
    }

    public function test_admin_can_access_maintenance_summary_report(): void
    {
        $admin = User::query()->where('email', 'pusdatinppkp@gmail.com')->firstOrFail();

        $response = $this->actingAs($admin)->get(route('reports.maintenance-summary'));

        $response->assertOk();
        $response->assertSee('Rekap Pemeliharaan per Unit');
    }

    public function test_admin_can_export_maintenance_summary_csv(): void
    {
        $admin = User::query()->where('email', 'pusdatinppkp@gmail.com')->firstOrFail();

        $response = $this->actingAs($admin)->get(route('reports.maintenance-summary.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition');
        $this->assertStringContainsString(
            'rekap-maintenance-',
            (string) $response->headers->get('Content-Disposition')
        );
    }

    public function test_valid_kalibrasi_closes_request_and_creates_history(): void
    {
        $admin = User::query()->where('email', 'pusdatinppkp@gmail.com')->firstOrFail();
        $register = $this->findRegisterWithKirAndPemohon();
        $pemohon = $this->findPemohonByUnit($register->id_unit_kerja);

        $permintaan = PermintaanPemeliharaan::query()->create([
            'no_permintaan_pemeliharaan' => 'PMH/' . now()->year . '/9101',
            'id_register_aset' => $register->id_register_aset,
            'id_unit_kerja' => $register->id_unit_kerja,
            'id_pemohon' => $pemohon->id,
            'tanggal_permintaan' => now()->toDateString(),
            'jenis_pemeliharaan' => 'KALIBRASI',
            'prioritas' => 'SEDANG',
            'status_permintaan' => 'DISETUJUI',
            'deskripsi_kerusakan' => 'Kalibrasi berkala',
            'keterangan' => null,
        ]);

        $response = $this->actingAs($admin)->post(route('maintenance.kalibrasi-aset.store'), [
            'id_register_aset' => $register->id_register_aset,
            'id_permintaan_pemeliharaan' => $permintaan->id_permintaan_pemeliharaan,
            'tanggal_kalibrasi' => now()->toDateString(),
            'tanggal_berlaku' => now()->toDateString(),
            'tanggal_kadaluarsa' => now()->addYear()->toDateString(),
            'lembaga_kalibrasi' => 'Lab Kalibrasi Test',
            'no_sertifikat' => 'CERT-TEST-001',
            'biaya_kalibrasi' => 250000,
            'keterangan' => 'Kalibrasi valid',
        ]);
        $response->assertRedirect(route('maintenance.kalibrasi-aset.index'));

        $kalibrasi = KalibrasiAset::query()
            ->where('id_permintaan_pemeliharaan', $permintaan->id_permintaan_pemeliharaan)
            ->firstOrFail();

        $permintaan->refresh();
        $this->assertSame('SELESAI', $permintaan->status_permintaan);
        $this->assertDatabaseHas('riwayat_pemeliharaan', [
            'id_kalibrasi' => $kalibrasi->id_kalibrasi,
            'id_register_aset' => $register->id_register_aset,
            'status' => 'SELESAI',
            'jenis_pemeliharaan' => 'KALIBRASI',
        ]);
    }

    public function test_non_valid_kalibrasi_update_does_not_create_additional_history(): void
    {
        $admin = User::query()->where('email', 'pusdatinppkp@gmail.com')->firstOrFail();
        $register = $this->findRegisterWithKirAndPemohon();

        $response = $this->actingAs($admin)->post(route('maintenance.kalibrasi-aset.store'), [
            'id_register_aset' => $register->id_register_aset,
            'tanggal_kalibrasi' => now()->toDateString(),
            'tanggal_berlaku' => now()->toDateString(),
            'tanggal_kadaluarsa' => now()->addMonths(6)->toDateString(),
            'lembaga_kalibrasi' => 'Lab Test',
            'no_sertifikat' => 'CERT-TEST-002',
            'biaya_kalibrasi' => 100000,
            'keterangan' => 'Akan diubah jadi menunggu',
        ]);
        $response->assertRedirect(route('maintenance.kalibrasi-aset.index'));

        $kalibrasi = KalibrasiAset::query()->latest('id_kalibrasi')->firstOrFail();
        $this->assertSame('VALID', $kalibrasi->status_kalibrasi);
        $historyCountBefore = DB::table('riwayat_pemeliharaan')
            ->where('id_kalibrasi', $kalibrasi->id_kalibrasi)
            ->count();

        $update = $this->actingAs($admin)->put(route('maintenance.kalibrasi-aset.update', $kalibrasi->id_kalibrasi), [
            'id_register_aset' => $kalibrasi->id_register_aset,
            'id_permintaan_pemeliharaan' => $kalibrasi->id_permintaan_pemeliharaan,
            'tanggal_kalibrasi' => $kalibrasi->tanggal_kalibrasi->format('Y-m-d'),
            'tanggal_berlaku' => $kalibrasi->tanggal_berlaku->format('Y-m-d'),
            'tanggal_kadaluarsa' => $kalibrasi->tanggal_kadaluarsa->format('Y-m-d'),
            'lembaga_kalibrasi' => $kalibrasi->lembaga_kalibrasi,
            'no_sertifikat' => $kalibrasi->no_sertifikat,
            'status_kalibrasi' => 'MENUNGGU',
            'biaya_kalibrasi' => $kalibrasi->biaya_kalibrasi,
            'keterangan' => 'Belum valid',
        ]);
        $update->assertRedirect(route('maintenance.kalibrasi-aset.index'));

        $historyCountAfter = DB::table('riwayat_pemeliharaan')
            ->where('id_kalibrasi', $kalibrasi->id_kalibrasi)
            ->count();

        $this->assertSame($historyCountBefore, $historyCountAfter);
    }

    private function findRegisterWithKirAndPemohon(): RegisterAset
    {
        $register = RegisterAset::query()
            ->where('status_aset', 'AKTIF')
            ->whereHas('kartuInventarisRuangan')
            ->whereNotNull('id_unit_kerja')
            ->firstOrFail();

        $pemohonExists = MasterPegawai::query()
            ->where('id_unit_kerja', $register->id_unit_kerja)
            ->exists();

        if (!$pemohonExists) {
            $this->fail('Tidak ditemukan pemohon pada unit kerja register aset untuk pengujian maintenance.');
        }

        return $register;
    }

    private function findPemohonByUnit(int $unitKerjaId): MasterPegawai
    {
        return MasterPegawai::query()
            ->where('id_unit_kerja', $unitKerjaId)
            ->orderBy('id')
            ->firstOrFail();
    }
}

