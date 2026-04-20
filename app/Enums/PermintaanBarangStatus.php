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

    /** Tailwind badge classes for list/show views */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Draft => 'bg-gray-100 text-gray-800',
            self::Diajukan => 'bg-yellow-100 text-yellow-800',
            self::Diverifikasi => 'bg-green-100 text-green-800',
            self::Ditolak => 'bg-red-100 text-red-800',
            self::MenungguPengadaan => 'bg-amber-100 text-amber-800',
            self::ProsesPengadaan => 'bg-orange-100 text-orange-800',
            self::BarangTersedia => 'bg-lime-100 text-lime-800',
            self::ProsesDistribusi => 'bg-blue-100 text-blue-800',
            self::Dikirim => 'bg-indigo-100 text-indigo-800',
            self::Diterima => 'bg-teal-100 text-teal-800',
            self::Selesai => 'bg-emerald-100 text-emerald-800',
        };
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
