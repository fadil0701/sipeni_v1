<?php

namespace App\Services;

use App\Models\User;
use App\Support\Rbac\CanonicalRoleCatalog;
use App\Support\Rbac\RoleCompatibility;
use Illuminate\Support\Str;
use RuntimeException;

class PanduanPenggunaService
{
    public const SOURCE_DIR = PanduanPenggunaPdfExporter::SOURCE_DIR;

    /** @var array<string, string> slug => relative markdown path */
    public const CHAPTERS = [
        'pengenalan' => '01-pengenalan-dan-login.md',
        'modul-dan-fitur' => '02-modul-dan-fitur.md',
        'alur-kerja' => '03-alur-kerja-utama.md',
        'matriks-role' => '04-matrik-akses-role.md',
        'daftar-role' => 'per-role/README.md',
    ];

    /** @var array<string, string> role name => relative markdown path */
    public const ROLE_FILES = [
        'super_administrator' => 'per-role/super_administrator.md',
        'kepala_pusat' => 'per-role/kepala_pusat.md',
        'kasubbag_tu' => 'per-role/kasubbag_tu.md',
        'kepala_unit' => 'per-role/kepala_unit.md',
        'admin_unit' => 'per-role/admin_unit.md',
        'perencana' => 'per-role/perencana.md',
        'pengadaan' => 'per-role/pengadaan.md',
        'keuangan' => 'per-role/keuangan.md',
        'pptk_apbd' => 'per-role/pptk_apbd.md',
        'pptk_blud' => 'per-role/pptk_blud.md',
        'pengurus_barang' => 'per-role/pengurus_barang.md',
        'admin_gudang_pusat' => 'per-role/admin_gudang_pusat.md',
        'admin_gudang_aset' => 'per-role/admin_gudang_aset.md',
        'admin_gudang_persediaan' => 'per-role/admin_gudang_persediaan.md',
        'admin_gudang_farmasi' => 'per-role/admin_gudang_farmasi.md',
        'admin' => 'per-role/admin-dan-administrator.md',
        'administrator' => 'per-role/admin-dan-administrator.md',
    ];

    public static function basePath(): string
    {
        return base_path(self::SOURCE_DIR);
    }

    /**
     * @return array{slug: string, title: string, description: string, relative: string}
     */
    public static function chapterMeta(string $slug): array
    {
        $relative = self::CHAPTERS[$slug] ?? null;
        if (! $relative) {
            throw new RuntimeException('Bab panduan tidak dikenal.');
        }

        return [
            'slug' => $slug,
            'title' => self::titleFromRelativePath($relative),
            'description' => self::chapterDescription($slug),
            'relative' => $relative,
        ];
    }

    /**
     * @return array{slug: string, role: string, title: string, description: string, relative: string}
     */
    public static function roleMeta(string $role): array
    {
        $canonical = RoleCompatibility::canonicalFor($role);
        $relative = self::ROLE_FILES[$role] ?? self::ROLE_FILES[$canonical] ?? null;
        if (! $relative) {
            throw new RuntimeException('Panduan role tidak tersedia.');
        }

        $definitions = CanonicalRoleCatalog::definitions();
        $display = $definitions[$canonical]['display_name'] ?? Str::headline(str_replace('_', ' ', $canonical));

        return [
            'slug' => 'role-'.$canonical,
            'role' => $canonical,
            'title' => 'Panduan: '.$display,
            'description' => $definitions[$canonical]['description'] ?? 'Tugas dan menu sesuai role.',
            'relative' => $relative,
        ];
    }

    public static function docSlugForRole(string $role): string
    {
        return self::roleMeta($role)['slug'];
    }

    /**
     * @return array{slug: string, relative: string, title: string}
     */
    public static function resolveDoc(string $doc): array
    {
        $slug = self::resolveDocSlug($doc);
        if ($slug === null) {
            throw new RuntimeException('Dokumen panduan tidak ditemukan.');
        }

        if (isset(self::CHAPTERS[$slug])) {
            $meta = self::chapterMeta($slug);
        } else {
            $meta = self::roleMeta(substr($slug, 5));
        }

        return [
            'slug' => $meta['slug'],
            'relative' => $meta['relative'],
            'title' => $meta['title'],
        ];
    }

