<?php

namespace App\Services;

use App\Enums\PermintaanBarangStatus;
use App\Models\ApprovalFlowDefinition;
use App\Models\ApprovalLog;
use App\Models\DetailPermintaanBarang;
use App\Models\MasterDataBarang;
use App\Models\MasterPegawai;
use App\Models\PermintaanBarang;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PermintaanService
{
    public function __construct(
        private readonly PermintaanBarangStatusService $statusService
    ) {}

    public function createDraft(array $validated): PermintaanBarang
    {
        return DB::transaction(function () use ($validated): PermintaanBarang {
            $permintaan = PermintaanBarang::create([
                'no_permintaan' => $this->generateNomorPermintaan($validated['tanggal_permintaan']),
                'id_unit_kerja' => $validated['id_unit_kerja'],
                'id_pemohon' => $validated['id_pemohon'],
                'tanggal_permintaan' => $validated['tanggal_permintaan'],
                'tipe_permintaan' => $validated['tipe_permintaan'],
                'jenis_permintaan' => $validated['jenis_permintaan'],
                'status' => PermintaanBarangStatus::Draft,
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            $this->syncItems($permintaan, $validated['detail']);

            return $permintaan;
        });
    }

    public function updateDraft(PermintaanBarang $permintaan, array $validated): PermintaanBarang
    {
        return DB::transaction(function () use ($permintaan, $validated): PermintaanBarang {
            $permintaan->update([
                'id_unit_kerja' => $validated['id_unit_kerja'],
                'id_pemohon' => $validated['id_pemohon'],
                'tanggal_permintaan' => $validated['tanggal_permintaan'],
                'tipe_permintaan' => $validated['tipe_permintaan'],
                'jenis_permintaan' => $validated['jenis_permintaan'],
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            $this->syncItems($permintaan, $validated['detail']);

            return $permintaan->fresh(['detailPermintaan']);
        });
    }

    public function submit(PermintaanBarang $permintaan): void
    {
        DB::transaction(function () use ($permintaan): void {
            $this->statusService->setStatus($permintaan, PermintaanBarangStatus::Diajukan);
            $this->ensureInitialApprovalLog($permintaan->fresh());
        });
    }

    /**
     * Keep permintaan(status=diajukan) and first approval step in sync.
     */
    public function ensureInitialApprovalLog(PermintaanBarang $permintaan): void
    {
        $flowStep2 = ApprovalFlowDefinition::where('modul_approval', 'PERMINTAAN_BARANG')
            ->where('step_order', 2)
            ->first();

        if (! $flowStep2) {
            throw new \RuntimeException('Konfigurasi approval flow tidak ditemukan. Jalankan ApprovalFlowDefinitionSeeder.');
        }

        ApprovalLog::updateOrCreate(
            [
                'modul_approval' => 'PERMINTAAN_BARANG',
                'id_referensi' => $permintaan->id_permintaan,
                'id_approval_flow' => $flowStep2->id,
            ],
            [
                'user_id' => null,
                'role_id' => $flowStep2->role_id,
                'status' => 'MENUNGGU',
                'catatan' => null,
                'approved_at' => null,
            ]
        );
    }

    public function deleteDraft(PermintaanBarang $permintaan): void
    {
        DB::transaction(function () use ($permintaan): void {
            $permintaan->detailPermintaan()->delete();
            $permintaan->delete();
        });
    }

    public function createAndSubmitFromUser(int $userId, array $validated): PermintaanBarang
    {
        $pegawai = MasterPegawai::where('user_id', $userId)->firstOrFail();
        $barang = MasterDataBarang::findOrFail($validated['id_data_barang']);

        $payload = [
            'id_unit_kerja' => $pegawai->id_unit_kerja,
            'id_pemohon' => $pegawai->id,
            'tanggal_permintaan' => now()->toDateString(),
            'tipe_permintaan' => 'RUTIN',
            'jenis_permintaan' => ['PERSEDIAAN'],
            'keterangan' => $validated['keterangan'] ?? null,
            'detail' => [[
                'id_data_barang' => $validated['id_data_barang'],
                'deskripsi_barang' => null,
                'qty_diminta' => (float) $validated['qty_permintaan'],
                'id_satuan' => $barang->id_satuan,
                'keterangan' => $validated['keterangan'] ?? null,
            ]],
        ];

        $permintaan = $this->createDraft($payload);
        $this->submit($permintaan->fresh());

        return $permintaan->fresh(['detailPermintaan.dataBarang', 'pemohon']);
    }

    private function syncItems(PermintaanBarang $permintaan, array $details): void
    {
        $permintaan->detailPermintaan()->delete();
        foreach ($details as $detail) {
            DetailPermintaanBarang::create([
                'id_permintaan' => $permintaan->id_permintaan,
                'id_data_barang' => $detail['id_data_barang'] ?? null,
                'deskripsi_barang' => $detail['deskripsi_barang'] ?? null,
                'qty_diminta' => $detail['qty_diminta'],
                'qty_diminta_awal' => $detail['qty_diminta'],
                'qty_disetujui' => $detail['qty_diminta'],
                'id_satuan' => $detail['id_satuan'],
                'keterangan' => $detail['keterangan'] ?? null,
            ]);
        }
    }

    private function generateNomorPermintaan(string $tanggalPermintaan): string
    {
        $tahun = Carbon::parse($tanggalPermintaan)->format('Y');
        $last = PermintaanBarang::whereYear('tanggal_permintaan', $tahun)
            ->orderBy('no_permintaan', 'desc')
            ->first();

        $urut = 1;
        if ($last) {
            $parts = explode('/', $last->no_permintaan);
            $urut = (int) end($parts) + 1;
        }

        return sprintf('PMT/%s/%04d', $tahun, $urut);
    }
}
