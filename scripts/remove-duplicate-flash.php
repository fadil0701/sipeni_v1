<?php

$skip = [
    'resources/views/components/ui/flash-messages.blade.php',
    'resources/views/admin/partials/flash.blade.php',
    'resources/views/layouts/app.blade.php',
];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(__DIR__ . '/../resources/views')
);

$changed = [];

foreach ($iterator as $file) {
    if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
        continue;
    }

    $path = str_replace('\\', '/', $file->getPathname());
    $rel = preg_replace('#^.+?resources/views/#', 'resources/views/', $path);

    if (in_array($rel, $skip, true)) {
        continue;
    }

    if (str_contains($rel, 'asset/kartu-inventaris-ruangan/document-unit')) {
        continue;
    }

    $content = file_get_contents($path);
    $original = $content;

    $content = preg_replace('/^[ \t]*@include\([\'"]admin\.partials\.flash[\'"]\)[ \t]*\r?\n/m', '', $content);

    $patterns = [
        '/\r?\n[ \t]*@if\(session\([\'"]success[\'"]\)\)\s*\r?\n.*?\r?\n[ \t]*@endif\r?\n/s',
        '/\r?\n[ \t]*@if\(session\([\'"]error[\'"]\)\)\s*\r?\n.*?\r?\n[ \t]*@endif\r?\n/s',
        '/\r?\n[ \t]*@if\(session\([\'"]info[\'"]\)\)\s*\r?\n.*?\r?\n[ \t]*@endif\r?\n/s',
        '/\r?\n[ \t]*@if\(session\([\'"]warning[\'"]\)\)\s*\r?\n.*?\r?\n[ \t]*@endif\r?\n/s',
    ];

    foreach ($patterns as $pattern) {
        $content = preg_replace($pattern, "\n", $content);
    }

    if ($content !== $original) {
        file_put_contents($path, $content);
        $changed[] = $rel;
    }
}

echo 'Updated ' . count($changed) . " files:\n";
foreach ($changed as $f) {
    echo "  - {$f}\n";
}
