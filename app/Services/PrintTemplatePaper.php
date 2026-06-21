<?php

namespace App\Services;

use App\Models\PrintTemplate;

/**
 * Ukuran kertas A4 / F4 (folio Indonesia) dan orientasi untuk @page cetak & Dompdf.
 */
class PrintTemplatePaper
{
    public static function paperSize(PrintTemplate $template): string
    {
        $v = strtolower((string) ($template->paper_size ?? 'a4'));

        return in_array($v, ['a4', 'f4'], true) ? $v : 'a4';
    }

    public static function orientation(PrintTemplate $template): string
    {
        $v = strtolower((string) ($template->orientation ?? 'portrait'));

        return in_array($v, ['portrait', 'landscape'], true) ? $v : 'portrait';
    }

    public static function marginMm(PrintTemplate $template): int
    {
        $m = (int) ($template->print_margin_mm ?? 12);
        if ($m < 5) {
            return 5;
        }
        if ($m > 30) {
            return 30;
        }

        return $m;
    }

    /**
     * CSS @page + body untuk pratinjau browser dan pembungkus PDF.
     */
    public static function printStylesCss(?PrintTemplate $template = null): string
    {
        if ($template === null) {
            return self::defaultPrintStylesCss();
        }

        $paper = self::paperSize($template);
        $orient = self::orientation($template);
        $mm = self::marginMm($template);

        $sizeDecl = $paper === 'f4'
            ? ($orient === 'landscape' ? '330mm 210mm' : '210mm 330mm')
            : ($orient === 'landscape' ? 'A4 landscape' : 'A4 portrait');

        $m = $mm.'mm';
        $screenMax = $paper === 'f4' ? '210mm' : '210mm';

        return <<<CSS
@page { size: {$sizeDecl}; margin: {$m}; }
@media print {
  html, body { margin: 0 !important; padding: 0 !important; }
  .no-print { display: none !important; }
}
@media screen {
  .print-root { max-width: {$screenMax}; margin: 0 auto; padding: {$m}; background: #fff; box-shadow: 0 0 0 1px #e5e7eb; min-height: 80vh; }
}
body { font-family: 'Times New Roman', Times, 'Liberation Serif', Georgia, serif; }
CSS;
    }

    public static function defaultPrintStylesCss(): string
    {
        return <<<'CSS'
@page { size: A4 portrait; margin: 12mm; }
@media print {
  html, body { margin: 0 !important; padding: 0 !important; }
  .no-print { display: none !important; }
}
@media screen {
  .print-root { max-width: 210mm; margin: 0 auto; padding: 12mm; background: #fff; box-shadow: 0 0 0 1px #e5e7eb; min-height: 80vh; }
}
CSS;
    }
}
