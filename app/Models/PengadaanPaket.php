<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PengadaanPaket extends Model
{
    protected $table = 'pengadaan_paket';
    protected $primaryKey = 'id_paket';
    public $timestamps = true;

    protected $fillable = [
        'id_permintaan',
        'id_sub_kegiatan',
        'id_rku',
        'no_paket',
        'nama_paket',
        'deskripsi_paket',
        'metode_pengadaan',
        'nilai_paket',
        'tanggal_mulai',
        'tanggal_selesai',
        'status_paket',
        'keterangan',
    ];

    protected $casts = [
        'metode_pengadaan' => 'string',
        'status_paket' => 'string',
        'nilai_paket' => 'decimal:2',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    // Relationships
    public function subKegiatan(): BelongsTo
    {
        return $this->belongsTo(MasterSubKegiatan::class, 'id_sub_kegiatan', 'id_sub_kegiatan');
    }

    public function rku(): BelongsTo
    {
        return $this->belongsTo(RkuHeader::class, 'id_rku', 'id_rku');
    }

    public function permintaan(): BelongsTo
    {
        return $this->belongsTo(PermintaanBarang::class, 'id_permintaan', 'id_permintaan');
    }

    public function kontrak(): HasOne
    {
        return $this->hasOne(Kontrak::class, 'id_paket', 'id_paket');
    }
}
