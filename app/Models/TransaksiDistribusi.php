<?php

namespace App\Models;

use App\Enums\DistribusiStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransaksiDistribusi extends Model
{
    protected $table = 'transaksi_distribusi';
    protected $primaryKey = 'id_distribusi';
    public $timestamps = true;

    protected $fillable = [
        'no_sbbk',
        'id_permintaan',
        'tanggal_distribusi',
        'id_gudang_asal',
        'id_gudang_tujuan',
        'id_pegawai_pengirim',
        'status_distribusi',
        'tipe_distribusi',
        'id_distribusi_parent',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_distribusi' => 'datetime',
    ];

    protected function statusDistribusi(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => DistribusiStatus::normalizeStored(
                $value !== null ? (string) $value : null
            ),
            set: fn ($value) => [
                'status_distribusi' => $value instanceof DistribusiStatus
                    ? $value->value
                    : DistribusiStatus::normalizeStored((string) $value)->value,
            ],
        );
    }

    // Relationships
    public function permintaan(): BelongsTo
    {
        return $this->belongsTo(PermintaanBarang::class, 'id_permintaan', 'id_permintaan');
    }

    public function gudangAsal(): BelongsTo
    {
        return $this->belongsTo(MasterGudang::class, 'id_gudang_asal', 'id_gudang');
    }

    public function gudangTujuan(): BelongsTo
    {
        return $this->belongsTo(MasterGudang::class, 'id_gudang_tujuan', 'id_gudang');
    }

    public function pegawaiPengirim(): BelongsTo
    {
        return $this->belongsTo(MasterPegawai::class, 'id_pegawai_pengirim', 'id');
    }

    public function detailDistribusi(): HasMany
    {
        return $this->hasMany(DetailDistribusi::class, 'id_distribusi', 'id_distribusi');
    }

    public function penerimaanBarang(): HasMany
    {
        return $this->hasMany(PenerimaanBarang::class, 'id_distribusi', 'id_distribusi');
    }

    public function returBarang(): HasMany
    {
        return $this->hasMany(ReturBarang::class, 'id_distribusi', 'id_distribusi');
    }
}
