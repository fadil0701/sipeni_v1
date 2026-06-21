<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterJabatan extends Model
{
    protected $table = 'master_jabatan';

    protected $primaryKey = 'id_jabatan';

    public $timestamps = true;

    protected $fillable = [
        'nama_jabatan',
        'urutan',
        'deskripsi',
    ];

    public function pegawai(): HasMany
    {
        return $this->hasMany(MasterPegawai::class, 'id_jabatan', 'id_jabatan');
    }
}
