<?php

namespace App\Services;

use App\Enums\PermintaanBarangStatus;
use App\Models\ApprovalLog;
use App\Models\PermintaanBarang;
use App\Models\TransaksiDistribusi;

/**
 * Single entry point for persisting permintaan_barang.status changes.
 */
class PermintaanBarangStatusService
{
    public function setStatus(PermintaanBarang $permintaan, PermintaanBarangStatus $status): void
    {
        if ($permintaan->status === $status) {
            return;
        }

        $permintaan->status = $status;
        $permintaan->save();
    }

    /**
     * After distribusi status becomes SELESAI (penerimaan), align permintaan:
     * — if all distribusi for this permintaan are SELESAI → selesai
     * — otherwise → diterima (partial / in progress)
     */
    public function syncAfterDistribusiSelesai(TransaksiDistribusi $distribusi): void
    {
        $distribusi->loadMissing(['permintaan', 'gudangAsal']);
        $permintaan = $distribusi->permintaan;
        if (! $permintaan instanceof PermintaanBarang) {
            return;
        }

        $idPermintaan = $permintaan->id_permintaan;
        $hasOpen = TransaksiDistribusi::where('id_permintaan', $idPermintaan)
            ->where('status_distribusi', '!=', 'selesai')
            ->exists();

        $target = $hasOpen ? PermintaanBarangStatus::Diterima : PermintaanBarangStatus::Selesai;
        $this->setStatus($permintaan->fresh(), $target);

        $this->markDisposisiApprovalCompleted($distribusi);
    }

    /**
     * Tandai approval step disposisi (step 4) sebagai SELESAI setelah penerimaan OK.
     */
    public function markDisposisiApprovalCompleted(TransaksiDistribusi $distribusi): void
    {
        if (! $distribusi->id_permintaan) {
            return;
        }

        $distribusi->loadMissing('gudangAsal');

        $roleByKategori = [
            'ASET' => 'admin_gudang_aset',
            'PERSEDIAAN' => 'admin_gudang_persediaan',
            'FARMASI' => 'admin_gudang_farmasi',
        ];
        $roleName = $roleByKategori[strtoupper((string) ($distribusi->gudangAsal?->kategori_gudang ?? ''))] ?? null;

        $query = ApprovalLog::query()
            ->where('modul_approval', 'PERMINTAAN_BARANG')
            ->where('id_referensi', $distribusi->id_permintaan)
            ->where('status', 'DIPROSES')
            ->whereHas('approvalFlow', fn ($q) => $q->where('step_order', 4));

        if ($roleName) {
            $query->whereHas('approvalFlow.role', fn ($q) => $q->where('name', $roleName));
        }

        $query->update([
            'status' => 'SELESAI',
            'updated_at' => now(),
        ]);
    }
}
