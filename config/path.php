<?php

$explicitPrefix = env('APP_ROUTE_PREFIX');

if (in_array($explicitPrefix, ['false', 'none', '0'], true)) {
    $routePrefix = '';
} elseif ($explicitPrefix !== null && $explicitPrefix !== '' && $explicitPrefix !== 'auto') {
    $routePrefix = trim((string) $explicitPrefix, '/');
} else {
    $routePrefix = trim((string) parse_url(env('APP_URL', 'http://localhost'), PHP_URL_PATH), '/');
}

return [
    'route_prefix' => $routePrefix,
];
