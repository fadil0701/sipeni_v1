<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PemakaianBarang extends Model
{
    protected $table = 'pemakaian_barang';
    protected $primaryKey = 'id_pemakaian';
    public $timestamps = true;

    protected $fillable = [
        'no_pemakaian',
        'id_unit_kerja',
        'id_gudang',
        'id_pegawai_pemakai',
        'tanggal_pemakaian',
        'status_pemakaian',
        'keterangan',
        'alasan_pemakaian',
        'id_approver',
        'tanggal_approval',
        'catatan_approval',
    ];

    protected $casts = [
        'tanggal_pemakaian' => 'date',
        'status_pemakaian' => 'string',
        'tanggal_approval' => 'datetime',
    ];

    // Relationships
    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(MasterUnitKerja::class, 'id_unit_kerja', 'id_unit_kerja');
    }

    public function gudang(): BelongsTo
    {
        return $this->belongsTo(MasterGudang::class, 'id_gudang', 'id_gudang');
    }

    public function pegawaiPemakai(): BelongsTo
    {
        return $this->belongsTo(MasterPegawai::class, 'id_pegawai_pemakai', 'id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_approver', 'id');
    }

    public function detailPemakaian(): HasMany
    {
        return $this->hasMany(DetailPemakaianBarang::class, 'id_pemakaian', 'id_pemakaian');
    }
}
