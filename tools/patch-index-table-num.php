<?php
/**
 * Tambah kolom nomor urut (x-table.num-th / num-td) + data-pagination-base
 * pada file index blade yang punya pola: satu <table> utama + @forelse($plural as $singular)
 *
 * Jalankan dari root project: php tools/patch-index-table-num.php
 */

$files = [
    ['path' => 'resources/views/master-data/kode-barang/index.blade.php', 'paginator' => '$kodeBarangs', 'firstTh' => 'Kode Barang', 'colspan' => 4],
    ['path' => 'resources/views/master-data/subjenis-barang/index.blade.php', 'paginator' => '$subjenisBarangs', 'firstTh' => 'Kode Subjenis', 'colspan' => 4],
    ['path' => 'resources/views/master-data/aset/index.blade.php', 'paginator' => '$asets', 'firstTh' => 'Nama Aset', 'colspan' => 2],
    ['path' => 'resources/views/master-data/sumber-anggaran/index.blade.php', 'paginator' => '$sumberAnggarans', 'firstTh' => 'Nama Anggaran', 'colspan' => 2],
    ['path' => 'resources/views/master-data/data-barang/index.blade.php', 'paginator' => '$dataBarangs', 'firstTh' => 'Kode Barang', 'colspan' => 5],
];

$root = dirname(__DIR__);

foreach ($files as $job) {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $job['path']);
    if (! is_file($path)) {
        fwrite(STDERR, "Missing: {$job['path']}\n");
        continue;
    }
    $c = file_get_contents($path);
    $p = $job['paginator'];
    $first = $job['firstTh'];
    $col = (int) $job['colspan'];

    if (str_contains($c, '<x-table.num-th')) {
        echo "Skip (already): {$job['path']}\n";
        continue;
    }

    $c = preg_replace(
        '/<table class="min-w-full divide-y divide-gray-200">/',
        "<table\n            class=\"min-w-full divide-y divide-gray-200\"\n            @if({$p} instanceof \\Illuminate\\Contracts\\Pagination\\Paginator) data-pagination-base=\"{{ {$p}->firstItem() }}\" @endif\n        >",
        $c,
        1
    );

    $c = preg_replace(
        '/(<thead class="bg-gray-50">\s*<tr>\s*)<th class="px-6 py-3[^"]*">' . preg_quote($first, '/') . '<\/th>/',
        '$1<x-table.num-th />' . "\n                    " . '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">' . $first . '</th>',
        $c,
        1
    );

    $needle = '@forelse(' . $p . ' as $';
    $pos = strpos($c, $needle);
    if ($pos === false) {
        fwrite(STDERR, "No forelse for {$job['path']}\n");
        continue;
    }
    $asPos = strpos($c, ' as $', $pos) + 5;
    $endVar = strpos($c, ')', $asPos);
    $itemVar = substr($c, $asPos, $endVar - $asPos);

    $trNeedle = "@forelse({$p} as {$itemVar})\n                    <tr class=\"hover:bg-gray-50 transition-colors\">\n                        <td class=\"px-6 py-4";
    $trRepl = "@forelse({$p} as {$itemVar})\n                    <tr class=\"hover:bg-gray-50 transition-colors\">\n                        <x-table.num-td :paginator=\"{$p}\" />\n                        <td class=\"px-6 py-4";
    if (! str_contains($c, $trNeedle)) {
        $trNeedle = "@forelse({$p} as {$itemVar})\r\n                    <tr class=\"hover:bg-gray-50 transition-colors\">\r\n                        <td class=\"px-6 py-4";
        $trRepl = "@forelse({$p} as {$itemVar})\r\n                    <tr class=\"hover:bg-gray-50 transition-colors\">\r\n                        <x-table.num-td :paginator=\"{$p}\" />\r\n                        <td class=\"px-6 py-4";
    }
    $c = str_replace($trNeedle, $trRepl, $c);

    $c = preg_replace('/<td colspan="' . $col . '" class="px-6 py-12 text-center">/', '<td colspan="' . ($col + 1) . '" class="px-6 py-12 text-center">', $c, 1);

    file_put_contents($path, $c);
    echo "Patched: {$job['path']}\n";
}
