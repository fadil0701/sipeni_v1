<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RkuHeader extends Model
{
    protected $table = 'rku_header';
    protected $primaryKey = 'id_rku';
    public $timestamps = true;

    protected $fillable = [
        'id_unit_kerja',
        'id_sub_kegiatan',
        'no_rku',
        'tahun_anggaran',
        'tanggal_pengajuan',
        'jenis_rku',
        'status_rku',
        'id_pengaju',
        'id_approver',
        'tanggal_approval',
        'catatan_approval',
        'keterangan',
        'total_anggaran',
    ];

    protected $casts = [
        'jenis_rku' => 'string',
        'status_rku' => 'string',
        'tanggal_pengajuan' => 'date',
        'tanggal_approval' => 'date',
        'total_anggaran' => 'decimal:2',
    ];

    // Relationships
    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(MasterUnitKerja::class, 'id_unit_kerja', 'id_unit_kerja');
    }

    public function subKegiatan(): BelongsTo
    {
        return $this->belongsTo(MasterSubKegiatan::class, 'id_sub_kegiatan', 'id_sub_kegiatan');
    }

    public function pengaju(): BelongsTo
    {
        return $this->belongsTo(MasterPegawai::class, 'id_pengaju', 'id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(MasterPegawai::class, 'id_approver', 'id');
    }

    public function rkuDetail(): HasMany
    {
        return $this->hasMany(RkuDetail::class, 'id_rku', 'id_rku');
    }

    public function pengadaanPaket(): HasMany
    {
        return $this->hasMany(PengadaanPaket::class, 'id_rku', 'id_rku');
    }
}
