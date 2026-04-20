<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kontrak extends Model
{
    protected $table = 'kontrak';
    protected $primaryKey = 'id_kontrak';
    public $timestamps = true;

    protected $fillable = [
        'id_paket',
        'no_kontrak',
        'no_sp',
        'no_po',
        'nama_vendor',
        'npwp_vendor',
        'alamat_vendor',
        'nilai_kontrak',
        'tanggal_kontrak',
        'tanggal_mulai',
        'tanggal_selesai',
        'jenis_pembayaran',
        'jumlah_termin',
        'status_kontrak',
        'upload_dokumen',
        'keterangan',
    ];

    protected $casts = [
        'jenis_pembayaran' => 'string',
        'status_kontrak' => 'string',
        'nilai_kontrak' => 'decimal:2',
        'tanggal_kontrak' => 'date',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    // Relationships
    public function paket(): BelongsTo
    {
        return $this->belongsTo(PengadaanPaket::class, 'id_paket', 'id_paket');
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'id_kontrak', 'id_kontrak');
    }
}
