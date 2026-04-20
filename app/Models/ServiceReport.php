<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceReport extends Model
{
    protected $table = 'service_report';
    protected $primaryKey = 'id_service_report';

    protected $fillable = [
        'no_service_report',
        'id_permintaan_pemeliharaan',
        'id_register_aset',
        'tanggal_service',
        'tanggal_selesai',
        'jenis_service',
        'status_service',
        'vendor',
        'teknisi',
        'deskripsi_kerja',
        'tindakan_yang_dilakukan',
        'sparepart_yang_diganti',
        'biaya_service',
        'biaya_sparepart',
        'total_biaya',
        'kondisi_setelah_service',
        'file_laporan',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_service' => 'date',
        'tanggal_selesai' => 'date',
        'biaya_service' => 'decimal:2',
        'biaya_sparepart' => 'decimal:2',
        'total_biaya' => 'decimal:2',
    ];

    /**
     * Permintaan pemeliharaan yang terkait
     */
    public function permintaanPemeliharaan(): BelongsTo
    {
        return $this->belongsTo(PermintaanPemeliharaan::class, 'id_permintaan_pemeliharaan', 'id_permintaan_pemeliharaan');
    }

    /**
     * Register aset yang diservis
     */
    public function registerAset(): BelongsTo
    {
        return $this->belongsTo(RegisterAset::class, 'id_register_aset', 'id_register_aset');
    }

    /**
     * User yang membuat service report
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Riwayat pemeliharaan
     */
    public function riwayatPemeliharaan(): HasMany
    {
        return $this->hasMany(RiwayatPemeliharaan::class, 'id_service_report', 'id_service_report');
    }
}


