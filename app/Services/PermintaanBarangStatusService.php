<?php

namespace App\Services;

use App\Enums\PermintaanBarangStatus;
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
        $distribusi->loadMissing('permintaan');
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
    }
}
