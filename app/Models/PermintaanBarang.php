<?php

namespace App\Models;

use App\Enums\PermintaanBarangStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PermintaanBarang extends Model
{
    protected $table = 'permintaan_barang';
    protected $primaryKey = 'id_permintaan';
    public $timestamps = true;

    protected $fillable = [
        'no_permintaan',
        'id_unit_kerja',
        'id_pemohon',
        'tanggal_permintaan',
        'tipe_permintaan',
        'jenis_permintaan',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_permintaan' => 'date',
        'jenis_permintaan' => 'array', // Cast sebagai array untuk JSON
        'status' => PermintaanBarangStatus::class,
    ];

    // Relationships
    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(MasterUnitKerja::class, 'id_unit_kerja', 'id_unit_kerja');
    }

    public function pemohon(): BelongsTo
    {
        return $this->belongsTo(MasterPegawai::class, 'id_pemohon', 'id');
    }

    public function detailPermintaan(): HasMany
    {
        return $this->hasMany(DetailPermintaanBarang::class, 'id_permintaan', 'id_permintaan');
    }

    public function items(): HasMany
    {
        return $this->detailPermintaan();
    }

    public function approvalLogs(): HasMany
    {
        return $this->hasMany(ApprovalLog::class, 'id_referensi', 'id_permintaan')
            ->where('modul_approval', 'PERMINTAAN_BARANG');
    }

    public function approval(): HasMany
    {
        return $this->approvalLogs();
    }

    public function transaksiDistribusi(): HasMany
    {
        return $this->hasMany(TransaksiDistribusi::class, 'id_permintaan', 'id_permintaan');
    }

    public function distribusi(): HasMany
    {
        return $this->transaksiDistribusi();
    }

    public function draftDetailDistribusi(): HasMany
    {
        return $this->hasMany(DraftDetailDistribusi::class, 'id_permintaan', 'id_permintaan');
    }

    public function pengadaanPakets(): HasMany
    {
        return $this->hasMany(PengadaanPaket::class, 'id_permintaan', 'id_permintaan');
    }
}
