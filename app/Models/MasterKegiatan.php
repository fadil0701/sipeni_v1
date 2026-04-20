<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterKegiatan extends Model
{
    protected $table = 'master_kegiatan';
    protected $primaryKey = 'id_kegiatan';
    public $timestamps = true;

    protected $fillable = [
        'id_program',
        'kode_kegiatan',
        'nama_kegiatan',
    ];

    // Relationships
    public function program(): BelongsTo
    {
        return $this->belongsTo(MasterProgram::class, 'id_program', 'id_program');
    }

    public function subKegiatan(): HasMany
    {
        return $this->hasMany(MasterSubKegiatan::class, 'id_kegiatan', 'id_kegiatan');
    }
}
