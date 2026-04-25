<?php

namespace Database\Seeders;

use App\Models\MasterRuangan;
use App\Models\MasterUnitKerja;
use Illuminate\Database\Seeder;

class MasterRuanganSeeder extends Seeder
{
    public function run(): void
    {
        $rowsByUnit = [
            'MANAJEMEN' => [
                ['kode_ruangan' => 'MAN-001', 'nama_ruangan' => 'RUANGAN KEPALA PPKP'],
                ['kode_ruangan' => 'MAN-002', 'nama_ruangan' => 'RUANGAN KEPALA SUBBAG TU'],
                ['kode_ruangan' => 'MAN-003', 'nama_ruangan' => 'RUANGAN KEUANGAN'],
                ['kode_ruangan' => 'MAN-004', 'nama_ruangan' => 'RUANGAN KASATPEL'],
                ['kode_ruangan' => 'MAN-005', 'nama_ruangan' => 'RUANGAN UTAMA'],
                ['kode_ruangan' => 'MAN-006', 'nama_ruangan' => 'RUANGAN KEPEGAWAIAN'],
            ],
            'KLINIK UTAMA BALAIKOTA' => [
                ['kode_ruangan' => '0001', 'nama_ruangan' => 'Umum'],
                ['kode_ruangan' => '0005', 'nama_ruangan' => 'Laboratorium'],
                ['kode_ruangan' => '0007', 'nama_ruangan' => 'APOTEK'],
                ['kode_ruangan' => '0011', 'nama_ruangan' => 'POLI MATA'],
                ['kode_ruangan' => '0023', 'nama_ruangan' => 'POLI PENYAKIT DALAM'],
                ['kode_ruangan' => '0025', 'nama_ruangan' => 'POLI FISIOTERAPI'],
                ['kode_ruangan' => '0026', 'nama_ruangan' => 'POLI OBGYN'],
                ['kode_ruangan' => '0031', 'nama_ruangan' => 'POLI GIZI'],
                ['kode_ruangan' => '0033', 'nama_ruangan' => 'POLI RADIOLOGI'],
                ['kode_ruangan' => '0036', 'nama_ruangan' => 'POLI JIWA'],
                ['kode_ruangan' => '0037', 'nama_ruangan' => 'POLI JANTUNG'],
                ['kode_ruangan' => '0038', 'nama_ruangan' => 'POLI SARAF'],
                ['kode_ruangan' => '0039', 'nama_ruangan' => 'POLI THT'],
                ['kode_ruangan' => '0041', 'nama_ruangan' => 'POLI BEDAH MULUT'],
                ['kode_ruangan' => '0042', 'nama_ruangan' => 'POLI KEDOKTERAN OLAHRAGA'],
                ['kode_ruangan' => '0045', 'nama_ruangan' => 'POLI KONSERVASI GIGI'],
                ['kode_ruangan' => '0048', 'nama_ruangan' => 'POLI REFRAKSIONIS'],
                ['kode_ruangan' => '0049', 'nama_ruangan' => 'POLI NUTRISIONIS'],
                ['kode_ruangan' => '0050', 'nama_ruangan' => 'RUANG OLAHRAGA DAN KEBUGARAN'],
                ['kode_ruangan' => '0055', 'nama_ruangan' => 'POLI PERIODONTI'],
                ['kode_ruangan' => '0065', 'nama_ruangan' => 'POLI VAKSIN'],
                ['kode_ruangan' => '0069', 'nama_ruangan' => 'POLI AKUPUNKTUR'],
            ],
            'KLINIK PRATAMA SATPELKES BALAIKOTA' => [
                ['kode_ruangan' => '0001', 'nama_ruangan' => 'POLI UMUM'],
                ['kode_ruangan' => '0002', 'nama_ruangan' => 'POLI GIGI'],
                ['kode_ruangan' => '0003', 'nama_ruangan' => 'POLI KIA'],
                ['kode_ruangan' => '0004', 'nama_ruangan' => 'LDM'],
                ['kode_ruangan' => '0005', 'nama_ruangan' => 'APOTEK'],
            ],
            'KLINIK PRATAMA SATPELKES DPRD' => [
                ['kode_ruangan' => '0001', 'nama_ruangan' => 'POLI UMUM'],
                ['kode_ruangan' => '0002', 'nama_ruangan' => 'POLI GIGI'],
                ['kode_ruangan' => '0003', 'nama_ruangan' => 'APOTEK'],
            ],
            'KLINIK PRATAMA SATPELKES DINAS SOSIAL' => [
                ['kode_ruangan' => '0001', 'nama_ruangan' => 'POLI UMUM'],
                ['kode_ruangan' => '0002', 'nama_ruangan' => 'POLI GIGI'],
                ['kode_ruangan' => '0003', 'nama_ruangan' => 'APOTEK'],
            ],
            'KLINIK PRATAMA SATPELKES DINAS PERTAMANAN & HUTAN KOTA' => [
                ['kode_ruangan' => '0001', 'nama_ruangan' => 'POLI UMUM'],
                ['kode_ruangan' => '0002', 'nama_ruangan' => 'POLI GIGI'],
                ['kode_ruangan' => '0003', 'nama_ruangan' => 'APOTEK'],
            ],
            'KLINIK PRATAMA SATPELKES WALIKOTA JAKARTA PUSAT' => [
                ['kode_ruangan' => '0001', 'nama_ruangan' => 'POLI UMUM'],
                ['kode_ruangan' => '0002', 'nama_ruangan' => 'POLI GIGI'],
                ['kode_ruangan' => '0003', 'nama_ruangan' => 'APOTEK'],
            ],
        ];

        $defaultRooms = [
            ['kode_ruangan' => '0001', 'nama_ruangan' => 'POLI UMUM'],
            ['kode_ruangan' => '0002', 'nama_ruangan' => 'POLI GIGI'],
            ['kode_ruangan' => '0003', 'nama_ruangan' => 'APOTEK'],
        ];

        $units = MasterUnitKerja::query()->get(['id_unit_kerja', 'nama_unit_kerja']);

        foreach ($units as $unit) {
            $rows = $rowsByUnit[$unit->nama_unit_kerja] ?? $defaultRooms;
            foreach ($rows as $row) {
                MasterRuangan::updateOrCreate(
                    [
                        'id_unit_kerja' => $unit->id_unit_kerja,
                        'kode_ruangan' => $row['kode_ruangan'],
                    ],
                    [
                        'nama_ruangan' => $row['nama_ruangan'],
                    ]
                );
            }
        }
    }
}
