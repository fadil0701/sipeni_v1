<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeminjamanBarang extends Model
{
    protected $table = 'peminjaman_barang';
    protected $primaryKey = 'id_peminjaman';

    protected $fillable = [
        'no_peminjaman',
        'id_unit_peminjam',
        'id_pemohon',
        'tujuan_peminjaman',
        'id_unit_pemilik',
        'id_gudang_pusat',
        'tanggal_pinjam',
        'tanggal_rencana_kembali',
        'tanggal_serah_terima',
        'tanggal_pengembalian',
        'status',
        'alasan',
    ];

    protected $casts = [
        'tanggal_pinjam' => 'date',
        'tanggal_rencana_kembali' => 'date',
        'tanggal_serah_terima' => 'datetime',
        'tanggal_pengembalian' => 'datetime',
    ];

    public const STATUS_DIAJUKAN = 'DIAJUKAN';
    public const STATUS_DIVERIFIKASI_KEPALA_UNIT_PEMINJAM = 'DIVERIFIKASI_UNIT_A';
    public const STATUS_MENUNGGU_PERSETUJUAN_KEPALA_UNIT_PEMILIK = 'MENUNGGU_PERSETUJUAN_UNIT_B';
    public const STATUS_DITOLAK_KEPALA_UNIT_PEMILIK = 'DITOLAK_UNIT_B';
    public const STATUS_MENUNGGU_APPROVAL_PENGURUS = 'MENUNGGU_APPROVAL_PENGURUS';
    public const STATUS_DITOLAK_PENGURUS = 'DITOLAK_PENGURUS';
    public const STATUS_DISETUJUI_PENGURUS = 'DISETUJUI_PENGURUS';
    public const STATUS_DIKETAHUI_KASUBAG_TU = 'DIKETAHUI_KASUBAG_TU';
    public const STATUS_SERAH_TERIMA = 'SERAH_TERIMA';
    public const STATUS_PENGEMBALIAN = 'PENGEMBALIAN';
    public const STATUS_SELESAI = 'SELESAI';

    // Alias legacy agar kompatibel.
    public const STATUS_DIVERIFIKASI_UNIT_A = self::STATUS_DIVERIFIKASI_KEPALA_UNIT_PEMINJAM;
    public const STATUS_MENUNGGU_PERSETUJUAN_UNIT_B = self::STATUS_MENUNGGU_PERSETUJUAN_KEPALA_UNIT_PEMILIK;
    public const STATUS_DITOLAK_UNIT_B = self::STATUS_DITOLAK_KEPALA_UNIT_PEMILIK;

    public const TUJUAN_ANTAR_UNIT_KERJA = 'UNIT';
    public const TUJUAN_GUDANG_PUSAT = 'GUDANG_PUSAT';
    public const TUJUAN_UNIT = self::TUJUAN_ANTAR_UNIT_KERJA;

    public static function statusLabels(): array
    {
        return [
            self::STATUS_DIAJUKAN => 'Diajukan (Unit Kerja)',
            self::STATUS_DIVERIFIKASI_KEPALA_UNIT_PEMINJAM => 'Diverifikasi (Unit Kerja)',
            self::STATUS_MENUNGGU_PERSETUJUAN_KEPALA_UNIT_PEMILIK => 'Diapproval (Unit yang Dipinjam)',
            self::STATUS_DITOLAK_KEPALA_UNIT_PEMILIK => 'Ditolak Kepala Unit Pemilik',
            self::STATUS_MENUNGGU_APPROVAL_PENGURUS => 'Diapproval + Disposisi (Pengurus Barang)',
            self::STATUS_DITOLAK_PENGURUS => 'Ditolak Pengurus',
            self::STATUS_DISETUJUI_PENGURUS => 'Menunggu Mengetahui Kasubag TU',
            self::STATUS_DIKETAHUI_KASUBAG_TU => 'Mengetahui (Kasubag TU)',
            self::STATUS_SERAH_TERIMA => 'Serah Terima',
            self::STATUS_PENGEMBALIAN => 'Pengembalian (Unit Kerja)',
            self::STATUS_SELESAI => 'Selesai',
        ];
    }

    public static function orderedStatuses(): array
    {
        return [
            self::STATUS_DIAJUKAN,
            self::STATUS_DIVERIFIKASI_KEPALA_UNIT_PEMINJAM,
            self::STATUS_MENUNGGU_APPROVAL_PENGURUS,
            self::STATUS_MENUNGGU_PERSETUJUAN_KEPALA_UNIT_PEMILIK,
            self::STATUS_DISETUJUI_PENGURUS,
            self::STATUS_DIKETAHUI_KASUBAG_TU,
            self::STATUS_SERAH_TERIMA,
            self::STATUS_PENGEMBALIAN,
            self::STATUS_SELESAI,
            self::STATUS_DITOLAK_KEPALA_UNIT_PEMILIK,
            self::STATUS_DITOLAK_PENGURUS,
        ];
    }

    public static function tujuanLabels(): array
    {
        return [
            self::TUJUAN_ANTAR_UNIT_KERJA => 'Antar Unit Kerja',
            self::TUJUAN_GUDANG_PUSAT => 'Gudang Pusat',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    public function getTujuanLabelAttribute(): string
    {
        return self::tujuanLabels()[$this->tujuan_peminjaman] ?? $this->tujuan_peminjaman;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_SELESAI => 'bg-green-50 text-green-700',
            self::STATUS_DITOLAK_KEPALA_UNIT_PEMILIK, self::STATUS_DITOLAK_PENGURUS => 'bg-red-50 text-red-700',
            self::STATUS_SERAH_TERIMA, self::STATUS_PENGEMBALIAN => 'bg-indigo-50 text-indigo-700',
            default => 'bg-blue-50 text-blue-700',
        };
    }

    public static function activeLoanStatuses(): array
    {
        return [
            self::STATUS_DIAJUKAN,
            self::STATUS_DIVERIFIKASI_KEPALA_UNIT_PEMINJAM,
            self::STATUS_MENUNGGU_PERSETUJUAN_KEPALA_UNIT_PEMILIK,
            self::STATUS_MENUNGGU_APPROVAL_PENGURUS,
            self::STATUS_DISETUJUI_PENGURUS,
            self::STATUS_DIKETAHUI_KASUBAG_TU,
            self::STATUS_SERAH_TERIMA,
            self::STATUS_PENGEMBALIAN,
        ];
    }

    public function unitPeminjam(): BelongsTo
    {
        return $this->belongsTo(MasterUnitKerja::class, 'id_unit_peminjam', 'id_unit_kerja');
    }

    public function unitPemilik(): BelongsTo
    {
        return $this->belongsTo(MasterUnitKerja::class, 'id_unit_pemilik', 'id_unit_kerja');
    }

    public function gudangPusat(): BelongsTo
    {
        return $this->belongsTo(MasterGudang::class, 'id_gudang_pusat', 'id_gudang');
    }

    public function pemohon(): BelongsTo
    {
        return $this->belongsTo(MasterPegawai::class, 'id_pemohon', 'id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DetailPeminjamanBarang::class, 'id_peminjaman', 'id_peminjaman');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PeminjamanBarangLog::class, 'id_peminjaman', 'id_peminjaman');
    }
}

