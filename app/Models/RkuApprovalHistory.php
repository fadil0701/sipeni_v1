<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RkuApprovalHistory extends Model
{
    protected $table = 'rku_approval_histories';
    public $timestamps = false;
    protected $primaryKey = 'id';

    public $fillable = [
        'id_rku',
        'approver_id',
        'from_status',
        'to_status',
        'notes',
        'is_approved',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function rku(): BelongsTo
    {
        return $this->belongsTo(RkuHeader::class, 'id_rku', 'id_rku');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function getIsApprovalAttribute(): bool
    {
        return $this->to_status === RkuHeader::STATUS_DISETUJUI;
    }

    public function getIsRejectionAttribute(): bool
    {
        return $this->to_status === RkuHeader::STATUS_DITOLAK;
    }
}