    public static function resolveDocSlug(string $doc): ?string
    {
        $doc = trim($doc);
        if ($doc === '') {
            return null;
        }

        if (isset(self::CHAPTERS[$doc])) {
            return $doc;
        }

        if (str_starts_with($doc, 'role-')) {
            try {
                self::roleMeta(substr($doc, 5));

                return $doc;
            } catch (RuntimeException) {
                return null;
            }
        }

        $normalized = str_replace('\\', '/', $doc);
        $normalized = ltrim($normalized, './');
        while (str_starts_with($normalized, '../')) {
            $normalized = substr($normalized, 3);
        }

        $map = self::markdownFileToSlugMap();
        if (isset($map[$normalized])) {
            return $map[$normalized];
        }

        $withoutExtension = preg_replace('/\.md$/i', '', $normalized);
        if ($withoutExtension !== null && isset($map[$withoutExtension])) {
            return $map[$withoutExtension];
        }

        $basename = basename($normalized);
        if (isset($map[$basename])) {
            return $map[$basename];
        }

        return null;
    }

    /**
     * @return array<string, string> referensi berkas markdown => slug panduan web
     */
    public static function markdownFileToSlugMap(): array
    {
        static $map = null;
        if (is_array($map)) {
            return $map;
        }

        $map = [];
        foreach (self::CHAPTERS as $slug => $file) {
            $map[$file] = $slug;
            $map[basename($file)] = $slug;
        }

        $seenRoleFiles = [];
        foreach (self::ROLE_FILES as $role => $file) {
            if (isset($seenRoleFiles[$file])) {
                continue;
            }
            $seenRoleFiles[$file] = true;
            $roleSlug = self::docSlugForRole($role);
            $map[$file] = $roleSlug;
            $map['per-role/'.basename($file)] = $roleSlug;
            $map[basename($file)] = $roleSlug;
        }

        $map['per-role/README.md'] = 'daftar-role';

        return $map;
    }

    /**
     * @return list<string>
     */
    public static function legacyMarkdownRedirectPaths(): array
    {
        $paths = [];
        foreach (array_keys(self::markdownFileToSlugMap()) as $reference) {
            if (! str_contains($reference, '/')) {
                $paths[] = $reference;
            }
        }

        return array_values(array_unique($paths));
    }

    public static function absolutePath(string $relative): string
    {
        $full = self::basePath().DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
        $realBase = realpath(self::basePath());
        $realFile = realpath($full);

        if ($realBase === false || $realFile === false || ! str_starts_with($realFile, $realBase)) {
            throw new RuntimeException('Path panduan tidak valid.');
        }

        return $realFile;
    }

    public static function htmlFromDoc(string $doc): string
    {
        $resolved = self::resolveDoc($doc);
        $markdown = (string) file_get_contents(self::absolutePath($resolved['relative']));

        return self::markdownToWebHtml($markdown);
    }

    public static function markdownToWebHtml(string $markdown): string
    {
        $prepared = self::rewriteMarkdownLinksForWeb($markdown);

        return PanduanPenggunaPdfExporter::markdownToHtml($prepared);
    }

    public static function rewriteMarkdownLinksForWeb(string $markdown): string
    {
        $fileToUrl = [];
        foreach (self::markdownFileToSlugMap() as $file => $slug) {
            $fileToUrl[$file] = route('panduan.show', ['doc' => $slug]);
        }

        return (string) preg_replace_callback(
            '/\]\(([^)]+)\)/',
            static function (array $matches) use ($fileToUrl): string {
                $target = $matches[1];
                $hash = '';
                if (str_contains($target, '#')) {
                    [$target, $hashPart] = explode('#', $target, 2);
                    $hash = '#'.$hashPart;
                }

                $normalized = str_replace('\\', '/', $target);
                $normalized = ltrim($normalized, './');
                while (str_starts_with($normalized, '../')) {
                    $normalized = substr($normalized, 3);
                }

                if (isset($fileToUrl[$normalized])) {
                    return ']('.$fileToUrl[$normalized].$hash.')';
                }

                $basename = basename($normalized);
                if (isset($fileToUrl[$basename])) {
                    return ']('.$fileToUrl[$basename].$hash.')';
                }

                return $matches[0];
            },
            $markdown
        );
    }

    /**
     * @return list<array{slug: string, title: string, description: string}>
     */
    public static function generalChapters(): array
    {
        $out = [];
        foreach (self::CHAPTERS as $slug => $_file) {
            $meta = self::chapterMeta($slug);
            $out[] = [
                'slug' => $meta['slug'],
                'title' => $meta['title'],
                'description' => $meta['description'],
            ];
        }

        return $out;
    }

