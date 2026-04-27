<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturBarang extends Model
{
    public const JENIS_RUSAK = 'RUSAK';
    public const JENIS_SISA = 'SISA';
    public const JENIS_LAINNYA = 'LAINNYA';

    protected $table = 'retur_barang';
    protected $primaryKey = 'id_retur';
    public $timestamps = true;

    protected $fillable = [
        'no_retur',
        'id_unit_kerja',
        'id_gudang_asal',
        'id_gudang_tujuan',
        'id_pegawai_pengirim',
        'tanggal_retur',
        'status_retur',
        'alasan_retur',
    ];

    protected $casts = [
        'tanggal_retur' => 'date',
        'status_retur' => 'string',
    ];

    // Relationships
    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(MasterUnitKerja::class, 'id_unit_kerja', 'id_unit_kerja');
    }

    public function gudangAsal(): BelongsTo
    {
        return $this->belongsTo(MasterGudang::class, 'id_gudang_asal', 'id_gudang');
    }

    public function gudangTujuan(): BelongsTo
    {
        return $this->belongsTo(MasterGudang::class, 'id_gudang_tujuan', 'id_gudang');
    }

    public function pegawaiPengirim(): BelongsTo
    {
        return $this->belongsTo(MasterPegawai::class, 'id_pegawai_pengirim', 'id');
    }

    public function detailRetur(): HasMany
    {
        return $this->hasMany(DetailReturBarang::class, 'id_retur', 'id_retur');
    }

    public static function jenisReturOptions(): array
    {
        return [
            self::JENIS_RUSAK => 'Barang Rusak',
            self::JENIS_SISA => 'Sisa Pakai / Tidak Terpakai',
            self::JENIS_LAINNYA => 'Lainnya',
        ];
    }

    public function getJenisReturAttribute(): string
    {
        $alasan = (string) ($this->alasan_retur ?? '');
        if (str_starts_with($alasan, '[' . self::JENIS_RUSAK . ']')) {
            return self::JENIS_RUSAK;
        }
        if (str_starts_with($alasan, '[' . self::JENIS_SISA . ']')) {
            return self::JENIS_SISA;
        }

        return self::JENIS_LAINNYA;
    }
}



