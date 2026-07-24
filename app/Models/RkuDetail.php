<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RkuDetail extends Model
{
    use SoftDeletes;

    protected $table = 'rku_detail';
    protected $primaryKey = 'id_rku_detail';
    public $timestamps = true;

    protected $fillable = [
        'id_rku',
        'jenis_rku',
        'id_data_barang',
        'nama_item',
        'qty_rencana',
        'id_satuan',
        'harga_satuan_rencana',
        'subtotal_rencana',
        'keterangan',
        'foto_path',
        'created_by',
        'updated_by',
        'is_approved',
        'approval_notes',
        'approved_by',
        'approved_at',
        'last_price',
        'price_change_pct',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    protected $casts = [
        'jenis_rku' => 'string',
        'qty_rencana' => 'decimal:2',
        'harga_satuan_rencana' => 'decimal:2',
        'subtotal_rencana' => 'decimal:2',
        'last_price' => 'decimal:2',
        'price_change_pct' => 'decimal:2',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function rkuHeader(): BelongsTo
    {
        return $this->belongsTo(RkuHeader::class, 'id_rku', 'id_rku');
    }

    public function dataBarang(): BelongsTo
    {
        return $this->belongsTo(MasterDataBarang::class, 'id_data_barang', 'id_data_barang');
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(MasterSatuan::class, 'id_satuan', 'id_satuan');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function approverUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_approved', false);
    }

    // Accessors
    public function getJenisRkuLabelAttribute(): string
    {
        return match ($this->jenis_rku) {
            'BARANG' => 'Barang',
            'JASA' => 'Jasa',
            'MODAL' => 'Modal',
            'ASET' => 'Modal (data lama)',
            default => (string) ($this->jenis_rku ?? '-'),
        };
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->harga_satuan_rencana, 0, ',', '.');
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format($this->subtotal_rencana, 0, ',', '.');
    }

    public function getPriceChangeLabelAttribute(): string
    {
        if ($this->price_change_pct === null) {
            return '-';
        }
        
        $change = $this->price_change_pct;
        $direction = $change > 0 ? '↑' : ($change < 0 ? '↓' : '→');
        
        return "{$direction} " . abs($change) . '%';
    }

    public function getIsHighPriceChangeAttribute(): bool
    {
        return abs($this->price_change_pct ?? 0) > 25;
    }

    // Mutators
    public function setSubtotalRencanaAttribute($value): void
    {
        // Auto-calculate if qty and price are set
        $qty = $this->attributes['qty_rencana'] ?? 0;
        $price = $this->attributes['harga_satuan_rencana'] ?? 0;
        $this->attributes['subtotal_rencana'] = $qty * $price;
    }

    public function setQtyRencanaAttribute($value): void
    {
        $this->attributes['qty_rencana'] = $value;
        $this->recalculateSubtotal();
    }

    public function setHargaSatuanRencanaAttribute($value): void
    {
        $this->attributes['harga_satuan_rencana'] = $value;
        $this->recalculateSubtotal();
    }

    protected function recalculateSubtotal(): void
    {
        $qty = $this->attributes['qty_rencana'] ?? 0;
        $price = $this->attributes['harga_satuan_rencana'] ?? 0;
        $this->attributes['subtotal_rencana'] = $qty * $price;
    }

    // Methods
    public function calculatePriceChange(): self
    {
        if ($this->last_price && $this->last_price > 0) {
            $this->price_change_pct = (($this->harga_satuan_rencana - $this->last_price) / $this->last_price) * 100;
        } else {
            $this->price_change_pct = null;
        }

        return $this;
    }

    public function approve(?string $notes = null, ?int $userId = null): self
    {
        $this->update([
            'is_approved' => true,
            'approval_notes' => $notes,
            'approved_by' => $userId ?? auth()->id(),
            'approved_at' => now(),
        ]);

        return $this;
    }

    public function reject(?string $notes = null, ?int $userId = null): self
    {
        $this->update([
            'is_approved' => false,
            'approval_notes' => $notes,
            'approved_by' => $userId ?? auth()->id(),
            'approved_at' => now(),
        ]);

        return $this;
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
                $model->updated_by = auth()->id();
            }

            // Get last price for comparison
            $barang = MasterDataBarang::find($model->id_data_barang);
            if ($barang && $barang->harga_satuan) {
                $model->last_price = $barang->harga_satuan;
            }

            $model->calculatePriceChange();
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }

            $model->calculatePriceChange();
        });
    }
}