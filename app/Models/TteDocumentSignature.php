<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TteDocumentSignature extends Model
{
    protected $table = 'tte_document_signatures';

    protected $fillable = [
        'tte_document_seal_id',
        'signer_role',
        'expected_pegawai_id',
        'signed_by_user_id',
        'signed_at',
        'sign_token',
        'signature_hash',
    ];

    protected function casts(): array
    {
        return [
            'signed_at' => 'datetime',
        ];
    }

    public function seal(): BelongsTo
    {
        return $this->belongsTo(TteDocumentSeal::class, 'tte_document_seal_id');
    }

    public function expectedPegawai(): BelongsTo
    {
        return $this->belongsTo(MasterPegawai::class, 'expected_pegawai_id');
    }

    public function signedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by_user_id');
    }
}
