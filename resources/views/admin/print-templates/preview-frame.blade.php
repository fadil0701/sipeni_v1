@php
    $__printCss = isset($printTemplate)
        ? \App\Services\PrintTemplatePaper::printStylesCss($printTemplate)
        : \App\Services\PrintTemplatePaper::defaultPrintStylesCss();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <style>
        body { margin: 0; font-family: system-ui, sans-serif; }
        {!! $__printCss !!}
        @media print {
            .no-print { display: none !important; }
        }
        .no-print { padding: 10px 12px; background: #f3f4f6; border-bottom: 1px solid #e5e7eb; display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .no-print button, .no-print a {
            font-size: 14px; padding: 6px 12px; border-radius: 6px; cursor: pointer; text-decoration: none; border: 1px solid #d1d5db; background: #fff; color: #111827;
        }
        .no-print button { background: #2563eb; color: #fff; border-color: #2563eb; }
    </style>
</head>
<body>
    <div class="no-print">
        <button type="button" onclick="window.print()">Cetak</button>
        @if(! empty($allowPdfExport) && isset($printTemplate))
            <a href="{{ route('admin.print-templates.pdf', $printTemplate) }}">Unduh PDF (contoh)</a>
        @endif
        <a href="javascript:window.close()">Tutup jendela</a>
    </div>
    <div class="print-root">
        {!! $html !!}
    </div>
</body>
</html>
