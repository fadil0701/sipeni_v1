<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RkuHeader extends Model
{
    use SoftDeletes;

    protected $table = 'rku_header';
    protected $primaryKey = 'id_rku';
    public $timestamps = true;

    // Status Constants
    public const STATUS_DRAFT = 'DRAFT';
    public const STATUS_DIAJUKAN = 'DIAJUKAN';
    public const STATUS_DIPROSES = 'DIPROSES';
    public const STATUS_DISETUJUI = 'DISETUJUI';
    public const STATUS_DITOLAK = 'DITOLAK';
    public const STATUS_REVIEW_KASUBAG_TU = 'REVIEW_KASUBAG_TU';
    public const STATUS_REVIEW_KEPALA_PUSAT = 'REVIEW_KEPALA_PUSAT';
    public const STATUS_REVISION_REQUIRED = 'REVISION_REQUIRED';

    public const STATUSES = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_DIAJUKAN => 'Diajukan',
        self::STATUS_DIPROSES => 'Diproses',
        self::STATUS_REVIEW_KASUBAG_TU => 'Review Kasubbag TU',
        self::STATUS_REVIEW_KEPALA_PUSAT => 'Review Kepala Pusat',
        self::STATUS_DISETUJUI => 'Disetujui',
        self::STATUS_DITOLAK => 'Ditolak',
        self::STATUS_REVISION_REQUIRED => 'Perlu Revisi',
    ];

    // Priority Constants
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_URGENT = 'urgent';
    public const PRIORITY_CITO = 'cito';

    // Jenis Constants
    public const JENIS_BARANG = 'BARANG';
    public const JENIS_JASA = 'JASA';
    public const JENIS_MODAL = 'MODAL';

    public const JENIS = [
        self::JENIS_BARANG => 'Barang',
        self::JENIS_JASA => 'Jasa',
        self::JENIS_MODAL => 'Modal',
    ];

    // Fillable
    protected $fillable = [
        'id_unit_kerja',
        'id_sub_kegiatan',
        'no_rku',
        'tahun_anggaran',
        'tanggal_pengajuan',
        'jenis_rku',
        'status_rku',
        'id_pengaju',
        'id_approver',
        'tanggal_approval',
        'catatan_approval',
        'keterangan',
        'total_anggaran',
        'created_by',
        'updated_by',
        'deleted_by',
        'version',
        'locked_at',
        'locked_by',
        'is_locked',
        'submitted_at',
        'approved_at',
        'notes',
        'id_rekening_belanja',
        'priority',
    ];

    // Hidden
    protected $hidden = [
        'deleted_at',
        'deleted_by',
    ];

    // Casts
    protected $casts = [
        'jenis_rku' => 'string',
        'status_rku' => 'string',
        'priority' => 'string',
        'tanggal_pengajuan' => 'date',
        'tanggal_approval' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'locked_at' => 'datetime',
        'total_anggaran' => 'decimal:2',
        'version' => 'integer',
        'is_locked' => 'boolean',
    ];

    // Relationships
    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(MasterUnitKerja::class, 'id_unit_kerja', 'id_unit_kerja');
    }

    /**
     * Relasi ke master sub kegiatan (kolom DB: id_sub_kegiatan → master_sub_kegiatan).
     */
    public function subKegiatan(): BelongsTo
    {
        return $this->belongsTo(MasterSubKegiatan::class, 'id_sub_kegiatan', 'id_sub_kegiatan');
    }

    /** Alias nama lama agar eager load / kode lama tetap jalan. */
    public function subKegiatankegitan(): BelongsTo
    {
        return $this->belongsTo(MasterSubKegiatan::class, 'id_sub_kegiatan', 'id_sub_kegiatan');
    }

    public function pengaju(): BelongsTo
    {
        return $this->belongsTo(MasterPegawai::class, 'id_pengaju', 'id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(MasterPegawai::class, 'id_approver', 'id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function locker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function rkuDetail(): HasMany
    {
        return $this->hasMany(RkuDetail::class, 'id_rku', 'id_rku');
    }

    public function approvalHistories(): HasMany
    {
        return $this->hasMany(RkuApprovalHistory::class, 'id_rku', 'id_rku');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(RkuAuditLog::class, 'id_rku', 'id_rku');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(RkuVersion::class, 'id_rku', 'id_rku');
    }

    public function pengadaanPaket(): HasMany
    {
        return $this->hasMany(PengadaanPaket::class, 'id_rku', 'id_rku');
    }

    public function rekeningBelanja(): BelongsTo
    {
        return $this->belongsTo(MasterRekeningBelanja::class, 'id_rekening_belanja');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByUnit($query, int $unitId)
    {
        return $query->where('id_unit_kerja', $unitId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status_rku', $status);
    }

    public function scopeByTahun($query, string $tahun)
    {
        return $query->where('tahun_anggaran', $tahun);
    }

    public function scopeDraft($query)
    {
        return $query->where('status_rku', self::STATUS_DRAFT);
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status_rku', self::STATUS_DIAJUKAN);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status_rku', self::STATUS_DIPROSES);
    }

    public function scopeApproved($query)
    {
        return $query->where('status_rku', self::STATUS_DISETUJUI);
    }

    public function scopeRejected($query)
    {
        return $query->where('status_rku', self::STATUS_DITOLAK);
    }

    public function scopeLocked($query)
    {
        return $query->where('is_locked', true);
    }

    public function scopeUnlocked($query)
    {
        return $query->where('is_locked', false);
    }

    // Accessors & Mutators
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status_rku] ?? $this->status_rku;
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->total_anggaran, 0, ',', '.');
    }

    public function getJenisLabelAttribute(): string
    {
        return self::JENIS[$this->jenis_rku] ?? (string) $this->jenis_rku;
    }

    public function getDocumentNumberAttribute(): string
    {
        return $this->no_rku ?? 'RKU-' . str_pad($this->id_rku, 6, '0', STR_PAD_LEFT);
    }

    public function getIsEditableAttribute(): bool
    {
        return in_array($this->status_rku, [self::STATUS_DRAFT, self::STATUS_DITOLAK]) 
            && !$this->is_locked;
    }

    public function getIsDeletableAttribute(): bool
    {
        return $this->status_rku === self::STATUS_DRAFT && !$this->is_locked;
    }

    public function getIsSubmittableAttribute(): bool
    {
        return $this->status_rku === self::STATUS_DRAFT 
            && $this->rkuDetail()->exists()
            && !$this->is_locked;
    }

    public function getIsApproveableAttribute(): bool
    {
        return in_array($this->status_rku, [self::STATUS_DIAJUKAN, self::STATUS_DIPROSES]);
    }

    // Workflow Methods
    public function canEdit(?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        if (!$user) return false;

        if (!$this->is_locked) {
            if ($user->hasPermission('planning.rku.update_all')) {
                return true;
            }
            return $this->id_unit_kerja === $user->pegawai?->id_unit_kerja
                && in_array($this->status_rku, [self::STATUS_DRAFT, self::STATUS_DITOLAK, self::STATUS_REVISION_REQUIRED], true);
        }

        return false;
    }

    public function canApprove(?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        if (!$user) return false;

        return $user->hasPermission('planning.rku.approve') 
            && $this->status_rku !== self::STATUS_DISETUJUI;
    }

    public function canDelete(?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        if (!$user) return false;

        if ($user->hasPermission('planning.rku.delete_all')) {
            return true;
        }

        return $this->status_rku === self::STATUS_DRAFT 
            && !$this->is_locked
            && $this->id_unit_kerja === $user->pegawai?->id_unit_kerja;
    }

    public function isLocked(): bool
    {
        return $this->is_locked && $this->locked_at !== null;
    }

    public function isEditableBy(?User $user = null): bool
    {
        return $this->canEdit($user);
    }

    // Calculation Methods
    public function calculateTotal(): float
    {
        return $this->rkuDetail()
            ->whereNull('deleted_at')
            ->sum('subtotal_rencana');
    }

    public function recalculateTotal(): self
    {
        $this->total_anggaran = $this->calculateTotal();
        return $this;
    }

    // Versioning Methods
    public function createVersionSnapshot(?string $reason = null): RkuVersion
    {
        $versionNumber = $this->versions()->max('version_number') + 1;

        return RkuVersion::create([
            'id_rku' => $this->id_rku,
            'version_number' => $versionNumber,
            'header_snapshot' => $this->toArray(),
            'details_snapshot' => $this->rkuDetail()->get()->toArray(),
            'created_by' => auth()->id(),
            'change_reason' => $reason,
        ]);
    }

    public function restoreToVersion(int $versionNumber): bool
    {
        $version = $this->versions()->where('version_number', $versionNumber)->first();
        
        if (!$version) {
            return false;
        }

        $this->createVersionSnapshot('Restore to version ' . $versionNumber);

        if ($version->header_snapshot) {
            $this->update($version->header_snapshot);
        }

        $this->rkuDetail()->delete();

        if ($version->details_snapshot) {
            foreach ($version->details_snapshot as $detail) {
                unset($detail['id_rku_detail']);
                unset($detail['created_at']);
                unset($detail['updated_at']);
                $this->rkuDetail()->create($detail);
            }
        }

        $this->recalculateTotal()->save();

        return true;
    }

    // Locking Methods
    public function lock(?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        
        $this->update([
            'is_locked' => true,
            'locked_at' => now(),
            'locked_by' => $user?->id,
        ]);

        return true;
    }

    public function unlock(?User $user = null): bool
    {
        $user = $user ?? auth()->user();

        if ($this->locked_by !== $user?->id && !$user?->hasPermission('planning.rku.unlock_all')) {
            return false;
        }

        $this->update([
            'is_locked' => false,
            'locked_at' => null,
            'locked_by' => null,
        ]);

        return true;
    }

    // Document Number Generation
    public static function generateDocumentNumber(string $tahun, int $unitId): string
    {
        $unit = MasterUnitKerja::find($unitId);
        $unitCode = $unit?->kode_unit_kerja ?? 'UNK';
        
        $prefix = "RKU/{$unitCode}/{$tahun}/";
        
        $lastRku = self::where('no_rku', 'like', $prefix . '%')
            ->where('tahun_anggaran', $tahun)
            ->orderByDesc('no_rku')
            ->first();

        $nextNumber = 1;
        if ($lastRku) {
            $lastNumber = (int) substr($lastRku->no_rku, -4);
            $nextNumber = $lastNumber + 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    // Boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
                $model->updated_by = auth()->id();
            }
            
            if (empty($model->no_rku) && !empty($model->tahun_anggaran) && !empty($model->id_unit_kerja)) {
                $model->no_rku = self::generateDocumentNumber(
                    $model->tahun_anggaran,
                    $model->id_unit_kerja
                );
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });

        static::deleting(function ($model) {
            if (auth()->check()) {
                $model->deleted_by = auth()->id();
                $model->save();
            }
        });
    }
}