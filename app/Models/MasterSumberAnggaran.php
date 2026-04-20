<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterSumberAnggaran extends Model
{
    protected $table = 'master_sumber_anggaran';
    protected $primaryKey = 'id_anggaran';
    public $timestamps = true;

    protected $fillable = [
        'nama_anggaran',
    ];

    // Relationships
    public function dataInventory(): HasMany
    {
        return $this->hasMany(DataInventory::class, 'id_anggaran', 'id_anggaran');
    }
}
