<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RkuVersion extends Model
{
    protected $table = 'rku_versions';
    public $timestamps = false;
    protected $primaryKey = 'id';

    public $fillable = [
        'id_rku',
        'version_number',
        'header_snapshot',
        'details_snapshot',
        'created_by',
        'change_reason',
        'created_at',
    ];

    protected $casts = [
        'header_snapshot' => 'array',
        'details_snapshot' => 'array',
        'created_at' => 'datetime',
    ];

    public function rku(): BelongsTo
    {
        return $this->belongsTo(RkuHeader::class, 'id_rku', 'id_rku');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function restore(): bool
    {
        $rku = $this->rku;
        
        if (!$rku) {
            return false;
        }

        // Create new version before restore
        $rku->createVersionSnapshot('Before restore to v' . $this->version_number);

        // Restore header
        if ($this->header_snapshot) {
            $rku->update($this->header_snapshot);
        }

        // Restore details
        $rku->rkuDetail()->delete();
        
        if ($this->details_snapshot) {
            foreach ($this->details_snapshot as $detail) {
                unset($detail['id_rku_detail'], $detail['created_at'], $detail['updated_at']);
                $rku->rkuDetail()->create($detail);
            }
        }

        $rku->recalculateTotal()->save();

        return true;
    }
}