<?php

namespace App\Enums;

enum PemeliharaanRekomendasi: string
{
    case Baik = 'BAIK';
    case TidakAda = 'TIDAK_ADA';
    case PendingSparepart = 'PENDING_SPAREPART';
    case TidakBisaDiperbaiki = 'TIDAK_BISA_DIPERBAIKI';

    public function label(): string
    {
        return match ($this) {
            self::Baik => 'Baik',
            self::TidakAda => 'Tidak ada (selesai)',
            self::PendingSparepart => 'Pending (perlu pembelian spare part/dll)',
            self::TidakBisaDiperbaiki => 'Tidak bisa diperbaiki',
        };
    }

    public function isClosing(): bool
    {
        return in_array($this, [self::Baik, self::TidakAda, self::TidakBisaDiperbaiki], true);
    }

    public function requiresPengadaan(): bool
    {
        return $this === self::PendingSparepart;
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
