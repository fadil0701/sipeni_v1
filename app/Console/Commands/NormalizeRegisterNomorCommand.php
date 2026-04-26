<?php

namespace App\Console\Commands;

use App\Models\RegisterAset;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class NormalizeRegisterNomorCommand extends Command
{
    protected $signature = 'register-aset:normalize-nomor {--dry-run : Tampilkan perubahan tanpa menyimpan}';

    protected $description = 'Normalisasi nomor_register lama agar tidak sama dengan kode_register inventory item';

    public function handle(): int
    {
        if (!Schema::hasColumn('register_aset', 'id_item')) {
            $this->error('Kolom register_aset.id_item belum tersedia. Jalankan migrasi terlebih dahulu.');
            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $this->info($dryRun
            ? 'Mode DRY RUN: tidak ada data yang diubah.'
            : 'Memulai normalisasi nomor register...'
        );

        $targets = RegisterAset::query()
            ->select('register_aset.*')
            ->join('inventory_item', 'inventory_item.id_item', '=', 'register_aset.id_item')
            ->whereColumn('register_aset.nomor_register', 'inventory_item.kode_register')
            ->orderBy('register_aset.id_register_aset')
            ->get();

        if ($targets->isEmpty()) {
            $this->info('Tidak ada data yang perlu dinormalisasi.');
            return self::SUCCESS;
        }

        $this->info("Ditemukan {$targets->count()} data untuk dinormalisasi.");

        $updated = 0;
        $reservedNomor = [];
        foreach ($targets as $register) {
            $old = (string) $register->nomor_register;
            $new = $this->generateNomorRegister(
                (int) $register->id_unit_kerja,
                $register->id_ruangan ? (int) $register->id_ruangan : null,
                $register->tanggal_perolehan ? (string) $register->tanggal_perolehan : null,
                (int) $register->id_register_aset,
                $reservedNomor
            );

            if ($old === $new) {
                continue;
            }

            $this->line("ID {$register->id_register_aset}: {$old} -> {$new}");
            if (!$dryRun) {
                $register->nomor_register = $new;
                $register->save();
            }
            $reservedNomor[] = $new;
            $updated++;
        }

        $this->newLine();
        $this->info($dryRun
            ? "DRY RUN selesai. Calon perubahan: {$updated}"
            : "Normalisasi selesai. Data diubah: {$updated}"
        );

        return self::SUCCESS;
    }

    private function generateNomorRegister(
        int $idUnitKerja,
        ?int $idRuangan,
        ?string $tanggalPerolehan,
        int $excludeId,
        array $reservedNomor = []
    ): string {
        $tahun = $tanggalPerolehan ? date('Y', strtotime($tanggalPerolehan)) : date('Y');
        $prefix = $idRuangan ? sprintf('%03d/%03d', $idUnitKerja, $idRuangan) : sprintf('%03d', $idUnitKerja);

        $lastRegister = RegisterAset::query()
            ->where('id_unit_kerja', $idUnitKerja)
            ->where('id_register_aset', '!=', $excludeId)
            ->where(function ($q) use ($idRuangan) {
                if ($idRuangan) {
                    $q->where('id_ruangan', $idRuangan);
                } else {
                    $q->whereNull('id_ruangan');
                }
            })
            ->whereYear('tanggal_perolehan', $tahun)
            ->where('nomor_register', 'like', $prefix . '/%')
            ->orderByRaw('CAST(SUBSTRING_INDEX(nomor_register, "/", -1) AS UNSIGNED) DESC')
            ->first();

        $urut = 1;
        if ($lastRegister) {
            $parts = explode('/', (string) $lastRegister->nomor_register);
            $urut = ((int) end($parts)) + 1;
        }

        $candidate = sprintf('%s/%04d', $prefix, $urut);
        while (RegisterAset::query()
            ->where('nomor_register', $candidate)
            ->where('id_register_aset', '!=', $excludeId)
            ->exists() || in_array($candidate, $reservedNomor, true)) {
            $urut++;
            $candidate = sprintf('%s/%04d', $prefix, $urut);
        }

        return $candidate;
    }
}
