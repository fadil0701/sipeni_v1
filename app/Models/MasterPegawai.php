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
        'nip',
        'nama_pegawai',
        'nama',
        'id_unit_kerja',
        'unit_kerja_id',
        'id_jabatan',
        'email_pegawai',
        'email',
        'jabatan',
        'no_telp',
        'user_id',
        'is_user',
        'status_pegawai',
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
