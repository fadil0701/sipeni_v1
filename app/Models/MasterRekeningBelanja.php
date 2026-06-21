<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterRekeningBelanja extends Model
{
    use SoftDeletes;

    protected $table = 'master_rekening_belanja';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'kode_rekening',
        'nama_rekening',
        'jenis',
        'kelompok',
        'objek',
        'rincian',
        'sub_rincian',
        'id_unit_kerja',
        'pagu_anggaran',
        'pagu_sebelumnya',
        'is_active',
    ];

    protected $casts = [
        'pagu_anggaran' => 'decimal:2',
        'pagu_sebelumnya' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(MasterUnitKerja::class, 'id_unit_kerja', 'id_unit_kerja');
    }

    public function getFullKodeAttribute(): string
    {
        return $this->kode_rekening;
    }

    public function getDisplayNameAttribute(): string
    {
        return "{$this->kode_rekening} - {$this->nama_rekening}";
    }

    public function getBudgetChangeAttribute(): float
    {
        if ($this->pagu_sebelumnya > 0) {
            return (($this->pagu_anggaran - $this->pagu_sebelumnya) / $this->pagu_sebelumnya) * 100;
        }
        return 0;
    }

    public function getFormattedPaguAttribute(): string
    {
        return 'Rp ' . number_format($this->pagu_anggaran, 0, ',', '.');
    }
}