<?php

/**
 * Dev server launcher — skips Laravel Pail when pcntl is unavailable (Windows).
 *
 * Usage: composer run dev  →  @php scripts/dev.php
 */

declare(strict_types=1);

$hasPcntl = function_exists('pcntl_fork');

$processes = [
    'php artisan serve',
    'php artisan queue:listen --tries=1',
    'npm run dev',
];

if ($hasPcntl) {
    array_splice($processes, 2, 0, ['php artisan pail --timeout=0']);
}

$names = $hasPcntl ? 'server,queue,logs,vite' : 'server,queue,vite';
$colors = $hasPcntl
    ? '#93c5fd,#c4b5fd,#fb7185,#fdba74'
    : '#93c5fd,#c4b5fd,#fdba74';

$quoted = array_map(static fn (string $p): string => escapeshellarg($p), $processes);

$cmd = sprintf(
    'npx concurrently -c %s %s --names=%s',
    escapeshellarg($colors),
    implode(' ', $quoted),
    $names
);

if (! $hasPcntl) {
    fwrite(STDERR, "[dev] pcntl tidak tersedia (Windows) — Pail dilewati. Log: storage/logs/laravel.log\n");
}

fwrite(STDERR, "[dev] Vite port 5173 sibuk? Vite otomatis coba port lain (strictPort=false).\n");
fwrite(STDERR, "[dev] Bebaskan 5173: netstat -ano | findstr :5173  lalu taskkill /PID <pid> /F\n");

passthru($cmd, $exitCode);
exit($exitCode);
