<?php

namespace App\Support;

/**
 * Semantic UI color system (single source of truth).
 *
 * Tones: primary | secondary | success | warning | danger | info | neutral
 *
 * Status decision tree (no ambiguity):
 * 1. Rejected / cancelled / failed / heavy damage     → danger
 * 2. Waiting / pending / submitted / draft-waiting    → warning
 * 3. Approved / accepted / available / completed      → success
 * 4. Active work / in transit / pipeline progress     → info
 * 5. Draft / inactive / unknown                       → neutral
 *
 * Buttons: primary = main action / proses / disposisi / mengetahui;
 *          success = approve / setujui / verifikasi / ajukan;
 *          danger = destroy / reject / tolak;
 *          warning = lanjut perbaikan / caution CTA;
 *          secondary = cancel / batal / netral.
 * Actions: gunakan x-ui.btn action="proses|setujui|…" (lihat UiColor::toneForAction).
 */
final class UiColor
{
    public const PRIMARY = 'primary';

    public const SECONDARY = 'secondary';

    public const SUCCESS = 'success';

    public const WARNING = 'warning';

    public const DANGER = 'danger';

    public const INFO = 'info';

    public const NEUTRAL = 'neutral';

    /**
     * Soft badge: light background + darker text (readable contrast).
     *
     * @return array<string, string>
     */
    public static function badgeMap(): array
    {
        return [
            self::PRIMARY => 'bg-blue-100 text-blue-900',
            self::SECONDARY => 'bg-gray-100 text-gray-900',
            self::SUCCESS => 'bg-green-100 text-green-900',
            self::WARNING => 'bg-amber-100 text-amber-950',
            self::DANGER => 'bg-red-100 text-red-900',
            self::INFO => 'bg-blue-100 text-blue-900',
            self::NEUTRAL => 'bg-gray-100 text-gray-900',
        ];
    }

    /**
     * Solid button classes.
     *
     * @return array<string, string>
     */
    public static function buttonMap(): array
    {
        return [
            self::PRIMARY => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
            self::SECONDARY => 'border border-gray-300 bg-white text-gray-800 hover:bg-gray-50 focus:ring-blue-500',
            self::SUCCESS => 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500',
            self::WARNING => 'bg-amber-600 text-white hover:bg-amber-700 focus:ring-amber-500',
            self::DANGER => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
            self::INFO => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
            self::NEUTRAL => 'border border-gray-300 bg-white text-gray-800 hover:bg-gray-50 focus:ring-gray-400',
        ];
    }

    /**
     * Soft / pill action buttons (icon rows in tables).
     *
     * @return array<string, string>
     */
    public static function softButtonMap(): array
    {
        return [
            self::PRIMARY => 'text-blue-800 bg-blue-100 hover:bg-blue-200 focus:ring-blue-500',
            self::SECONDARY => 'text-gray-700 bg-gray-100 hover:bg-gray-200 focus:ring-gray-400',
            self::SUCCESS => 'text-green-900 bg-green-100 hover:bg-green-200 focus:ring-green-500',
            self::WARNING => 'text-amber-950 bg-amber-100 hover:bg-amber-200 focus:ring-amber-500',
            self::DANGER => 'text-red-900 bg-red-100 hover:bg-red-200 focus:ring-red-500',
            self::INFO => 'text-blue-800 bg-blue-100 hover:bg-blue-200 focus:ring-blue-500',
            self::NEUTRAL => 'text-gray-700 bg-gray-100 hover:bg-gray-200 focus:ring-gray-400',
        ];
    }

    /**
     * Alert / tone card surfaces.
     *
     * @return array<string, string>
     */
    public static function alertMap(): array
    {
        return [
            self::PRIMARY => 'border-blue-200 bg-blue-50 text-blue-900',
            self::SECONDARY => 'border-gray-200 bg-gray-50 text-gray-800',
            self::SUCCESS => 'border-green-200 bg-green-50 text-green-900',
            self::WARNING => 'border-amber-200 bg-amber-50 text-amber-950',
            self::DANGER => 'border-red-200 bg-red-50 text-red-900',
            self::INFO => 'border-blue-200 bg-blue-50 text-blue-900',
            self::NEUTRAL => 'border-gray-200 bg-gray-50 text-gray-800',
        ];
    }

    public static function badge(string $tone): string
    {
        return self::badgeMap()[self::normalizeTone($tone)] ?? self::badgeMap()[self::NEUTRAL];
    }

    public static function button(string $tone): string
    {
        return self::buttonMap()[self::normalizeTone($tone)] ?? self::buttonMap()[self::SECONDARY];
    }

    public static function softButton(string $tone): string
    {
        return self::softButtonMap()[self::normalizeTone($tone)] ?? self::softButtonMap()[self::NEUTRAL];
    }

    public static function alert(string $tone): string
    {
        return self::alertMap()[self::normalizeTone($tone)] ?? self::alertMap()[self::NEUTRAL];
    }

