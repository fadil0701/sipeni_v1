<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalPermintaan extends Model
{
    protected $table = 'approval_permintaan';
    protected $primaryKey = 'id_approval';
    public $timestamps = true;

    protected $fillable = [
        'modul_approval',
        'id_referensi',
        'id_approver',
        'status_approval',
        'catatan',
        'tanggal_approval',
    ];

    protected $casts = [
        'status_approval' => 'string',
        'tanggal_approval' => 'datetime',
    ];

    // Relationships
    public function approver(): BelongsTo
    {
        return $this->belongsTo(MasterPegawai::class, 'id_approver', 'id');
    }
}
