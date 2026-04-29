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
                'nama_anggaran' => 'PNBP',
                'keterangan' => 'Penerimaan Negara Bukan Pajak.',
            ],
            [
                'nama_anggaran' => 'DAK',
                'keterangan' => 'Dana Alokasi Khusus.',
            ],
            [
                'nama_anggaran' => 'DAU',
                'keterangan' => 'Dana Alokasi Umum.',
            ],
            [
                'nama_anggaran' => 'Hibah',
                'keterangan' => 'Hibah pemerintah atau pihak ketiga.',
            ],
            [
                'nama_anggaran' => 'Pinjaman luar negeri',
                'keterangan' => 'Pinjaman/hibah luar negeri untuk pembangunan atau pengadaan.',
            ],
            [
                'nama_anggaran' => 'Swasta/kemitraan',
                'keterangan' => 'Kerja sama atau pendanaan non-APBN/APBD.',
            ],
            [
                'nama_anggaran' => 'Dana Diklat / BOK',
                'keterangan' => 'Dana bantuan operasional kesehatan atau pendidikan (sesuai peruntukan).',
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
