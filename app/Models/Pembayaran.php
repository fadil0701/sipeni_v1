<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';
    protected $primaryKey = 'id_pembayaran';
    public $timestamps = true;

    protected $fillable = [
        'id_kontrak',
        'no_pembayaran',
        'jenis_pembayaran',
        'termin_ke',
        'nilai_pembayaran',
        'ppn',
        'pph',
        'total_pembayaran',
        'tanggal_pembayaran',
        'status_pembayaran',
        'id_verifikator',
        'tanggal_verifikasi',
        'catatan_verifikasi',
        'no_bukti_bayar',
        'upload_bukti_bayar',
        'keterangan',
    ];

    protected $casts = [
        'jenis_pembayaran' => 'string',
        'status_pembayaran' => 'string',
        'nilai_pembayaran' => 'decimal:2',
        'ppn' => 'decimal:2',
        'pph' => 'decimal:2',
        'total_pembayaran' => 'decimal:2',
        'tanggal_pembayaran' => 'date',
        'tanggal_verifikasi' => 'date',
    ];

    // Relationships
    public function kontrak(): BelongsTo
    {
        return $this->belongsTo(Kontrak::class, 'id_kontrak', 'id_kontrak');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(MasterPegawai::class, 'id_verifikator', 'id');
    }
}