    public static function badgeForStatus(mixed $status): string
    {
        return self::badge(self::toneForStatus($status));
    }

    public static function softButtonForStatus(mixed $status): string
    {
        return self::softButton(self::toneForStatus($status));
    }

    /**
     * Semantic tone untuk jenis aksi tombol (seragam di seluruh modul).
     *
     * @example toneForAction('proses') === 'primary'
     * @example toneForAction('setujui') === 'success'
     */
    public static function toneForAction(string $action): string
    {
        $key = strtolower(str_replace([' ', '-'], '_', trim($action)));

        return match ($key) {
            // Navigasi / lihat
            'detail', 'lihat', 'view', 'show' => self::PRIMARY,

            // Alur kerja maju (proses pengerjaan, disposisi, mengetahui)
            'proses', 'process', 'disposisi', 'mengetahui', 'ketahui' => self::PRIMARY,

            // Persetujuan positif / selesai pengadaan
            'setujui', 'approve', 'menyetujui', 'verifikasi', 'verify', 'ajukan', 'submit', 'barang_tersedia' => self::SUCCESS,

            // Perhatian / lanjut siklus
            'lanjut', 'lanjut_perbaikan', 'warning' => self::WARNING,

            // Destruktif
            'tolak', 'reject', 'hapus', 'delete', 'destroy' => self::DANGER,

            // Form utama
            'tambah', 'create', 'simpan', 'save', 'edit', 'primary' => self::PRIMARY,

            // Netral
            'batal', 'cancel', 'secondary', 'kembali' => self::SECONDARY,

            default => self::PRIMARY,
        };
    }

    /**
     * Label singkat (tooltip / aria) untuk aksi tombol.
     */
    public static function labelForAction(string $action): string
    {
        $key = strtolower(str_replace([' ', '-'], '_', trim($action)));

        return match ($key) {
            'detail', 'lihat', 'view', 'show' => 'Detail',
            'proses', 'process' => 'Proses',
            'disposisi' => 'Disposisi',
            'mengetahui', 'ketahui' => 'Mengetahui',
            'setujui', 'approve', 'menyetujui' => 'Setujui',
            'verifikasi', 'verify' => 'Verifikasi',
            'ajukan', 'submit' => 'Ajukan',
            'barang_tersedia' => 'Barang tersedia',
            'lanjut', 'lanjut_perbaikan', 'warning' => 'Lanjut Perbaikan',
            'tolak', 'reject', 'kembalikan' => 'Tolak',
            'hapus', 'delete', 'destroy' => 'Hapus',
            'tambah', 'create' => 'Tambah',
            'edit' => 'Edit',
            'simpan', 'save' => 'Simpan',
            'batal', 'cancel' => 'Batal',
            'kembali' => 'Kembali',
            default => ucfirst(str_replace('_', ' ', $key)),
        };
    }

