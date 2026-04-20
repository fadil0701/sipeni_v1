<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterProgram extends Model
{
    protected $table = 'master_program';
    protected $primaryKey = 'id_program';
    public $timestamps = true;

    protected $fillable = [
        'kode_program',
        'nama_program',
    ];

    // Relationships
    public function kegiatan(): HasMany
    {
        return $this->hasMany(MasterKegiatan::class, 'id_program', 'id_program');
    }
}
