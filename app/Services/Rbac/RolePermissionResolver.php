<?php

namespace App\Services\Rbac;

use App\Models\Permission;
use App\Services\ModuleRegistry;
use Illuminate\Support\Collection;

/**
 * Centralizes matrix ↔ granular permission mapping without changing route permission names.
 */
class RolePermissionResolver
{
    public function __construct(
        private readonly ModuleRegistry $registry,
    ) {}

    /**
     * Expand submitted permission IDs (including partial group selections) to full granular set.
     *
     * @param  array<int, int>  $submittedIds
     * @return array<int, int>
     */
    public function expand(array $submittedIds, Collection $assignablePermissions): array
    {
        $submittedIds = $this->normalizeIds($submittedIds);
        if ($submittedIds === [] || $assignablePermissions->isEmpty()) {
            return [];
        }

        $byId = $assignablePermissions->keyBy(fn (Permission $p) => (int) $p->id);
        $groupedIds = $this->buildPermissionGroupIndex($assignablePermissions);

        $expanded = [];
        foreach ($submittedIds as $id) {
            $permission = $byId->get($id);
            if (! $permission) {
                continue;
            }

            $groupKey = $this->permissionGroupKey((string) $permission->name);
            foreach ($groupedIds[$groupKey] ?? [$id] as $groupId) {
                $expanded[] = (int) $groupId;
            }
        }

        return array_values(array_unique($expanded));
    }

    /**
     * Collapse granular permission IDs into matrix module/action state for UI.
     *
     * @param  array<int, int>  $granularPermissionIds
     * @return array<string, array<string, array{permission_ids: array<int>, all_checked: bool, some_checked: bool}>>
     */
    public function collapse(array $granularPermissionIds, Collection $assignablePermissions): array
    {
        $checkedIds = $this->normalizeIds($granularPermissionIds);
        $matrix = [];

        foreach ($this->registry->getModules() as $moduleKey => $module) {
            foreach ($module['actions'] ?? [] as $action) {
                $cellPerms = $this->getActionPermissions($moduleKey, $action, $assignablePermissions);
                if ($cellPerms->isEmpty()) {
                    continue;
                }

                $childIds = $cellPerms->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
                $intersect = array_intersect($childIds, $checkedIds);
                $allChecked = count($intersect) === count($childIds) && $childIds !== [];
                $someChecked = $intersect !== [] && ! $allChecked;

                $matrix[$moduleKey][$action] = [
                    'permission_ids' => $childIds,
                    'all_checked' => $allChecked,
                    'some_checked' => $someChecked,
                ];
            }
        }

        return $matrix;
    }

    /**
     * @return list<string> logical permission keys from config
     */
    public function getModulePermissions(string $module): array
    {
        return $this->registry->getPermissions($module);
    }

    /**
     * Permissions in DB matching module matrix action (prefix + action suffix rules).
     *
     * @return Collection<int, Permission>
     */
    public function getActionPermissions(string $module, string $action, Collection $assignablePermissions): Collection
    {
        if (! $this->registry->moduleHasAction($module, $action)) {
            return collect();
        }

        $prefixes = $this->registry->getModulePrefixes($module);
        $actionSuffixes = $this->registry->getActionSuffixes()[$action] ?? [];

        if ($prefixes === [] || $actionSuffixes === []) {
            return collect();
        }

        return $assignablePermissions->filter(function (Permission $perm) use ($prefixes, $actionSuffixes, $module, $action) {
            if ($this->matchesExplicitLogicalMap($perm, $module, $action)) {
                return true;
            }

            return $this->matchesPrefixAction((string) $perm->name, $prefixes, $actionSuffixes);
        })->values();
    }

    /**
     * Build matrix rows for role UI (delegates grouping to PermissionModuleService consumer).
     *
     * @param  array<int, int>  $checkedIds
     * @return array<int, array{key: string, label: string, actions: array}>
     */
    public function buildMatrixRows(Collection $assignablePermissions, array $checkedIds): array
    {
        $checkedIds = $this->normalizeIds($checkedIds);
        $rows = [];

        foreach ($this->registry->getModules() as $moduleKey => $module) {
            $actions = [];

            foreach ($module['actions'] ?? [] as $action) {
                $childPerms = $this->getActionPermissions($moduleKey, $action, $assignablePermissions);

                if ($childPerms->isEmpty()) {
                    $actions[$action] = [
                        'permissions' => collect(),
                        'all_checked' => false,
                        'some_checked' => false,
                        'permission_ids' => [],
                        'ids' => [],
                    ];

                    continue;
                }

                $childIds = $childPerms->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
                $intersect = array_intersect($childIds, $checkedIds);
                $allChecked = count($intersect) === count($childIds) && $childIds !== [];
                $someChecked = $intersect !== [] && ! $allChecked;

                $actions[$action] = [
                    'permissions' => $childPerms,
                    'all_checked' => $allChecked,
                    'some_checked' => $someChecked,
                    'permission_ids' => $childIds,
                    'ids' => $childIds,
                ];
            }

            $rows[] = [
                'key' => $moduleKey,
                'label' => $module['label'] ?? $moduleKey,
                'actions' => $actions,
            ];
        }

        return $rows;
    }

    /**
     * @return array<string, list<int>>
     */
    private function buildPermissionGroupIndex(Collection $assignablePermissions): array
    {
        $groupedIds = [];
        foreach ($assignablePermissions as $permission) {
            $groupKey = $this->permissionGroupKey((string) $permission->name);
            $groupedIds[$groupKey] ??= [];
            $groupedIds[$groupKey][] = (int) $permission->id;
        }

        return $groupedIds;
    }

    private function permissionGroupKey(string $permissionName): string
    {
        $parts = explode('.', $permissionName);
        if (count($parts) >= 3) {
            $resource = $parts[count($parts) - 2];

            return $parts[0].'.'.$resource;
        }

        return $permissionName;
    }

    private function matchesPrefixAction(string $name, array $prefixes, array $actionSuffixes): bool
    {
        foreach ($prefixes as $prefix) {
            if (! str_starts_with($name, $prefix)) {
                continue;
            }

            $suffix = substr($name, strlen($prefix));
            foreach ($actionSuffixes as $actionSuffix) {
                if ($suffix === $actionSuffix || str_ends_with($suffix, '.'.$actionSuffix)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function matchesExplicitLogicalMap(Permission $perm, string $module, string $action): bool
    {
        $map = $this->registry->getPermissionMap($module);
        if ($map === []) {
            return false;
        }

        $logicalKeys = $this->logicalKeysForAction($module, $action);
        if ($logicalKeys === []) {
            return false;
        }

        $name = (string) $perm->name;
        foreach ($logicalKeys as $logicalKey) {
            $fragments = $map[$logicalKey] ?? [];
            foreach ($fragments as $fragment) {
                if (str_contains($name, (string) $fragment)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private function logicalKeysForAction(string $module, string $action): array
    {
        $keys = [];
        foreach ($this->registry->getPermissions($module) as $logical) {
            if (str_starts_with($logical, $action.'_')) {
                $keys[] = $logical;
            }
        }

        return $keys;
    }

    /**
     * @param  array<int, int>  $ids
     * @return array<int, int>
     */
    private function normalizeIds(array $ids): array
    {
        return array_values(array_unique(array_filter(array_map('intval', $ids), fn (int $id) => $id > 0)));
    }
}
