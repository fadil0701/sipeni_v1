<?php

namespace App\Console\Commands;

use App\Models\MasterDataBarang;
use App\Models\MasterDataBarangPermendagri;
use Illuminate\Console\Command;

class BackfillPermendagriMapping extends Command
{
    protected $signature = 'permendagri:backfill-mapping
        {--dry-run : Simulasi tanpa menyimpan}
        {--overwrite : Timpa mapping yang sudah ada}
        {--only-missing : Hanya proses yang belum punya mapping}';

    protected $description = 'Backfill mapping Permendagri 108 dari struktur master barang yang sudah ada';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $overwrite = (bool) $this->option('overwrite');
        $onlyMissing = (bool) $this->option('only-missing');

        $query = MasterDataBarang::query()
            ->with('subjenisBarang')
            ->with('permendagriMapping')
            ->orderBy('id_data_barang');

        $total = 0;
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $needsReview = 0;

        $query->chunkById(200, function ($rows) use ($dryRun, $overwrite, $onlyMissing, &$total, &$created, &$updated, &$skipped, &$needsReview) {
            foreach ($rows as $dataBarang) {
                $total++;
                $existing = $dataBarang->permendagriMapping;

                if ($onlyMissing && $existing) {
                    $skipped++;
                    continue;
                }

                if ($existing && ! $overwrite) {
                    $skipped++;
                    continue;
                }

                $kodeSubjenis = (string) optional($dataBarang->subjenisBarang)->kode_subjenis_barang;
                $segments = $this->extractSegments($kodeSubjenis);

                $payload = [
                    'id_data_barang' => $dataBarang->id_data_barang,
                    'kode_akun' => '1',
                    'kode_kelompok' => '3',
                    'kode_jenis_108' => $segments[0],
                    'kode_objek' => $segments[1],
                    'kode_rincian_objek' => $segments[2],
                    'kode_sub_rincian_objek' => $segments[3],
                    'kode_sub_sub_rincian_objek' => $segments[4],
                    'sumber_mapping' => 'AUTO_SYSTEM',
                    'status_validasi' => 'DRAFT',
                    'catatan' => $kodeSubjenis !== ''
                        ? "Auto backfill dari kode_subjenis_barang: {$kodeSubjenis}"
                        : 'Auto backfill tanpa kode_subjenis_barang (pakai default segmen).',
                ];

                $payload['kode_barang_108'] = implode('.', [
                    $payload['kode_akun'],
                    $payload['kode_kelompok'],
                    $payload['kode_jenis_108'],
                    $payload['kode_objek'],
                    $payload['kode_rincian_objek'],
                    $payload['kode_sub_rincian_objek'],
                    $payload['kode_sub_sub_rincian_objek'],
                ]);

                $hasPlaceholderSegment = in_array($payload['kode_jenis_108'], ['00'], true)
                    || in_array($payload['kode_objek'], ['00'], true)
                    || in_array($payload['kode_rincian_objek'], ['00'], true)
                    || in_array($payload['kode_sub_rincian_objek'], ['000'], true)
                    || in_array($payload['kode_sub_sub_rincian_objek'], ['000'], true);

                if ($hasPlaceholderSegment) {
                    $payload['status_validasi'] = 'REVIEW';
                    $payload['catatan'] = trim(($payload['catatan'] ?? '').' Segmen default 00/000 terdeteksi, perlu validasi manual.');
                    $needsReview++;
                }

                if ($dryRun) {
                    $this->line("DRY-RUN {$dataBarang->kode_data_barang}: {$payload['kode_barang_108']}");
                    $existing ? $updated++ : $created++;
                    continue;
                }

                MasterDataBarangPermendagri::updateOrCreate(
                    ['id_data_barang' => $dataBarang->id_data_barang],
                    $payload
                );

                $existing ? $updated++ : $created++;
            }
        }, 'id_data_barang', 'id_data_barang');

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Data Barang Diproses', $total],
                ['Created', $created],
                ['Updated', $updated],
                ['Skipped', $skipped],
                ['Needs Review', $needsReview],
                ['Dry Run', $dryRun ? 'yes' : 'no'],
            ]
        );

        return self::SUCCESS;
    }

    /**
     * @return array{0:string,1:string,2:string,3:string,4:string}
     */
    private function extractSegments(string $kodeSubjenis): array
    {
        preg_match_all('/\d+/', $kodeSubjenis, $matches);
        $numbers = $matches[0] ?? [];

        // Struktur fallback: jenis.objek.rincian.sub_rincian.sub_sub_rincian
        $defaults = ['00', '00', '00', '000', '000'];
        foreach ($defaults as $index => $defaultValue) {
            if (! isset($numbers[$index])) {
                $numbers[$index] = $defaultValue;
                continue;
            }

            $width = strlen($defaultValue);
            $numbers[$index] = str_pad((string) $numbers[$index], $width, '0', STR_PAD_LEFT);
        }

        return [
            (string) $numbers[0],
            (string) $numbers[1],
            (string) $numbers[2],
            (string) $numbers[3],
            (string) $numbers[4],
        ];
    }
}