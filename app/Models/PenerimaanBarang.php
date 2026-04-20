<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenerimaanBarang extends Model
{
    protected $table = 'penerimaan_barang';
    protected $primaryKey = 'id_penerimaan';
    public $timestamps = true;

    protected $fillable = [
        'no_penerimaan',
        'id_distribusi',
        'id_unit_kerja',
        'id_pegawai_penerima',
        'tanggal_penerimaan',
        'status_penerimaan',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_penerimaan' => 'date',
        'status_penerimaan' => 'string',
    ];

    // Relationships
    public function distribusi(): BelongsTo
    {
        return $this->belongsTo(TransaksiDistribusi::class, 'id_distribusi', 'id_distribusi');
    }

    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(MasterUnitKerja::class, 'id_unit_kerja', 'id_unit_kerja');
    }

    public function pegawaiPenerima(): BelongsTo
    {
        return $this->belongsTo(MasterPegawai::class, 'id_pegawai_penerima', 'id');
    }

    public function detailPenerimaan(): HasMany
    {
        return $this->hasMany(DetailPenerimaanBarang::class, 'id_penerimaan', 'id_penerimaan');
    }

    public function returBarang(): HasMany
    {
        return $this->hasMany(ReturBarang::class, 'id_penerimaan', 'id_penerimaan');
    }
}
