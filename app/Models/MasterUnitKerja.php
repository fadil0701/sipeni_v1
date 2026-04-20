<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class MasterUnitKerja extends Model
{
    protected $table = 'master_unit_kerja';
    protected $primaryKey = 'id_unit_kerja';
    public $timestamps = true;

    protected $fillable = [
        'kode_unit_kerja',
        'nama_unit_kerja',
    ];

    // Relationships
    public function ruangan(): HasMany
    {
        return $this->hasMany(MasterRuangan::class, 'id_unit_kerja', 'id_unit_kerja');
    }

    public function gudang(): HasMany
    {
        return $this->hasMany(MasterGudang::class, 'id_unit_kerja', 'id_unit_kerja');
    }

    public function pegawai(): HasMany
    {
        return $this->hasMany(MasterPegawai::class, 'id_unit_kerja', 'id_unit_kerja');
    }

    public function permintaanBarang(): HasMany
    {
        return $this->hasMany(PermintaanBarang::class, 'id_unit_kerja', 'id_unit_kerja');
    }

    public function penerimaanBarang(): HasMany
    {
        return $this->hasMany(PenerimaanBarang::class, 'id_unit_kerja', 'id_unit_kerja');
    }

    public function returBarang(): HasMany
    {
        return $this->hasMany(ReturBarang::class, 'id_unit_kerja', 'id_unit_kerja');
    }

    public function registerAsets(): HasMany
    {
        return $this->hasMany(RegisterAset::class, 'id_unit_kerja', 'id_unit_kerja');
    }
}
