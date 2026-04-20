<?php

namespace App\Observers;

use App\Enums\PermintaanBarangStatus;
use App\Models\PermintaanBarang;
use App\Services\PermintaanService;

class PermintaanBarangObserver
{
    public function saved(PermintaanBarang $permintaan): void
    {
        if ($permintaan->status !== PermintaanBarangStatus::Diajukan) {
            return;
        }

        app(PermintaanService::class)->ensureInitialApprovalLog($permintaan);
    }
}
