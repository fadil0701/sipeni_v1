<?php

namespace App\Enums;

enum PemeliharaanJenisPelaksana: string
{
    case TeknisiAtem = 'TEKNISI_ATEM';
    case TeknisiIt = 'TEKNISI_IT';
    case KontrakService = 'KONTRAK_SERVICE';
    case Vendor = 'VENDOR';

    public function label(): string
    {
        return match ($this) {
            self::TeknisiAtem => 'Teknisi ATEM',
            self::TeknisiIt => 'Teknisi IT',
            self::KontrakService => 'Kontrak Service',
            self::Vendor => 'Vendor',
        };
    }

    public function requiresVendorName(): bool
    {
        return in_array($this, [self::KontrakService, self::Vendor], true);
    }

    public function isInternalTeknisi(): bool
    {
        return in_array($this, [self::TeknisiAtem, self::TeknisiIt], true);
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
