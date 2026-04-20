<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalMaintenance extends Model
{
    protected $table = 'jadwal_maintenance';
    protected $primaryKey = 'id_jadwal';

    protected $fillable = [
        'id_register_aset',
        'jenis_maintenance',
        'periode',
        'interval_hari',
        'tanggal_mulai',
        'tanggal_selanjutnya',
        'tanggal_terakhir',
        'status',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selanjutnya' => 'date',
        'tanggal_terakhir' => 'date',
    ];

    /**
     * Register aset yang dijadwalkan
     */
    public function registerAset(): BelongsTo
    {
        return $this->belongsTo(RegisterAset::class, 'id_register_aset', 'id_register_aset');
    }

    /**
     * User yang membuat jadwal
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}


