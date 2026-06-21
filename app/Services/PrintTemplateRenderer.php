<?php

namespace App\Services;

use App\Models\PrintTemplate;

/**
 * Mengganti placeholder di HTML template:
 * - {{{kunci}}} : HTML mentah (tanpa escape); hanya untuk nilai string dari payload tepercaya (mis. baris tabel).
 * - {{kunci}} atau {{nested.key}} : teks di-escape (e()).
 */
class PrintTemplateRenderer
{
    private const RAW_PLACEHOLDER_PATTERN = '/\{\{\{\s*([a-zA-Z_][a-zA-Z0-9_.]*)\s*\}\}\}/';

    private const PLACEHOLDER_PATTERN = '/\{\{\s*([a-zA-Z_][a-zA-Z0-9_.]*)\s*\}\}/';

    /**
     * Gabungan HTML header + isi untuk ekstraksi placeholder dan render.
     */
    public static function mergedTemplateString(PrintTemplate $template): string
    {
        $h = trim((string) ($template->header_html ?? ''));
        $b = $template->compiledBody();

        return $h === '' ? $b : $h."\n".$b;
    }

    public static function render(PrintTemplate $template, array $data = []): string
    {
        return self::renderString(self::mergedTemplateString($template), $data);
    }

    public static function renderString(string $body, array $data = []): string
    {
        $body = self::replaceRawPlaceholders($body, $data);

        return self::replaceEscapedPlaceholders($body, $data);
    }

    private static function replaceRawPlaceholders(string $body, array $data): string
    {
        return preg_replace_callback(self::RAW_PLACEHOLDER_PATTERN, function (array $m) use ($data) {
            $value = data_get($data, $m[1]);
            if ($value === null) {
                return '';
            }
            if (is_string($value)) {
                return self::sanitizeRawHtml($value);
            }
            if (is_scalar($value) || $value instanceof \Stringable) {
                return (string) $value;
            }

            $json = json_encode($value, JSON_UNESCAPED_UNICODE);

            return $json !== false ? $json : '';
        }, $body) ?? $body;
    }

    private static function replaceEscapedPlaceholders(string $body, array $data): string
    {
        return preg_replace_callback(self::PLACEHOLDER_PATTERN, function (array $m) use ($data) {
            $path = $m[1];
            $value = data_get($data, $path);

            if ($value === null) {
                return '';
            }
            if (is_scalar($value) || $value instanceof \Stringable) {
                return e((string) $value);
            }

            $json = json_encode($value, JSON_UNESCAPED_UNICODE);

            return e($json !== false ? $json : '');
        }, $body) ?? $body;
    }

    /**
     * @return array{raw: array<int, string>, escaped: array<int, string>}
     */
    public static function extractPlaceholderGroups(string $body): array
    {
        $rawKeys = [];
        if (preg_match_all(self::RAW_PLACEHOLDER_PATTERN, $body, $m1)) {
            $rawKeys = array_values(array_unique($m1[1]));
        }
        $escapedKeys = [];
        if (preg_match_all(self::PLACEHOLDER_PATTERN, $body, $m2)) {
            $escapedKeys = array_values(array_unique($m2[1]));
        }

        return [
            'raw' => $rawKeys,
            'escaped' => $escapedKeys,
        ];
    }

    public static function extractPlaceholders(string $body): array
    {
        $g = self::extractPlaceholderGroups($body);

        return array_values(array_unique(array_merge($g['raw'], $g['escaped'])));
    }

    /**
     * Daftar key template → variabel payload bawaan aplikasi (untuk chip / dokumentasi).
     *
     * @return array<string, array{raw: array<int, string>, escaped: array<int, string>}>
     */
    public static function allKnownVariableGroupsByTemplateKey(): array
    {
        $map = [];
        $providers = config('print_templates.variable_providers', []);
        foreach ($providers as $key => $class) {
            if (! is_string($key) || ! is_string($class) || ! class_exists($class)) {
                continue;
            }
            if (! method_exists($class, 'variableGroups')) {
                continue;
            }
            /** @var callable $cb */
            $cb = [$class, 'variableGroups'];
            $groups = $cb();
            if (is_array($groups) && isset($groups['raw'], $groups['escaped'])) {
                $map[$key] = $groups;
            }
        }

        return $map;
    }

