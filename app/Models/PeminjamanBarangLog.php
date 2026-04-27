<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeminjamanBarangLog extends Model
{
    protected $table = 'peminjaman_barang_log';
    protected $primaryKey = 'id_log';

    protected $fillable = [
        'id_peminjaman',
        'user_id',
        'aksi',
        'status_sebelum',
        'status_sesudah',
        'catatan',
    ];

    public function peminjaman(): BelongsTo
    {
        return $this->belongsTo(PeminjamanBarang::class, 'id_peminjaman', 'id_peminjaman');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}

