<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturBarang extends Model
{
    protected $table = 'retur_barang';
    protected $primaryKey = 'id_retur';
    public $timestamps = true;

    protected $fillable = [
        'no_retur',
        'id_penerimaan',
        'id_distribusi',
        'id_unit_kerja',
        'id_gudang_asal',
        'id_gudang_tujuan',
        'id_pegawai_pengirim',
        'tanggal_retur',
        'status_retur',
        'alasan_retur',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_retur' => 'date',
        'status_retur' => 'string',
    ];

    // Relationships
    public function penerimaan(): BelongsTo
    {
        return $this->belongsTo(PenerimaanBarang::class, 'id_penerimaan', 'id_penerimaan');
    }

    public function distribusi(): BelongsTo
    {
        return $this->belongsTo(TransaksiDistribusi::class, 'id_distribusi', 'id_distribusi');
    }

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
}



