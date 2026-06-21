<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RkuAuditLog extends Model
{
    protected $table = 'rku_audit_logs';
    public $timestamps = false;
    protected $primaryKey = 'id';

    public $fillable = [
        'id_rku',
        'user_id',
        'action',
        'old_values',
        'new_values',
        'changed_fields',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'changed_fields' => 'array',
        'created_at' => 'datetime',
    ];

    public const ACTION_CREATED = 'created';
    public const ACTION_UPDATED = 'updated';
    public const ACTION_DELETED = 'deleted';
    public const ACTION_SUBMITTED = 'submitted';
    public const ACTION_APPROVED = 'approved';
    public const ACTION_REJECTED = 'rejected';
    public const ACTION_CANCELLED = 'cancelled';
    public const ACTION_LOCKED = 'locked';
    public const ACTION_UNLOCKED = 'unlocked';
    public const ACTION_RESTORED = 'restored';
    public const ACTION_VIEWED = 'viewed';

    public function rku(): BelongsTo
    {
        return $this->belongsTo(RkuHeader::class, 'id_rku', 'id_rku');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function log(
        int $rkuId,
        string $action,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $changedFields = null
    ): self {
        return self::create([
            'id_rku' => $rkuId,
            'user_id' => auth()->id(),
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'changed_fields' => $changedFields,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }

    public function getChangedFieldsListAttribute(): string
    {
        if (!$this->changed_fields) {
            return '-';
        }

        return implode(', ', $this->changed_fields);
    }
}