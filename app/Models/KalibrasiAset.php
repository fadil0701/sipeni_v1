<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KalibrasiAset extends Model
{
    protected $table = 'kalibrasi_aset';
    protected $primaryKey = 'id_kalibrasi';

    protected $fillable = [
        'no_kalibrasi',
        'id_register_aset',
        'id_permintaan_pemeliharaan',
        'tanggal_kalibrasi',
        'tanggal_berlaku',
        'tanggal_kadaluarsa',
        'lembaga_kalibrasi',
        'no_sertifikat',
        'status_kalibrasi',
        'biaya_kalibrasi',
        'file_sertifikat',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_kalibrasi' => 'date',
        'tanggal_berlaku' => 'date',
        'tanggal_kadaluarsa' => 'date',
        'biaya_kalibrasi' => 'decimal:2',
    ];

    /**
     * Register aset yang dikalibrasi
     */
    public function registerAset(): BelongsTo
    {
        return $this->belongsTo(RegisterAset::class, 'id_register_aset', 'id_register_aset');
    }

    /**
     * Permintaan pemeliharaan yang terkait
     */
    public function permintaanPemeliharaan(): BelongsTo
    {
        return $this->belongsTo(PermintaanPemeliharaan::class, 'id_permintaan_pemeliharaan', 'id_permintaan_pemeliharaan');
    }

    /**
     * User yang membuat kalibrasi
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
        return $this->hasMany(RiwayatPemeliharaan::class, 'id_kalibrasi', 'id_kalibrasi');
    }
}