    /**
     * Gabungan variabel payload dari semua key terdaftar (untuk form create saat Key masih kosong).
     *
     * @return array{raw: array<int, string>, escaped: array<int, string>}
     */
    public static function mergedKnownVariableGroupsForAllKeys(): array
    {
        $raw = [];
        $esc = [];
        foreach (self::allKnownVariableGroupsByTemplateKey() as $groups) {
            $raw = array_merge($raw, $groups['raw'] ?? []);
            $esc = array_merge($esc, $groups['escaped'] ?? []);
        }
        $raw = array_values(array_unique($raw));
        $esc = array_values(array_unique(array_diff($esc, $raw)));
        sort($raw);
        sort($esc);

        return [
            'raw' => $raw,
            'escaped' => $esc,
        ];
    }

    /**
     * Variabel placeholder dari HTML + JSON contoh + definisi payload menurut key template.
     *
     * @return array{raw: array<int, string>, escaped: array<int, string>}
     */
    public static function mergePlaceholderGroupsWithData(array $fromHtml, ?array $sampleData, string $templateKey): array
    {
        $templateKey = trim($templateKey);
        $known = $templateKey === ''
            ? self::mergedKnownVariableGroupsForAllKeys()
            : (self::allKnownVariableGroupsByTemplateKey()[$templateKey] ?? ['raw' => [], 'escaped' => []]);
        $raw = array_values(array_unique(array_merge($fromHtml['raw'] ?? [], $known['raw'] ?? [])));
        $esc = array_values(array_unique(array_merge($fromHtml['escaped'] ?? [], $known['escaped'] ?? [])));

        $knownRawSet = array_fill_keys($known['raw'] ?? [], true);
        if ($sampleData !== null) {
            foreach (self::flattenSampleDataKeys($sampleData) as $path) {
                $last = str_contains($path, '.') ? substr($path, (int) strrpos($path, '.') + 1) : $path;
                if (isset($knownRawSet[$path]) || isset($knownRawSet[$last])) {
                    $raw[] = $path;
                } else {
                    $esc[] = $path;
                }
            }
        }

        $raw = array_values(array_unique($raw));
        $esc = array_values(array_unique(array_diff($esc, $raw)));
        sort($raw);
        sort($esc);

        return [
            'raw' => $raw,
            'escaped' => $esc,
        ];
    }

    /**
     * Path dot untuk kunci JSON asosiatif (nilai skalar / array dianggap daun).
     *
     * @return list<string>
     */
    public static function flattenSampleDataKeys(array $data, string $prefix = ''): array
    {
        $keys = [];
        foreach ($data as $k => $v) {
            if (! is_string($k) && ! is_int($k)) {
                continue;
            }
            $segment = is_int($k) ? (string) $k : $k;
            $path = $prefix === '' ? $segment : $prefix.'.'.$segment;
            if (is_array($v) && $v !== [] && ! array_is_list($v)) {
                foreach (self::flattenSampleDataKeys($v, $path) as $child) {
                    $keys[] = $child;
                }
            } else {
                $keys[] = $path;
            }
        }

        return $keys;
    }

    private static function sanitizeRawHtml(string $html): string
    {
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html) ?? $html;
        $html = preg_replace('/<iframe\b[^>]*>.*?<\/iframe>/is', '', $html) ?? $html;
        $html = preg_replace('/\s(on\w+|style)\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? $html;
        $html = preg_replace('/javascript\s*:/i', '', $html) ?? $html;

        return $html;
    }
}
