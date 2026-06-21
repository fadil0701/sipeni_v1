<?php

namespace Database\Factories;

use App\Models\MasterPegawai;
use App\Models\MasterUnitKerja;
use App\Models\PermintaanBarang;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PermintaanBarang>
 */
class PermintaanBarangFactory extends Factory
{
    protected $model = PermintaanBarang::class;

    public function definition(): array
    {
        $unit = MasterUnitKerja::query()->inRandomOrder()->first();
        $pegawai = MasterPegawai::query()->when($unit, fn ($q) => $q->where('id_unit_kerja', $unit->id_unit_kerja))->inRandomOrder()->first();

        return [
            'no_permintaan' => 'TST/'.now()->format('Ymd').'/'.fake()->unique()->numerify('####'),
            'id_unit_kerja' => $unit?->id_unit_kerja ?? 1,
            'id_pemohon' => $pegawai?->id ?? 1,
            'tanggal_permintaan' => now()->toDateString(),
            'tipe_permintaan' => 'RUTIN',
            'jenis_permintaan' => ['PERSEDIAAN'],
            'status' => 'draft',
            'keterangan' => fake()->optional()->sentence(),
        ];
    }
}
