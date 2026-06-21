<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id',
        'module_key',
        'action',
        'entity_type',
        'entity_id',
        'description',
        'old_values',
        'new_values',
        'metadata',
        'ip_address',
        'user_agent',
        'request_url',
        'method',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function entityLabel(): string
    {
        if ($this->entity_type === null) {
            return '—';
        }

        $short = class_basename($this->entity_type);
        if ($this->entity_id !== null) {
            return $short.' #'.$this->entity_id;
        }

        return $short;
    }
}
