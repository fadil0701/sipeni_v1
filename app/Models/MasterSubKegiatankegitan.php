<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterSubKegiatankegitan extends Model
{
    protected $table = 'master_sub_kegiatankegitan';

    protected $primaryKey = 'id_sub_kegiatankegitan';

    public $timestamps = false;

    protected $fillable = [
        'kode_sub_kegiatankegitan',
        'nama_sub_kegiatankegitan',
        'id_kegiatankegitan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function kegiatankegitan(): BelongsTo
    {
        return $this->belongsTo(MasterKegiatankegitan::class, 'id_kegiatankegitan', 'id_kegiatankegitan');
    }
}