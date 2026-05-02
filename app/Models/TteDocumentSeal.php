<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TteDocumentSeal extends Model
{
    protected $table = 'tte_document_seals';

    protected $fillable = [
        'document_type',
        'reference_id',
        'content_hash_sha256',
        'public_token',
        'verification_code',
        'meta',
        'issued_by_user_id',
        'issued_at',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'issued_at' => 'datetime',
        ];
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by_user_id');
    }

    public function signatures(): HasMany
    {
        return $this->hasMany(TteDocumentSignature::class, 'tte_document_seal_id');
    }
}
