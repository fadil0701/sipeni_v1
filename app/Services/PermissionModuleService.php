<?php

namespace App\Services;

use App\Models\Permission;
use App\Services\Rbac\RolePermissionResolver;
use Illuminate\Support\Collection;

class PermissionModuleService
{
    public function __construct(
        private readonly ModuleRegistry $registry,
        private readonly RolePermissionResolver $resolver,
    ) {}

    public function getModules(): array
    {
        return $this->registry->getModules();
    }

    public function getActions(): array
    {
        return $this->registry->getCanonicalActions();
    }

    public function moduleLabel(string $moduleKey): string
    {
        return $this->registry->moduleLabel($moduleKey);
    }

    public function moduleActions(string $moduleKey): array
    {
        return $this->registry->getModuleActions($moduleKey);
    }

    public function moduleHasAction(string $moduleKey, string $action): bool
    {
        return $this->registry->moduleHasAction($moduleKey, $action);
    }

    /**
     * @return array<int, array{key: string, label: string, actions: array}>
     */
    public function buildMatrix(Collection $assignablePermissions, array $checkedIds): array
    {
        return $this->resolver->buildMatrixRows($assignablePermissions, $checkedIds);
    }

    /**
     * @return array<int, array{key: string, label: string, items: array}>
     */
    public function buildGroupedMatrix(Collection $assignablePermissions, array $checkedIds): array
    {
        $rows = $this->buildMatrix($assignablePermissions, $checkedIds);
        $byKey = collect($rows)->keyBy('key');
        $groups = [];
        $assigned = [];

        foreach ($this->registry->getDisplayGroups() as $groupKey => $group) {
            $items = [];
            foreach ($group['modules'] ?? [] as $moduleKey) {
                if ($byKey->has($moduleKey)) {
                    $items[] = $byKey->get($moduleKey);
                    $assigned[$moduleKey] = true;
                }
            }
            if ($items !== []) {
                $groups[] = [
                    'key' => $groupKey,
                    'label' => $group['label'],
                    'items' => $items,
                ];
            }
        }

        $remaining = $rows;
        if ($assigned !== []) {
            $remaining = $byKey->reject(fn ($row, $key) => isset($assigned[$key]))->values()->all();
        }

        if ($remaining !== []) {
            $groups[] = [
                'key' => 'lainnya',
                'label' => 'Lainnya',
                'items' => array_values($remaining),
            ];
        }

        return $groups;
    }

    /**
     * @param  array<int, int>  $submittedIds
     * @return array<int, int>
     */
    public function expandSubmittedIds(Collection $assignablePermissions, array $submittedIds): array
    {
        return $this->resolver->expand($submittedIds, $assignablePermissions);
    }
}
