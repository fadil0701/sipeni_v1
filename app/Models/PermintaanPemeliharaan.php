<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PermintaanPemeliharaan extends Model
{
    protected $table = 'permintaan_pemeliharaan';
    protected $primaryKey = 'id_permintaan_pemeliharaan';

    protected $fillable = [
        'no_permintaan_pemeliharaan',
        'id_register_aset',
        'id_unit_kerja',
        'id_pemohon',
        'tanggal_permintaan',
        'jenis_pemeliharaan',
        'prioritas',
        'status_permintaan',
        'deskripsi_kerusakan',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_permintaan' => 'date',
    ];

    /**
     * Register aset yang diminta untuk pemeliharaan
     */
    public function registerAset(): BelongsTo
    {
        return $this->belongsTo(RegisterAset::class, 'id_register_aset', 'id_register_aset');
    }

    /**
     * Unit kerja pemohon
     */
    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(MasterUnitKerja::class, 'id_unit_kerja', 'id_unit_kerja');
    }

    /**
     * Pegawai pemohon
     */
    public function pemohon(): BelongsTo
    {
        return $this->belongsTo(MasterPegawai::class, 'id_pemohon', 'id');
    }

    /**
     * Service report yang terkait
     */
    public function serviceReport(): HasOne
    {
        return $this->hasOne(ServiceReport::class, 'id_permintaan_pemeliharaan', 'id_permintaan_pemeliharaan');
    }

    /**
     * Kalibrasi yang terkait (jika jenis KALIBRASI)
     */
    public function kalibrasi(): HasOne
    {
        return $this->hasOne(KalibrasiAset::class, 'id_permintaan_pemeliharaan', 'id_permintaan_pemeliharaan');
    }

    /**
     * Riwayat pemeliharaan
     */
    public function riwayatPemeliharaan(): HasMany
    {
        return $this->hasMany(RiwayatPemeliharaan::class, 'id_permintaan_pemeliharaan', 'id_permintaan_pemeliharaan');
    }

    /**
     * Approval logs untuk permintaan pemeliharaan
     */
    public function approvalLogs(): HasMany
    {
        return $this->hasMany(ApprovalLog::class, 'id_referensi', 'id_permintaan_pemeliharaan')
            ->where('modul_approval', 'PERMINTAAN_PEMELIHARAAN');
    }
}


