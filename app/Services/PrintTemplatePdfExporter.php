<?php

namespace App\Services;

use App\Models\PrintTemplate;
use Dompdf\Dompdf;
use Dompdf\Options;
use RuntimeException;

class PrintTemplatePdfExporter
{
    public static function isAvailable(): bool
    {
        return class_exists('Dompdf\\Dompdf');
    }

    /**
     * @return array{0: string, 1: string} binary, filename
     */
    public static function renderBinary(PrintTemplate $template, string $fullHtml): array
    {
        if (! self::isAvailable()) {
            throw new RuntimeException('Paket dompdf/dompdf belum terpasang. Jalankan: composer update');
        }

        $options = new Options;
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($fullHtml, 'UTF-8');
        self::applyDompdfPaper($dompdf, $template);
        $dompdf->render();

        $filename = 'surat-'.preg_replace('/[^a-z0-9._-]+/i', '-', $template->key).'.pdf';

        return [$dompdf->output(), $filename];
    }

    public static function wrapHtmlForPdf(string $innerHtml, PrintTemplate $template): string
    {
        $css = PrintTemplatePaper::printStylesCss($template);

        return '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><style>'.$css.'</style></head><body><div class="print-root">'.$innerHtml.'</div></body></html>';
    }

    /**
     * @param  Dompdf  $dompdf
     */
    private static function applyDompdfPaper($dompdf, PrintTemplate $template): void
    {
        $paper = PrintTemplatePaper::paperSize($template);
        $orient = PrintTemplatePaper::orientation($template);

        if ($paper === 'a4') {
            $dompdf->setPaper('a4', $orient);

            return;
        }

        $wMm = $orient === 'landscape' ? 330.0 : 210.0;
        $hMm = $orient === 'landscape' ? 210.0 : 330.0;
        $wPt = $wMm * 72 / 25.4;
        $hPt = $hMm * 72 / 25.4;
        $dompdf->setPaper([0.0, 0.0, $wPt, $hPt], $orient);
    }
}
