<?php

namespace Database\Seeders;

use App\Models\MasterGudang;
use App\Models\MasterUnitKerja;
use Illuminate\Database\Seeder;

class MasterGudangPusatSeeder extends Seeder
{
    public function run(): void
    {
        $pusatUnit = MasterUnitKerja::query()
            ->where(function ($q) {
                $q->where('kode_unit_kerja', '10000000012')
                    ->orWhereRaw('LOWER(nama_unit_kerja) like ?', ['%gudang pusat%']);
            })
            ->orderBy('id_unit_kerja')
            ->first();

        if (!$pusatUnit) {
            return;
        }

        foreach (['ASET', 'FARMASI', 'PERSEDIAAN'] as $kategoriGudang) {
            MasterGudang::updateOrCreate(
                [
                    'id_unit_kerja' => $pusatUnit->id_unit_kerja,
                    'jenis_gudang' => 'PUSAT',
                    'kategori_gudang' => $kategoriGudang,
                ],
                [
                    'nama_gudang' => 'GUDANG ' . $kategoriGudang,
                ]
            );
        }
    }
}

