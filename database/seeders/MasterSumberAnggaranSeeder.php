<?php

namespace Database\Seeders;

use App\Models\MasterSumberAnggaran;
use Illuminate\Database\Seeder;

/**
 * Sumber anggaran referensi untuk inventaris dan pengadaan (RS/pemda).
 */
class MasterSumberAnggaranSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'nama_anggaran' => 'APBD',
                'keterangan' => 'Anggaran Pendapatan dan Belanja Daerah.',
            ],
            [
                'nama_anggaran' => 'APBN',
                'keterangan' => 'Anggaran Pendapatan dan Belanja Negara.',
            ],
            [
                'nama_anggaran' => 'BLUD',
                'keterangan' => 'Pendapatan Badan Layanan Umum Daerah.',
            ],
            [
                'nama_anggaran' => 'Hibah',
                'keterangan' => 'Hibah pemerintah atau pihak ketiga.',
            ],
        ];

        foreach ($items as $row) {
            $nama = trim($row['nama_anggaran']);
            if ($nama === '') {
                continue;
            }
            MasterSumberAnggaran::firstOrCreate(
                ['nama_anggaran' => $nama],
                ['keterangan' => $row['keterangan'] ?? null]
            );
        }
    }
}
