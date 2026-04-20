<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterAset extends Model
{
    protected $table = 'master_aset';
    protected $primaryKey = 'id_aset';
    public $timestamps = true;

    protected $fillable = [
        'nama_aset',
    ];

    // Relationships
    public function kodeBarang(): HasMany
    {
        return $this->hasMany(MasterKodeBarang::class, 'id_aset', 'id_aset');
    }
}
