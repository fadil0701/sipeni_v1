<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id_jabatan
 * @property string $nama_jabatan
 * @property int $urutan
 * @property string|null $deskripsi
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MasterPegawai> $pegawai
 * @property-read int|null $pegawai_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterJabatan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterJabatan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterJabatan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterJabatan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterJabatan whereDeskripsi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterJabatan whereIdJabatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterJabatan whereNamaJabatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterJabatan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterJabatan whereUrutan($value)
 * @mixin \Eloquent
 */
class MasterJabatan extends Model
{
    protected $table = 'master_jabatan';

    protected $primaryKey = 'id_jabatan';

    public $timestamps = true;

    protected $fillable = [
        'nama_jabatan',
        'urutan',
        'deskripsi',
    ];

    public function pegawai(): HasMany
    {
        return $this->hasMany(MasterPegawai::class, 'id_jabatan', 'id_jabatan');
    }
}
