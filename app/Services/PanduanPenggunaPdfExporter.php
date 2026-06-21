<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Str;
use RuntimeException;

class PanduanPenggunaPdfExporter
{
    public const SOURCE_DIR = 'docs/panduan-pengguna';

    public const OUTPUT_DIR = 'docs/panduan-pengguna/pdf';

    /** @var list<string> */
    public const COMBINED_ORDER = [
        'README.md',
        '01-pengenalan-dan-login.md',
        '02-modul-dan-fitur.md',
        '03-alur-kerja-utama.md',
        '04-matrik-akses-role.md',
        'per-role/README.md',
        'per-role/super_administrator.md',
        'per-role/kepala_pusat.md',
        'per-role/kasubbag_tu.md',
        'per-role/kepala_unit.md',
        'per-role/admin_unit.md',
        'per-role/perencana.md',
        'per-role/pengadaan.md',
        'per-role/keuangan.md',
        'per-role/pptk_apbd.md',
        'per-role/pptk_blud.md',
        'per-role/pengurus_barang.md',
        'per-role/admin_gudang_pusat.md',
        'per-role/admin_gudang_aset.md',
        'per-role/admin_gudang_persediaan.md',
        'per-role/admin_gudang_farmasi.md',
        'per-role/admin-dan-administrator.md',
    ];

    public static function isAvailable(): bool
    {
        return class_exists(Dompdf::class);
    }

    /**
     * @return array{0: string, 1: string} binary, filename
     */
    public static function renderBinary(string $fullHtml, string $filename): array
    {
        if (! self::isAvailable()) {
            throw new RuntimeException('Paket dompdf/dompdf belum terpasang.');
        }

        $options = new Options;
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($fullHtml, 'UTF-8');
        $dompdf->setPaper('a4', 'portrait');
        $dompdf->render();

        return [$dompdf->output(), $filename];
    }

    public static function markdownToHtml(string $markdown): string
    {
        return Str::markdown($markdown);
    }

    public static function wrapHtml(string $title, string $bodyHtml): string
    {
        $title = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $css = self::documentStylesCss();

        return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>{$title}</title>
<style>{$css}</style>
</head>
<body>
<div class="doc-root">
{$bodyHtml}
</div>
</body>
</html>
HTML;
    }

    public static function htmlFromMarkdownFile(string $absolutePath): string
    {
        if (! is_readable($absolutePath)) {
            throw new RuntimeException("File tidak ditemukan: {$absolutePath}");
        }

        $markdown = (string) file_get_contents($absolutePath);

        return self::markdownToHtml($markdown);
    }

    public static function titleFromMarkdown(string $markdown): string
    {
        if (preg_match('/^#\s+(.+)$/m', $markdown, $matches)) {
            return trim($matches[1]);
        }

        return 'Panduan Pengguna SI-MANTIK';
    }

    /**
     * @return array{0: string, 1: string} binary, filename
     */
    public static function exportMarkdownFile(string $absolutePath, ?string $outputFilename = null): array
    {
        $markdown = (string) file_get_contents($absolutePath);
        $title = self::titleFromMarkdown($markdown);
        $body = self::markdownToHtml($markdown);
        $html = self::wrapHtml($title, $body);

        if ($outputFilename === null) {
            $outputFilename = self::pdfFilenameFromMarkdownPath($absolutePath);
        }

        return self::renderBinary($html, $outputFilename);
    }

    /**
     * @return array{0: string, 1: string} binary, filename
     */
    public static function exportCombined(string $basePath): array
    {
        $sections = [];

        foreach (self::COMBINED_ORDER as $relativePath) {
            $absolutePath = $basePath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            if (! is_readable($absolutePath)) {
                continue;
            }

            $html = self::htmlFromMarkdownFile($absolutePath);
            $sections[] = '<section class="chapter">'.$html.'</section>';
        }

        if ($sections === []) {
            throw new RuntimeException('Tidak ada file panduan yang dapat digabung.');
        }

        $body = implode("\n", $sections);
        $html = self::wrapHtml('Panduan Pengguna SI-MANTIK — Lengkap', $body);

        return self::renderBinary($html, 'panduan-pengguna-simantik-lengkap.pdf');
    }

    public static function pdfFilenameFromMarkdownPath(string $absolutePath): string
    {
        $base = basename($absolutePath, '.md');
        $slug = Str::slug($base);

        if ($slug === '' || $slug === 'readme') {
            $parent = basename(dirname($absolutePath));
            if ($parent !== 'panduan-pengguna') {
                $slug = Str::slug($parent.'-'.$base);
            } else {
                $slug = 'panduan-pengguna-index';
            }
        }

        return $slug.'.pdf';
    }

    public static function documentStylesCss(): string
    {
        return <<<'CSS'
@page { margin: 18mm 16mm 20mm 16mm; }
body {
  font-family: DejaVu Sans, sans-serif;
  font-size: 10.5pt;
  line-height: 1.45;
  color: #1f2937;
}
.doc-root { max-width: 100%; }
h1 {
  font-size: 18pt;
  color: #1e3a8a;
  border-bottom: 2px solid #2563eb;
  padding-bottom: 6px;
  margin: 0 0 14px;
  page-break-after: avoid;
}
h2 {
  font-size: 13pt;
  color: #1e40af;
  margin: 18px 0 8px;
  page-break-after: avoid;
}
h3 {
  font-size: 11pt;
  color: #1d4ed8;
  margin: 14px 0 6px;
  page-break-after: avoid;
}
p { margin: 0 0 8px; }
ul, ol { margin: 0 0 10px 18px; padding: 0; }
li { margin-bottom: 4px; }
a { color: #2563eb; text-decoration: none; }
blockquote {
  margin: 10px 0;
  padding: 8px 12px;
  border-left: 4px solid #93c5fd;
  background: #eff6ff;
  color: #1e3a8a;
}
code {
  font-family: DejaVu Sans Mono, monospace;
  font-size: 9pt;
  background: #f3f4f6;
  padding: 1px 4px;
  border-radius: 3px;
}
pre {
  background: #f3f4f6;
  padding: 10px;
  font-size: 9pt;
  overflow-wrap: anywhere;
  white-space: pre-wrap;
}
table {
  width: 100%;
  border-collapse: collapse;
  margin: 10px 0 14px;
  font-size: 9pt;
  page-break-inside: avoid;
}
th, td {
  border: 1px solid #d1d5db;
  padding: 5px 7px;
  vertical-align: top;
  text-align: left;
}
th {
  background: #dbeafe;
  color: #1e3a8a;
  font-weight: bold;
}
tr:nth-child(even) td { background: #f9fafb; }
hr {
  border: none;
  border-top: 1px solid #e5e7eb;
  margin: 16px 0;
}
section.chapter {
  page-break-before: always;
}
section.chapter:first-child {
  page-break-before: auto;
}
.cover-meta {
  margin-top: 24px;
  font-size: 10pt;
  color: #4b5563;
}
CSS;
    }
}
