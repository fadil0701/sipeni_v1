<?php

namespace Database\Seeders;

use App\Models\MasterGudang;
use App\Models\MasterUnitKerja;
use Illuminate\Database\Seeder;

class MasterGudangSeeder extends Seeder
{
    public function run(): void
    {
        $units = MasterUnitKerja::query()
            ->orderBy('id_unit_kerja')
            ->get(['id_unit_kerja', 'kode_unit_kerja', 'nama_unit_kerja']);

        if ($units->isEmpty()) {
            return;
        }

        $pusatUnit = $units->first(function (MasterUnitKerja $unit) {
            return $unit->kode_unit_kerja === '10000000012'
                || stripos($unit->nama_unit_kerja, 'gudang pusat') !== false;
        }) ?? $units->first();

        foreach ($units as $unit) {
            $isPusat = (int) $unit->id_unit_kerja === (int) $pusatUnit->id_unit_kerja;
            $jenisGudang = $isPusat ? 'PUSAT' : 'UNIT';
            $kategoriGudangList = ['ASET', 'FARMASI', 'PERSEDIAAN'];

            foreach ($kategoriGudangList as $kategoriGudang) {
                $namaGudang = $isPusat
                    ? 'GUDANG ' . $kategoriGudang
                    : 'GUDANG ' . $kategoriGudang . ' - ' . $unit->nama_unit_kerja;

                MasterGudang::updateOrCreate(
                    [
                        'id_unit_kerja' => $unit->id_unit_kerja,
                        'jenis_gudang' => $jenisGudang,
                        'kategori_gudang' => $kategoriGudang,
                    ],
                    [
                        'nama_gudang' => $namaGudang,
                    ]
                );
            }
        }
    }
}

