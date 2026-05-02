<?php

namespace App\Services\Tte;

use App\Models\MasterPegawai;
use App\Models\TteDocumentSeal;
use App\Models\TteDocumentSignature;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class TteSealService
{
    public const DOCUMENT_KIR_UNIT = 'kir_unit';

    /** Peran penandatangan dokumen KIR (kolom footer). */
    public const KIR_SIGNER_ROLES = ['kepala_pusat', 'pengurus_barang', 'kepala_unit'];

    /**
     * Buat atau sinkronkan slot TTE per penandatangan untuk segel KIR.
     *
     * @param  array<string, MasterPegawai|null>  $signatories  dari resolveKirSignatories
     */
    public function ensureKirSignatureSlots(TteDocumentSeal $seal, array $signatories): void
    {
        foreach (self::KIR_SIGNER_ROLES as $role) {
            /** @var MasterPegawai|null $pegawai */
            $pegawai = $signatories[$role] ?? null;

            $sig = TteDocumentSignature::query()->firstOrNew([
                'tte_document_seal_id' => $seal->id,
                'signer_role' => $role,
            ]);

            if (! $sig->exists) {
                $sig->expected_pegawai_id = $pegawai?->id;
                $sig->sign_token = $this->generateUniqueSignToken();
                $sig->save();
            } elseif ($sig->signed_at === null && (int) $sig->expected_pegawai_id !== (int) ($pegawai?->id)) {
                $sig->expected_pegawai_id = $pegawai?->id;
                $sig->save();
            }
        }
    }

    /**
     * Catat TTE internal untuk satu peran pada segel KIR.
     *
     * @throws ValidationException
     */
    public function signKirSlot(User $user, TteDocumentSeal $seal, string $role, int $idUnitKerja): TteDocumentSignature
    {
        if (! in_array($role, self::KIR_SIGNER_ROLES, true)) {
            throw ValidationException::withMessages(['signer_role' => 'Peran penandatangan tidak valid.']);
        }

        /** @var TteDocumentSignature|null $sig */
        $sig = $seal->signatures()->where('signer_role', $role)->first();

        if (! $sig) {
            throw ValidationException::withMessages(['signer_role' => 'Slot tanda tangan untuk peran ini belum tersedia.']);
        }

        if ($sig->signed_at !== null) {
            throw ValidationException::withMessages(['signer_role' => 'Peran ini sudah menandatangani dokumen versi ini.']);
        }

        if ((int) $seal->reference_id !== $idUnitKerja) {
            abort(403, 'Segel dokumen tidak sesuai unit kerja.');
        }

        $pegawai = MasterPegawai::query()->where('user_id', $user->id)->first();

        if (! $pegawai) {
            throw ValidationException::withMessages(['auth' => 'Akun Anda tidak terhubung ke data pegawai.']);
        }

        if (! $sig->expected_pegawai_id) {
            throw ValidationException::withMessages(['signer_role' => 'Penandatangan untuk peran ini belum diatur di master pegawai/jabatan.']);
        }

        if ((int) $sig->expected_pegawai_id !== (int) $pegawai->id) {
            throw ValidationException::withMessages(['signer_role' => 'Anda bukan pegawai yang ditetapkan untuk peran ini pada dokumen ini.']);
        }

        if ($role === 'kepala_unit' && (int) $pegawai->id_unit_kerja !== $idUnitKerja) {
            abort(403, 'Anda hanya dapat menandatangani sebagai kepala unit untuk unit kerja Anda sendiri.');
        }

        $signedAt = now();
        $signatureHash = hash(
            'sha256',
            $seal->content_hash_sha256.'|'.$role.'|'.$user->id.'|'.$signedAt->toIso8601String()
        );

        $sig->signed_by_user_id = $user->id;
        $sig->signed_at = $signedAt;
        $sig->signature_hash = $signatureHash;
        $sig->save();

        return $sig;
    }

    private function generateUniqueSignToken(): string
    {
        do {
            $token = bin2hex(random_bytes(32));
        } while (TteDocumentSignature::query()->where('sign_token', $token)->exists());

        return $token;
    }

    /**
     * Snapshot deterministik untuk dokumen KIR per unit (isi data, bukan HTML).
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\KartuInventarisRuangan>  $rows
     */
    public function buildKirUnitSnapshot(Collection $rows, int $unitId): array
    {
        $items = $rows->sortBy('id_kir')->values()->map(function ($row) {
            return [
                'id_kir' => $row->id_kir,
                'updated_at' => $row->updated_at?->toIso8601String(),
            ];
        })->all();

        return [
            'v' => 1,
            'type' => self::DOCUMENT_KIR_UNIT,
            'unit_id' => $unitId,
            'items' => $items,
        ];
    }

    public function hashSnapshot(array $snapshot): string
    {
        $json = json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        return hash('sha256', $json);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\KartuInventarisRuangan>  $rows
     */
    public function createOrGetSealForKirUnit(
        int $unitId,
        Collection $rows,
        ?int $issuedByUserId,
        array $extraMeta = []
    ): TteDocumentSeal {
        $snapshot = $this->buildKirUnitSnapshot($rows, $unitId);
        $hash = $this->hashSnapshot($snapshot);

        $existing = TteDocumentSeal::query()
            ->where('document_type', self::DOCUMENT_KIR_UNIT)
            ->where('content_hash_sha256', $hash)
            ->first();

        if ($existing) {
            return $existing;
        }

        $unitName = $extraMeta['nama_unit_kerja'] ?? null;
        $meta = array_merge([
            'snapshot_v' => $snapshot['v'],
            'jumlah_kir' => $rows->count(),
            'nama_unit_kerja' => $unitName,
        ], $extraMeta);

        return TteDocumentSeal::query()->create([
            'document_type' => self::DOCUMENT_KIR_UNIT,
            'reference_id' => $unitId,
            'content_hash_sha256' => $hash,
            'public_token' => $this->generateUniquePublicToken(),
            'verification_code' => $this->generateUniqueVerificationCode(),
            'meta' => $meta,
            'issued_by_user_id' => $issuedByUserId,
            'issued_at' => now(),
        ]);
    }

    public function findByPublicToken(string $token): ?TteDocumentSeal
    {
        if ($token === '' || strlen($token) > 64) {
            return null;
        }

        return TteDocumentSeal::query()->where('public_token', $token)->first();
    }

    private function generateUniquePublicToken(): string
    {
        do {
            $token = bin2hex(random_bytes(32));
        } while (TteDocumentSeal::query()->where('public_token', $token)->exists());

        return $token;
    }

    /**
     * Format ABC1-D2EF-GH34 (mudah dibaca, tanpa O/0 ambigu seragam — campuran huruf & angka).
     */
    private function generateUniqueVerificationCode(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $parts = [];
            for ($p = 0; $p < 3; $p++) {
                $chunk = '';
                for ($i = 0; $i < 4; $i++) {
                    $chunk .= $alphabet[random_int(0, strlen($alphabet) - 1)];
                }
                $parts[] = $chunk;
            }
            $code = implode('-', $parts);
        } while (TteDocumentSeal::query()->where('verification_code', $code)->exists());

        return $code;
    }
}
