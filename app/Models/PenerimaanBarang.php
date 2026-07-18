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
        'nama_penerima_lokasi',
        'foto_bukti_sampai',
        'sumber_bukti_sampai',
        'gps_latitude',
        'gps_longitude',
        'gps_akurasi',
        'gps_alamat',
        'waktu_sampai',
        'dilapor_oleh',
        'catatan_pengirim',
    ];

    protected $casts = [
        'tanggal_penerimaan' => 'date',
        'waktu_sampai' => 'datetime',
        'status_penerimaan' => 'string',
        'gps_latitude' => 'float',
        'gps_longitude' => 'float',
        'gps_akurasi' => 'float',
    ];

    public function getRouteKeyName(): string
    {
        return 'id_penerimaan';
    }

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

    public function dilaporOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dilapor_oleh');
    }

    public function detailPenerimaan(): HasMany
    {
        return $this->hasMany(DetailPenerimaanBarang::class, 'id_penerimaan', 'id_penerimaan');
    }

    public function hasBuktiSampai(): bool
    {
        return filled($this->foto_bukti_sampai) && filled($this->nama_penerima_lokasi);
    }

    public function menungguBuktiSampai(): bool
    {
        return $this->status_penerimaan === 'MENUNGGU_BUKTI_SAMPAI';
    }

    public function returBarang(): HasMany
    {
        return $this->hasMany(ReturBarang::class, 'id_penerimaan', 'id_penerimaan');
    }
}
