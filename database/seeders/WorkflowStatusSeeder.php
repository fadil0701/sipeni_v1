<?php

namespace Database\Seeders;

use App\Models\WorkflowStatus;
use Illuminate\Database\Seeder;

class WorkflowStatusSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['kode_status' => 'draft', 'nama_status' => 'Draft', 'urutan' => 1],
            ['kode_status' => 'diajukan', 'nama_status' => 'Diajukan', 'urutan' => 2],
            ['kode_status' => 'mengetahui', 'nama_status' => 'Mengetahui', 'urutan' => 3],
            ['kode_status' => 'verifikasi', 'nama_status' => 'Verifikasi', 'urutan' => 4],
            ['kode_status' => 'proses', 'nama_status' => 'Proses', 'urutan' => 5],
            ['kode_status' => 'selesai', 'nama_status' => 'Selesai', 'urutan' => 6],
        ];

        foreach ($defaults as $row) {
            WorkflowStatus::query()->updateOrCreate(
                ['kode_status' => $row['kode_status']],
                $row
            );
        }
    }
}
