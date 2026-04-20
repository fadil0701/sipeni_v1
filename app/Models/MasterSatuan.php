<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterSatuan extends Model
{
    protected $table = 'master_satuan';
    protected $primaryKey = 'id_satuan';
    public $timestamps = true;

    protected $fillable = [
        'nama_satuan',
    ];

    // Relationships
    public function dataBarang(): HasMany
    {
        return $this->hasMany(MasterDataBarang::class, 'id_satuan', 'id_satuan');
    }

    public function detailReturBarang(): HasMany
    {
        return $this->hasMany(DetailReturBarang::class, 'id_satuan', 'id_satuan');
    }
}
