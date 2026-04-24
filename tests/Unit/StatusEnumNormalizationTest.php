<?php

namespace Tests\Unit;

use App\Enums\DistribusiStatus;
use App\Enums\PermintaanBarangStatus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StatusEnumNormalizationTest extends TestCase
{
    #[Test]
    public function distribusi_status_normalize_handles_legacy_values(): void
    {
        $this->assertSame(DistribusiStatus::Draft, DistribusiStatus::normalizeStored('DRAFT'));
        $this->assertSame(DistribusiStatus::Diproses, DistribusiStatus::normalizeStored('DIPROSES'));
        $this->assertSame(DistribusiStatus::Dikirim, DistribusiStatus::normalizeStored('DIKIRIM'));
        $this->assertSame(DistribusiStatus::Selesai, DistribusiStatus::normalizeStored('SELESAI'));
    }

    #[Test]
    public function permintaan_status_normalize_handles_legacy_values(): void
    {
        $this->assertSame(PermintaanBarangStatus::Diajukan, PermintaanBarangStatus::normalizeStored('DIAJUKAN'));
        $this->assertSame(PermintaanBarangStatus::Diverifikasi, PermintaanBarangStatus::normalizeStored('DISETUJUI'));
        $this->assertSame(PermintaanBarangStatus::ProsesDistribusi, PermintaanBarangStatus::normalizeStored('DIDISPOSISIKAN'));
        $this->assertSame(PermintaanBarangStatus::Selesai, PermintaanBarangStatus::normalizeStored('SELESAI'));
    }
}
