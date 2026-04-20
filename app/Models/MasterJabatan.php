<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Role;

class MasterJabatan extends Model
{
    protected $table = 'master_jabatan';
    protected $primaryKey = 'id_jabatan';
    public $timestamps = true;

    protected $fillable = [
        'nama_jabatan',
        'urutan',
        'role_id',
        'deskripsi',
    ];

    // Relationships
    public function pegawai(): HasMany
    {
        return $this->hasMany(MasterPegawai::class, 'id_jabatan', 'id_jabatan');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}
