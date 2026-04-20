<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterPegawai extends Model
{
    protected $table = 'master_pegawai';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'nip_pegawai',
        'nama_pegawai',
        'id_unit_kerja',
        'id_jabatan',
        'email_pegawai',
        'no_telp',
        'user_id',
    ];

    // Relationships
    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(MasterUnitKerja::class, 'id_unit_kerja', 'id_unit_kerja');
    }

    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(MasterJabatan::class, 'id_jabatan', 'id_jabatan');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
