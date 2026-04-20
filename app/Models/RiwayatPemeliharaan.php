<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatPemeliharaan extends Model
{
    protected $table = 'riwayat_pemeliharaan';
    protected $primaryKey = 'id_riwayat';

    protected $fillable = [
        'id_register_aset',
        'id_permintaan_pemeliharaan',
        'id_service_report',
        'id_kalibrasi',
        'tanggal_pemeliharaan',
        'jenis_pemeliharaan',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_pemeliharaan' => 'date',
    ];

    /**
     * Register aset
     */
    public function registerAset(): BelongsTo
    {
        return $this->belongsTo(RegisterAset::class, 'id_register_aset', 'id_register_aset');
    }

    /**
     * Permintaan pemeliharaan
     */
    public function permintaanPemeliharaan(): BelongsTo
    {
        return $this->belongsTo(PermintaanPemeliharaan::class, 'id_permintaan_pemeliharaan', 'id_permintaan_pemeliharaan');
    }

    /**
     * Service report
     */
    public function serviceReport(): BelongsTo
    {
        return $this->belongsTo(ServiceReport::class, 'id_service_report', 'id_service_report');
    }

    /**
     * Kalibrasi
     */
    public function kalibrasi(): BelongsTo
    {
        return $this->belongsTo(KalibrasiAset::class, 'id_kalibrasi', 'id_kalibrasi');
    }
}


