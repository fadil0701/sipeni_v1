<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalLog extends Model
{
    protected $table = 'approval_log';
    public $timestamps = true;

    protected $fillable = [
        'modul_approval',
        'id_referensi',
        'id_approval_flow',
        'user_id',
        'role_id',
        'status',
        'catatan',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function approvalFlow(): BelongsTo
    {
        return $this->belongsTo(ApprovalFlowDefinition::class, 'id_approval_flow');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Get the related permintaan barang (for PERMINTAAN_BARANG module)
     */
    public function permintaan(): BelongsTo
    {
        if ($this->modul_approval === 'PERMINTAAN_BARANG') {
            return $this->belongsTo(PermintaanBarang::class, 'id_referensi', 'id_permintaan');
        }
        // Return null relationship untuk modul lain
        return $this->belongsTo(PermintaanBarang::class, 'id_referensi', 'id_permintaan')
            ->whereRaw('1 = 0'); // Always return null
    }

    /**
     * Get logs for a specific reference (e.g., permintaan_barang)
     */
    public static function getLogsForReference(string $modulApproval, int $idReferensi): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('modul_approval', $modulApproval)
            ->where('id_referensi', $idReferensi)
            ->with(['user', 'role', 'approvalFlow'])
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Get current step for a reference
     */
    public static function getCurrentStep(string $modulApproval, int $idReferensi): ?self
    {
        return self::where('modul_approval', $modulApproval)
            ->where('id_referensi', $idReferensi)
            ->whereIn('status', ['MENUNGGU', 'DIKETAHUI', 'DIVERIFIKASI'])
            ->with(['user', 'role', 'approvalFlow'])
            ->orderBy('created_at', 'desc')
            ->first();
    }
}
