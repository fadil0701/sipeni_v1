<?php

namespace App\Services;

use App\Enums\PermintaanBarangStatus;
use App\Models\MasterSubKegiatan;
use App\Models\PengadaanPaket;
use App\Models\PermintaanBarang;
use Illuminate\Support\Facades\DB;

class PengadaanService
{
    public function __construct(
        private readonly PermintaanBarangStatusService $permintaanStatusService
    ) {}

    public function createProcurement(PermintaanBarang $permintaan, ?string $catatan = null): PengadaanPaket
    {
        return DB::transaction(function () use ($permintaan, $catatan): PengadaanPaket {
            $existing = PengadaanPaket::where('id_permintaan', $permintaan->id_permintaan)
                ->whereIn('status_paket', ['DRAFT', 'DIAJUKAN', 'DIPROSES'])
                ->latest('id_paket')
                ->first();

            if ($existing) {
                $this->permintaanStatusService->setStatus($permintaan, PermintaanBarangStatus::MenungguPengadaan);
                return $existing;
            }

            $subKegiatanId = MasterSubKegiatan::query()->value('id_sub_kegiatan');
            if (! $subKegiatanId) {
                throw new \RuntimeException('Sub kegiatan belum tersedia untuk membuat paket pengadaan otomatis.');
            }

            $today = now()->toDateString();
            $nomor = sprintf('AUTO-PGD/%s/%04d', now()->format('Ymd'), ((int) PengadaanPaket::max('id_paket')) + 1);

            $paket = PengadaanPaket::create([
                'id_permintaan' => $permintaan->id_permintaan,
                'id_sub_kegiatan' => $subKegiatanId,
                'id_rku' => null,
                'no_paket' => $nomor,
                'nama_paket' => 'Pengadaan untuk ' . $permintaan->no_permintaan,
                'deskripsi_paket' => $catatan ?? ('Pengadaan otomatis dari permintaan ' . $permintaan->no_permintaan),
                'metode_pengadaan' => 'PEMILIHAN_LANGSUNG',
                'nilai_paket' => 0,
                'tanggal_mulai' => $today,
                'tanggal_selesai' => null,
                'status_paket' => 'DIAJUKAN',
                'keterangan' => $catatan,
            ]);

            $this->permintaanStatusService->setStatus($permintaan, PermintaanBarangStatus::MenungguPengadaan);
            return $paket;
        });
    }

    public function processProcurement(PengadaanPaket $paket): void
    {
        DB::transaction(function () use ($paket): void {
            if ($paket->status_paket !== 'DIPROSES') {
                $paket->update(['status_paket' => 'DIPROSES']);
            }
            if ($paket->permintaan) {
                $this->permintaanStatusService->setStatus($paket->permintaan, PermintaanBarangStatus::ProsesPengadaan);
            }
        });
    }

    public function markBarangTersedia(PengadaanPaket $paket): void
    {
        DB::transaction(function () use ($paket): void {
            if ($paket->status_paket !== 'SELESAI') {
                $paket->update(['status_paket' => 'SELESAI']);
            }
            if ($paket->permintaan) {
                $this->permintaanStatusService->setStatus($paket->permintaan, PermintaanBarangStatus::BarangTersedia);
            }
        });
    }
}
