<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KartuInventarisRuangan extends Model
{
    protected $table = 'kartu_inventaris_ruangan';
    protected $primaryKey = 'id_kir';
    public $timestamps = true;

    protected $fillable = [
        'id_register_aset',
        'id_ruangan',
        'id_penanggung_jawab',
        'tanggal_penempatan',
    ];

    protected $casts = [
        'tanggal_penempatan' => 'date',
    ];

    // Relationships
    public function registerAset(): BelongsTo
    {
        return $this->belongsTo(RegisterAset::class, 'id_register_aset', 'id_register_aset');
    }

    public function ruangan(): BelongsTo
    {
        return $this->belongsTo(MasterRuangan::class, 'id_ruangan', 'id_ruangan');
    }

    public function penanggungJawab(): BelongsTo
    {
        return $this->belongsTo(MasterPegawai::class, 'id_penanggung_jawab', 'id');
    }
}
