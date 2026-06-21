<?php

namespace App\Console\Commands;

use App\Services\PanduanPenggunaPdfExporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExportPanduanPenggunaPdfCommand extends Command
{
    protected $signature = 'panduan:export-pdf
                            {--combined-only : Hanya buat PDF gabungan lengkap}
                            {--file= : Ekspor satu file markdown (path relatif dari docs/panduan-pengguna)}';

    protected $description = 'Ekspor panduan pengguna Markdown ke PDF (docs/panduan-pengguna/pdf/)';

    public function handle(): int
    {
        if (! PanduanPenggunaPdfExporter::isAvailable()) {
            $this->error('dompdf/dompdf belum terpasang. Jalankan: composer install');

            return self::FAILURE;
        }

        $basePath = base_path(PanduanPenggunaPdfExporter::SOURCE_DIR);
        $outputDir = base_path(PanduanPenggunaPdfExporter::OUTPUT_DIR);

        if (! File::isDirectory($basePath)) {
            $this->error('Folder panduan tidak ditemukan: '.$basePath);

            return self::FAILURE;
        }

        File::ensureDirectoryExists($outputDir);

        $singleFile = $this->option('file');
        if (is_string($singleFile) && $singleFile !== '') {
            return $this->exportSingle($basePath, $outputDir, $singleFile);
        }

        $combinedOnly = (bool) $this->option('combined-only');

        if (! $combinedOnly) {
            $this->exportIndividualFiles($basePath, $outputDir);
        }

        $this->exportCombined($basePath, $outputDir);

        $this->newLine();
        $this->info('Selesai. PDF tersimpan di: '.PanduanPenggunaPdfExporter::OUTPUT_DIR.'/');

        return self::SUCCESS;
    }

    private function exportSingle(string $basePath, string $outputDir, string $relativePath): int
    {
        $relativePath = str_replace('\\', '/', ltrim($relativePath, '/'));
        $absolutePath = $basePath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

        if (! is_readable($absolutePath)) {
            $this->error('File tidak ditemukan: '.$relativePath);

            return self::FAILURE;
        }

        try {
            [$binary, $filename] = PanduanPenggunaPdfExporter::exportMarkdownFile($absolutePath);
            file_put_contents($outputDir.DIRECTORY_SEPARATOR.$filename, $binary);
            $this->info('✓ '.$filename);
        } catch (\Throwable $e) {
            $this->error('Gagal: '.$e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function exportIndividualFiles(string $basePath, string $outputDir): void
    {
        $this->info('Mengekspor PDF per dokumen...');

        foreach (PanduanPenggunaPdfExporter::COMBINED_ORDER as $relativePath) {
            $absolutePath = $basePath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            if (! is_readable($absolutePath)) {
                $this->warn('  Lewati (tidak ada): '.$relativePath);

                continue;
            }

            try {
                [$binary, $filename] = PanduanPenggunaPdfExporter::exportMarkdownFile($absolutePath);
                file_put_contents($outputDir.DIRECTORY_SEPARATOR.$filename, $binary);
                $this->line('  ✓ '.$filename);
            } catch (\Throwable $e) {
                $this->error('  ✗ '.$relativePath.': '.$e->getMessage());
            }
        }
    }

    private function exportCombined(string $basePath, string $outputDir): void
    {
        $this->info('Mengekspor PDF gabungan lengkap...');

        try {
            [$binary, $filename] = PanduanPenggunaPdfExporter::exportCombined($basePath);
            file_put_contents($outputDir.DIRECTORY_SEPARATOR.$filename, $binary);
            $this->line('  ✓ '.$filename);
        } catch (\Throwable $e) {
            $this->error('  ✗ Gagal PDF gabungan: '.$e->getMessage());
        }
    }
}
