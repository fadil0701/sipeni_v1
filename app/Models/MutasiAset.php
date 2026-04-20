<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutasiAset extends Model
{
    protected $table = 'mutasi_aset';
    protected $primaryKey = 'id_mutasi';
    public $timestamps = true;

    protected $fillable = [
        'id_register_aset',
        'id_ruangan_asal',
        'id_ruangan_tujuan',
        'tanggal_mutasi',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_mutasi' => 'date',
    ];

    // Relationships
    public function registerAset(): BelongsTo
    {
        return $this->belongsTo(RegisterAset::class, 'id_register_aset', 'id_register_aset');
    }

    public function ruanganAsal(): BelongsTo
    {
        return $this->belongsTo(MasterRuangan::class, 'id_ruangan_asal', 'id_ruangan');
    }

    public function ruanganTujuan(): BelongsTo
    {
        return $this->belongsTo(MasterRuangan::class, 'id_ruangan_tujuan', 'id_ruangan');
    }
}
