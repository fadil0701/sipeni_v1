<?php

namespace App\Enums;

enum DistribusiStatus: string
{
    case Draft = 'draft';
    case Diproses = 'diproses';
    case Dikirim = 'dikirim';
    case Selesai = 'selesai';

    public static function fromLegacy(?string $value): self
    {
        $v = strtoupper(trim((string) $value));

        return match ($v) {
            'DRAFT' => self::Draft,
            'DIPROSES' => self::Diproses,
            'DIKIRIM' => self::Dikirim,
            'SELESAI' => self::Selesai,
            default => self::Draft,
        };
    }

    public static function normalizeStored(?string $value): self
    {
        if ($value === null || $value === '') {
            return self::Draft;
        }

        return self::tryFrom($value) ?? self::fromLegacy($value);
    }
}