    /**
     * @return list<array{slug: string, role: string, title: string, description: string}>
     */
    public static function roleGuidesForUser(?User $user): array
    {
        if (! $user) {
            return [];
        }

        $roles = RoleCompatibility::effectiveRoleNamesForUser($user);
        $definitions = CanonicalRoleCatalog::definitions();
        $guides = [];
        $seen = [];

        foreach ($roles as $roleName) {
            $canonical = RoleCompatibility::canonicalFor($roleName);
            if (isset($seen[$canonical])) {
                continue;
            }
            if (! isset(self::ROLE_FILES[$canonical]) && ! isset(self::ROLE_FILES[$roleName])) {
                if (! in_array($roleName, ['admin', 'administrator'], true)) {
                    continue;
                }
            }

            try {
                $meta = self::roleMeta($canonical);
            } catch (RuntimeException) {
                if (in_array($roleName, ['admin', 'administrator'], true)) {
                    $meta = self::roleMeta('admin');
                } else {
                    continue;
                }
            }

            $seen[$canonical] = true;
            $guides[] = [
                'slug' => $meta['slug'],
                'role' => $meta['role'],
                'title' => $definitions[$meta['role']]['display_name'] ?? $meta['title'],
                'description' => $definitions[$meta['role']]['description'] ?? $meta['description'],
            ];
        }

        usort($guides, fn (array $a, array $b) => strcmp($a['title'], $b['title']));

        return $guides;
    }

    public static function primaryRoleGuide(?User $user): ?array
    {
        $guides = self::roleGuidesForUser($user);

        return $guides[0] ?? null;
    }

    public static function titleFromRelativePath(string $relative): string
    {
        $absolute = self::absolutePath($relative);
        $markdown = (string) file_get_contents($absolute);

        return PanduanPenggunaPdfExporter::titleFromMarkdown($markdown);
    }

    public static function chapterDescription(string $slug): string
    {
        return match ($slug) {
            'pengenalan' => 'Login, navigasi, kebijakan password, dan scope unit kerja.',
            'modul-dan-fitur' => 'Semua menu sidebar dan kegunaannya.',
            'alur-kerja' => 'Permintaan, distribusi, RKU, pengadaan, dan modul terkait.',
            'matriks-role' => 'Tabel ringkas akses role × modul.',
            'daftar-role' => 'Indeks panduan untuk setiap role kanonik.',
            default => 'Panduan pengguna SI-MANTIK.',
        };
    }

    public static function webContentCss(): string
    {
        return <<<'CSS'
.panduan-content h1 { font-size: 1.75rem; font-weight: 700; color: #1e3a8a; margin: 0 0 1rem; padding-bottom: .5rem; border-bottom: 2px solid #bfdbfe; }
.panduan-content h2 { font-size: 1.25rem; font-weight: 600; color: #1e40af; margin: 1.5rem 0 .75rem; }
.panduan-content h3 { font-size: 1.05rem; font-weight: 600; color: #1d4ed8; margin: 1.25rem 0 .5rem; }
.panduan-content p { margin: 0 0 .75rem; color: #334155; line-height: 1.65; }
.panduan-content ul, .panduan-content ol { margin: 0 0 1rem 1.25rem; color: #334155; }
.panduan-content li { margin-bottom: .35rem; }
.panduan-content a { color: #2563eb; text-decoration: underline; }
.panduan-content a:hover { color: #1d4ed8; }
.panduan-content blockquote { margin: 1rem 0; padding: .75rem 1rem; border-left: 4px solid #93c5fd; background: #eff6ff; color: #1e3a8a; border-radius: .375rem; }
.panduan-content code { font-size: .85em; background: #f1f5f9; padding: .1rem .35rem; border-radius: .25rem; }
.panduan-content pre { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: .5rem; padding: 1rem; overflow-x: auto; font-size: .85rem; margin-bottom: 1rem; }
.panduan-content table { width: 100%; border-collapse: collapse; margin: 1rem 0; font-size: .9rem; }
.panduan-content th, .panduan-content td { border: 1px solid #e2e8f0; padding: .5rem .65rem; text-align: left; vertical-align: top; }
.panduan-content th { background: #eff6ff; color: #1e3a8a; font-weight: 600; }
.panduan-content hr { border: none; border-top: 1px solid #e2e8f0; margin: 1.5rem 0; }
CSS;
    }
}
