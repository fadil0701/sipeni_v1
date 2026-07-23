<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $user_id
 * @property int $is_user
 * @property string $status_pegawai
 * @property string $nip_pegawai
 * @property string|null $nip
 * @property string $nama_pegawai
 * @property string|null $nama
 * @property int $id_unit_kerja
 * @property int|null $unit_kerja_id
 * @property int $id_jabatan
 * @property \App\Models\MasterJabatan $jabatan
 * @property string $email_pegawai
 * @property string|null $email
 * @property string|null $no_telp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string|null $nama_jabatan
 * @property-read \App\Models\MasterJabatan $masterJabatan
 * @property-read \App\Models\MasterUnitKerja $unitKerja
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterPegawai newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterPegawai newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterPegawai query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterPegawai whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterPegawai whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterPegawai whereEmailPegawai($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterPegawai whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterPegawai whereIdJabatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterPegawai whereIdUnitKerja($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterPegawai whereIsUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterPegawai whereJabatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterPegawai whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterPegawai whereNamaPegawai($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterPegawai whereNip($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterPegawai whereNipPegawai($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterPegawai whereNoTelp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterPegawai whereStatusPegawai($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterPegawai whereUnitKerjaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterPegawai whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterPegawai whereUserId($value)
 * @mixin \Eloquent
 */
class MasterPegawai extends Model
{
    protected $table = 'master_pegawai';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'nip_pegawai',
        'nip',
        'nama_pegawai',
        'nama',
        'id_unit_kerja',
        'unit_kerja_id',
        'id_jabatan',
        'email_pegawai',
        'email',
        'jabatan',
        'no_telp',
        'user_id',
        'is_user',
        'status_pegawai',
    ];

    // Relationships
    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(MasterUnitKerja::class, 'id_unit_kerja', 'id_unit_kerja');
    }

    public function jabatan(): BelongsTo
    {
        return $this->masterJabatan();
    }

    public function masterJabatan(): BelongsTo
    {
        return $this->belongsTo(MasterJabatan::class, 'id_jabatan', 'id_jabatan');
    }

    public function getNamaJabatanAttribute(): ?string
    {
        if ($this->relationLoaded('masterJabatan')) {
            $nama = $this->getRelation('masterJabatan')?->nama_jabatan;
            if (filled($nama)) {
                return $nama;
            }
        } elseif ($this->id_jabatan) {
            $nama = $this->masterJabatan()->value('nama_jabatan');
            if (filled($nama)) {
                return $nama;
            }
        }

        $legacy = $this->attributes['jabatan'] ?? null;

        return is_string($legacy) && $legacy !== '' ? $legacy : null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Pegawai dengan jabatan teknisi internal (ATEM / IT Support).
     */
    public function scopeTeknisiInternal($query)
    {
        return $query->whereHas('masterJabatan', function ($q) {
            $q->where(function ($inner) {
                $inner->whereRaw('LOWER(nama_jabatan) LIKE ?', ['%atem%'])
                    ->orWhereRaw('LOWER(nama_jabatan) LIKE ?', ['%it support%'])
                    ->orWhereRaw('LOWER(nama_jabatan) LIKE ?', ['%teknisi%']);
            });
        });
    }
}
