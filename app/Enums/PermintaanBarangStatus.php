<?php

namespace App\Enums;

/**
 * Canonical workflow status for permintaan_barang.status (single source of truth).
 */
enum PermintaanBarangStatus: string
{
    case Draft = 'draft';
    case Diajukan = 'diajukan';
    case Diverifikasi = 'diverifikasi';
    case Ditolak = 'ditolak';
    case MenungguPengadaan = 'menunggu_pengadaan';
    case ProsesPengadaan = 'proses_pengadaan';
    case BarangTersedia = 'barang_tersedia';
    case ProsesDistribusi = 'proses_distribusi';
    case Dikirim = 'dikirim';
    case Diterima = 'diterima';
    case Selesai = 'selesai';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Diajukan => 'Diajukan',
            self::Diverifikasi => 'Diverifikasi',
            self::Ditolak => 'Ditolak',
            self::MenungguPengadaan => 'Menunggu pengadaan',
            self::ProsesPengadaan => 'Proses pengadaan',
            self::BarangTersedia => 'Barang tersedia',
            self::ProsesDistribusi => 'Proses distribusi',
            self::Dikirim => 'Dikirim',
            self::Diterima => 'Diterima',
            self::Selesai => 'Selesai',
        };
    }

    /** Tailwind badge classes for list/show views (semantic: App\Support\UiColor). */
    public function badgeClasses(): string
    {
        return \App\Support\UiColor::badgeForStatus($this->value);
    }

    public function tone(): string
    {
        return \App\Support\UiColor::toneForStatus($this->value);
    }

    /**
     * Map legacy permintaan_barang.status_permintaan (UPPERCASE enum) to canonical values.
     */
    public static function fromLegacy(?string $value): self
    {
        $v = strtoupper(trim((string) $value));

        return match ($v) {
            'DIAJUKAN', 'DIKETAHUI_UNIT', 'DIKETAHUI_TU' => self::Diajukan,
            'DISETUJUI' => self::Diverifikasi,
            'DISETUJUI_PIMPINAN' => self::MenungguPengadaan,
            'DITOLAK' => self::Ditolak,
            'DIDISPOSISIKAN', 'DIPROSES' => self::ProsesDistribusi,
            'SELESAI' => self::Selesai,
            'DRAFT', '' => self::Draft,
            default => self::Draft,
        };
    }

    /** Accept canonical snake_case or legacy ENUM strings from older databases. */
    public static function normalizeStored(?string $value): self
    {
        if ($value === null || $value === '') {
            return self::Draft;
        }

        return self::tryFrom($value) ?? self::fromLegacy($value);
    }
}