    /**
     * Resolve semantic tone for any workflow / domain status string.
     */
    public static function toneForStatus(mixed $status): string
    {
        if ($status instanceof \BackedEnum) {
            $status = $status->value;
        }

        $key = strtoupper(str_replace(['-', ' '], '_', trim((string) $status)));

        if ($key === '') {
            return self::NEUTRAL;
        }

        // --- Explicit overrides (canonical + legacy) ---
        $map = [
            // Neutral
            'DRAFT' => self::NEUTRAL,
            'INACTIVE' => self::NEUTRAL,
            'NONAKTIF' => self::NEUTRAL,
            'DIBATALKAN' => self::DANGER,
            'CANCELLED' => self::DANGER,
            'BATAL' => self::DANGER,

            'BELUM_DIAJUKAN' => self::NEUTRAL,

            // Waiting / pending
            'MENUNGGU' => self::WARNING,
            'MENUNGGU_VERIFIKASI' => self::WARNING,
            'MENUNGGU_BUKTI_SAMPAI' => self::WARNING,
            'MENUNGGU_PENGADAAN' => self::WARNING,
            'DIAJUKAN' => self::WARNING,
            'PENDING' => self::WARNING,
            'REVISI' => self::WARNING,
            'REVISION_REQUIRED' => self::WARNING,
            'RUSAK_RINGAN' => self::WARNING,
            'SEDANG' => self::WARNING,
            'PRIORITAS_SEDANG' => self::WARNING,
            'REVIEW_KASUBAG_TU' => self::INFO,
            'REVIEW_KEPALA_PUSAT' => self::INFO,

            // Success outcomes
            'DISETUJUI' => self::SUCCESS,
            'DISETUJUI_PIMPINAN' => self::SUCCESS,
            'DIVERIFIKASI' => self::SUCCESS,
            'DITERIMA' => self::SUCCESS,
            'SELESAI' => self::SUCCESS,
            'BARANG_TERSEDIA' => self::SUCCESS,
            'SESUAI' => self::SUCCESS,
            'BAIK' => self::SUCCESS,
            'AKTIF' => self::SUCCESS,
            'ACTIVE' => self::SUCCESS,
            'COMPLETED' => self::SUCCESS,
            'APPROVED' => self::SUCCESS,
            'RENDAH' => self::NEUTRAL,
            'PRIORITAS_RENDAH' => self::NEUTRAL,

            // Danger
            'DITOLAK' => self::DANGER,
            'TIDAK_SESUAI' => self::DANGER,
            'GAGAL' => self::DANGER,
            'RUSAK_BERAT' => self::DANGER,
            'TINGGI' => self::DANGER,
            'PRIORITAS_TINGGI' => self::DANGER,
            'DARURAT' => self::DANGER,
            'REJECTED' => self::DANGER,
            'FAILED' => self::DANGER,

            // In progress / pipeline / in transit
            'DIKETAHUI' => self::INFO,
            'DIKETAHUI_UNIT' => self::INFO,
            'DIKETAHUI_TU' => self::INFO,
            'DIDISPOSISIKAN' => self::INFO,
            'DIPROSES' => self::INFO,
            'PROSES' => self::INFO,
            'PROSES_PENGADAAN' => self::INFO,
            'PROSES_DISTRIBUSI' => self::INFO,
            'DIKIRIM' => self::INFO,
            'DALAM_PERJALANAN' => self::INFO,
            'IN_PROGRESS' => self::INFO,
            'PROCESSING' => self::INFO,
            'SHIPPED' => self::INFO,

            'DISETUJUI_PENGURUS' => self::SUCCESS,
            'DIVERIFIKASI_UNIT_A' => self::SUCCESS,
            'DIVERIFIKASI_KEPALA_UNIT_PEMINJAM' => self::SUCCESS,
            'MENUNGGU_PERSETUJUAN_UNIT_B' => self::WARNING,
            'MENUNGGU_PERSETUJUAN_KEPALA_UNIT_PEMILIK' => self::WARNING,
            'MENUNGGU_APPROVAL_PENGURUS' => self::WARNING,
            'DITOLAK_UNIT_B' => self::DANGER,
            'DITOLAK_KEPALA_UNIT_PEMILIK' => self::DANGER,
            'DITOLAK_PENGURUS' => self::DANGER,
            'DIKETAHUI_KASUBAG_TU' => self::INFO,
            'SERAH_TERIMA' => self::INFO,
            'PENGEMBALIAN' => self::INFO,

            // Jenis / kategori (non-workflow) — limited palette, still semantic
            'KALIBRASI' => self::INFO,
            'RUTIN' => self::INFO,
            'PERBAIKAN' => self::WARNING,
            'PENGGANTIAN_SPAREPART' => self::WARNING,
            'ASET' => self::INFO,
            'PERSEDIAAN' => self::SUCCESS,
        ];

        if (isset($map[$key])) {
            return $map[$key];
        }

        // Snake_case canonical permintaan / distribusi statuses
        $snake = strtolower(trim((string) $status));
        $snakeMap = [
            'draft' => self::NEUTRAL,
            'diajukan' => self::WARNING,
            'diverifikasi' => self::SUCCESS,
            'ditolak' => self::DANGER,
            'menunggu_pengadaan' => self::WARNING,
            'proses_pengadaan' => self::INFO,
            'barang_tersedia' => self::SUCCESS,
            'proses_distribusi' => self::INFO,
            'dikirim' => self::INFO,
            'diterima' => self::SUCCESS,
            'selesai' => self::SUCCESS,
            'diproses' => self::INFO,
        ];

        if (isset($snakeMap[$snake])) {
            return $snakeMap[$snake];
        }

        // Keyword fallbacks (order matters — most specific first)
        if (preg_match('/tolak|reject|gagal|batal|hapus|rusak_berat|tidak_sesuai|critical/i', $key)) {
            return self::DANGER;
        }
        if (preg_match('/menunggu|ajuan|pending|revisi|rusak_ringan/i', $key)) {
            return self::WARNING;
        }
        if (preg_match('/selesai|setuju|terima|sesuai|tersedia|approved|complete|baik|verifikasi/i', $key)) {
            return self::SUCCESS;
        }
        if (preg_match('/proses|kirim|disposisi|ketahui|progress|ship|serah|pengembalian/i', $key)) {
            return self::INFO;
        }

        return self::NEUTRAL;
    }

    private static function normalizeTone(string $tone): string
    {
        $tone = strtolower(trim($tone));

        return match ($tone) {
            'primary', 'secondary', 'success', 'warning', 'danger', 'info', 'neutral' => $tone,
            'default', 'gray', 'slate', 'ghost' => self::NEUTRAL,
            'error', 'destructive' => self::DANGER,
            'amber', 'yellow' => self::WARNING,
            'green', 'emerald' => self::SUCCESS,
            'blue', 'indigo' => self::INFO,
            'red' => self::DANGER,
            default => self::NEUTRAL,
        };
    }
}
