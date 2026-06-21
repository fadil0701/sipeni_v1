<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Models\Role;
use App\Support\Rbac\RoleCompatibility;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RbacAuditCommand extends Command
{
    protected $signature = 'rbac:audit {--json : Output sebagai JSON}';

    protected $description = 'Audit RBAC: hardcoded role, blade @role, route permission, orphan permission, deprecated role';

    /** @var list<string> */
    private array $legacyRolePatterns = [
        'pegawai_unit',
        'admin_gudang_unit',
        'pegawai',
        'admin_perencanaan',
        'perencanaan',
        'admin_pengadaan_apbd',
        'admin_pengadaan_blud',
        'admin_keuangan',
        'admin_pptk_apbd',
        'admin_pptk_blud',
    ];

    public function handle(): int
    {
        $report = [
            'hardcoded_role_usage' => $this->scanHardcodedRoles(),
            'admin_bypass_candidates' => $this->scanAdminBypassCandidates(),
            'blade_role_directive' => $this->scanBladeRoleDirective(),
            'routes_without_named_permission' => $this->scanRoutesWithoutPermission(),
            'orphan_permissions' => $this->scanOrphanPermissions(),
            'duplicate_permission_names' => $this->scanDuplicatePermissions(),
            'deprecated_roles_in_db' => $this->scanDeprecatedRoles(),
            'wildcard_permissions' => $this->scanWildcardPermissions(),
        ];

        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        }

        $this->printSection('Hardcoded role (app/)', $report['hardcoded_role_usage']);
        $this->printSection('Kandidat bypass admin/administrator', $report['admin_bypass_candidates']);
        $this->printSection('Blade @role', $report['blade_role_directive']);
        $this->printSection('Route tanpa nama (tidak bisa dijadikan permission)', $report['routes_without_named_permission']);
        $this->printSection('Orphan permission (tidak dipakai role manapun)', $report['orphan_permissions']);
        $this->printSection('Duplikat nama permission', $report['duplicate_permission_names']);
        $this->printSection('Role deprecated di DB', $report['deprecated_roles_in_db']);
        $this->printSection('Wildcard permission', $report['wildcard_permissions']);

        $issues = count($report['hardcoded_role_usage'])
            + count($report['admin_bypass_candidates'])
            + count($report['blade_role_directive']);

        $this->newLine();
        $this->info('Audit selesai. Temuan prioritas (role hardcoded / bypass / @role): '.$issues);

        return self::SUCCESS;
    }

    /**
     * @return list<array{file: string, line: int, snippet: string}>
     */
    private function scanHardcodedRoles(): array
    {
        $findings = [];
        $needles = array_merge($this->legacyRolePatterns, ['hasRole(', 'hasAnyRole(']);

        foreach (File::allFiles(app_path()) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $path = str_replace('\\', '/', $file->getPathname());
            if (str_contains($path, '/Support/Rbac/') || str_contains($path, 'RbacAuditCommand.php')) {
                continue;
            }
            $lines = file($file->getPathname());
            foreach ($lines as $i => $line) {
                if ($this->isCommentOrDocblockLine($line)) {
                    continue;
                }
                if (! str_contains($line, 'hasRole(') && ! str_contains($line, 'hasAnyRole(')) {
                    continue;
                }
                foreach ($this->legacyRolePatterns as $legacy) {
                    if (str_contains($line, "'{$legacy}'") || str_contains($line, "\"{$legacy}\"")) {
                        $findings[] = $this->finding($path, $i + 1, trim($line));
                        break;
                    }
                }
            }
        }

        return $findings;
    }

    /**
     * @return list<array{file: string, line: int, snippet: string}>
     */
    private function scanAdminBypassCandidates(): array
    {
        $findings = [];
        foreach (File::allFiles(app_path()) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $path = str_replace('\\', '/', $file->getPathname());
            if (str_contains($path, '/Support/Rbac/') || str_contains($path, 'RbacAuditCommand.php')) {
                continue;
            }
            $lines = file($file->getPathname());
            foreach ($lines as $i => $line) {
                if ($this->isCommentOrDocblockLine($line)) {
                    continue;
                }
                if (preg_match("/hasRole\s*\(\s*['\"]admin(?:istrator)?['\"]\s*\)/", $line)) {
                    $findings[] = $this->finding($path, $i + 1, trim($line));
                }
            }
        }

        foreach (File::allFiles(resource_path('views')) as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }
            $path = str_replace('\\', '/', $file->getPathname());
            $content = file_get_contents($path);
            if ($content && preg_match('/hasRole\([\'"]admin[\'"]\)/', $content)) {
                $findings[] = $this->finding($path, 0, 'hasRole(admin) in blade');
            }
        }

        return $findings;
    }

    /**
     * @return list<array{file: string, line: int, snippet: string}>
     */
    private function scanBladeRoleDirective(): array
    {
        $findings = [];
        foreach (File::allFiles(resource_path('views')) as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }
            $path = str_replace('\\', '/', $file->getPathname());
            $lines = file($path);
            foreach ($lines as $i => $line) {
                if (preg_match('/@role\s*\(|@endrole|@hasrole/i', $line)) {
                    $findings[] = $this->finding($path, $i + 1, trim($line));
                }
            }
        }

        return $findings;
    }

    /**
     * @return list<string>
     */
    private function scanRoutesWithoutPermission(): array
    {
        $missing = [];
        foreach (Route::getRoutes() as $route) {
            $name = $route->getName();
            if (! $name || ! str_starts_with($route->uri(), '/') && ! str_contains($route->uri(), 'login')) {
                if ($route->getName() === null && in_array('web', $route->middleware(), true)) {
                    $missing[] = $route->uri().' ['.implode('|', $route->methods()).']';
                }
            }
        }

        return array_slice(array_unique($missing), 0, 50);
    }

    /**
     * @return list<string>
     */
    private function scanOrphanPermissions(): array
    {
        $assigned = DB::table('permission_role')->distinct()->pluck('permission_id')->all();
        $all = Permission::query()->pluck('name', 'id');

        $orphans = [];
        foreach ($all as $id => $name) {
            if (! in_array($id, $assigned, true)) {
                $orphans[] = $name;
            }
        }

        return array_slice($orphans, 0, 100);
    }

    /**
     * @return list<string>
     */
    private function scanDuplicatePermissions(): array
    {
        return Permission::query()
            ->select('name')
            ->groupBy('name')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('name')
            ->all();
    }

    /**
     * @return list<string>
     */
    private function scanDeprecatedRoles(): array
    {
        if (! Schema::hasColumn('roles', 'is_deprecated')) {
            return Role::query()
                ->whereIn('name', RoleCompatibility::DEPRECATED_LEGACY_ROLES)
                ->pluck('name')
                ->all();
        }

        return Role::query()->where('is_deprecated', true)->pluck('name')->all();
    }

    /**
     * @return list<string>
     */
    private function scanWildcardPermissions(): array
    {
        return Permission::query()
            ->where('name', 'like', '%*%')
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    /**
     * @param  list<array{file: string, line: int, snippet: string}>|list<string>  $items
     */
    private function printSection(string $title, array $items): void
    {
        $this->newLine();
        $this->comment($title.' ('.count($items).')');
        if ($items === []) {
            $this->line('  (tidak ada)');

            return;
        }
        foreach (array_slice($items, 0, 25) as $item) {
            if (is_array($item)) {
                $loc = $item['line'] > 0 ? ":{$item['line']}" : '';
                $this->line("  {$item['file']}{$loc} — {$item['snippet']}");
            } else {
                $this->line('  '.$item);
            }
        }
        if (count($items) > 25) {
            $this->line('  ... dan '.(count($items) - 25).' lainnya');
        }
    }

    private function isCommentOrDocblockLine(string $line): bool
    {
        $trimmed = ltrim($line);

        return $trimmed === ''
            || str_starts_with($trimmed, '//')
            || str_starts_with($trimmed, '*')
            || str_starts_with($trimmed, '/*');
    }

    /**
     * @return array{file: string, line: int, snippet: string}
     */
    private function finding(string $file, int $line, string $snippet): array
    {
        return [
            'file' => Str::after($file, base_path().'/'),
            'line' => $line,
            'snippet' => Str::limit($snippet, 120),
        ];
    }
}
