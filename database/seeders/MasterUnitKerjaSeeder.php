<?php

namespace Database\Seeders;

use App\Models\MasterUnitKerja;
use Illuminate\Database\Seeder;

class MasterUnitKerjaSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['kode_unit_kerja' => '10000000011', 'nama_unit_kerja' => 'MANAJEMEN'],
            ['kode_unit_kerja' => '10000000012', 'nama_unit_kerja' => 'GUDANG'],
            ['kode_unit_kerja' => '10000000013', 'nama_unit_kerja' => 'KLINIK UTAMA BALAIKOTA'],
            ['kode_unit_kerja' => '31710300014', 'nama_unit_kerja' => 'KLINIK PRATAMA SATPELKES DPRD'],
            ['kode_unit_kerja' => '31710300002', 'nama_unit_kerja' => 'KLINIK PRATAMA SATPELKES BALAIKOTA'],
            ['kode_unit_kerja' => '31710300012', 'nama_unit_kerja' => 'KLINIK PRATAMA SATPELKES DINAS SOSIAL'],
            ['kode_unit_kerja' => '31710300086', 'nama_unit_kerja' => 'KLINIK PRATAMA SATPELKES DINAS PERTAMANAN & HUTAN KOTA'],
            ['kode_unit_kerja' => '31710300053', 'nama_unit_kerja' => 'KLINIK PRATAMA SATPELKES WALIKOTA JAKARTA PUSAT'],
            ['kode_unit_kerja' => '31710300057', 'nama_unit_kerja' => 'KLINIK PRATAMA SATPELKES DINAS TEKNIS ABDUL MUIS'],
            ['kode_unit_kerja' => '31710300001', 'nama_unit_kerja' => 'KLINIK PRATAMA SATPELKES DINAS KESEHATAN'],
            ['kode_unit_kerja' => '31710300051', 'nama_unit_kerja' => 'KLINIK PRATAMA SATPELKES DINAS TEKNIS JATIBARU'],
            ['kode_unit_kerja' => '31750300009', 'nama_unit_kerja' => 'KLINIK PRATAMA SATPELKES WALIKOTA JAKARTA TIMUR'],
            ['kode_unit_kerja' => '31730300025', 'nama_unit_kerja' => 'KLINIK PRATAMA SATPELKES WALIKOTA JAKARTA BARAT'],
            ['kode_unit_kerja' => '31720300017', 'nama_unit_kerja' => 'KLINIK PRATAMA SATPELKES WALIKOTA JAKARTA UTARA'],
            ['kode_unit_kerja' => '31740300004', 'nama_unit_kerja' => 'KLINIK PRATAMA SATPELKES WALIKOTA JAKARTA SELATAN'],
            ['kode_unit_kerja' => '31740300129', 'nama_unit_kerja' => 'KLINIK PRATAMA SATPELKES DPMPTSP DKI JAKARTA'],
            ['kode_unit_kerja' => '31740300229', 'nama_unit_kerja' => 'KLINIK PRATAMA SATPELKES TAMAN MARGASATWA RAGUNAN'],
            ['kode_unit_kerja' => '31740300022', 'nama_unit_kerja' => 'KLINIK PRATAMA SATPELKES DINAS PARIWISATA DAN EKONOMI KREATIF'],
        ];

        foreach ($rows as $row) {
            MasterUnitKerja::updateOrCreate(
                ['kode_unit_kerja' => $row['kode_unit_kerja']],
                ['nama_unit_kerja' => $row['nama_unit_kerja']]
            );
        }
    }
}
