<?php

namespace Database\Seeders;

use App\Models\MasterGudang;
use App\Models\MasterUnitKerja;
use Illuminate\Database\Seeder;

class MasterGudangUnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = MasterUnitKerja::query()
            ->orderBy('id_unit_kerja')
            ->get(['id_unit_kerja', 'nama_unit_kerja']);

        if ($units->isEmpty()) {
            return;
        }

        // Normalisasi: gudang UNIT cukup 1 per unit kerja.
        MasterGudang::query()->where('jenis_gudang', 'UNIT')->delete();

        foreach ($units as $unit) {
            MasterGudang::create([
                'id_unit_kerja' => $unit->id_unit_kerja,
                'jenis_gudang' => 'UNIT',
                'kategori_gudang' => null,
                'nama_gudang' => $unit->nama_unit_kerja,
            ]);
        }
    }
}

