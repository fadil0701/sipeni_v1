<?php

namespace App\Services;

/**
 * Metadata provider for module_permissions config (SSOT for module features & grouping).
 * Does not drive sidebar rendering — sidebar remains permission-route based.
 */
class ModuleRegistry
{
    private array $config;

    public function __construct(?array $config = null)
    {
        $this->config = $config ?? (array) config('module_permissions', []);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getModules(): array
    {
        return $this->config['modules'] ?? [];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getModule(string $key): ?array
    {
        return $this->getModules()[$key] ?? null;
    }

    public function hasModule(string $key): bool
    {
        return $this->getModule($key) !== null;
    }

    /**
     * Logical permission keys declared for a module (documentation + optional explicit mapping).
     *
     * @return list<string>
     */
    public function getPermissions(string $key): array
    {
        $module = $this->getModule($key);

        return array_values($module['permissions'] ?? []);
    }

    /**
     * @return array<string, bool>
     */
    public function getFeatures(string $key): array
    {
        $module = $this->getModule($key);
        if ($module === null) {
            return $this->defaultFeatures();
        }

        return array_merge($this->defaultFeatures(), (array) ($module['features'] ?? []));
    }

    public function hasFeature(string $key, string $feature): bool
    {
        $features = $this->getFeatures($key);

        return (bool) ($features[$feature] ?? false);
    }

    /**
     * @return array<string, array{label: string, modules: list<string>}>
     */
    public function getDisplayGroups(): array
    {
        return $this->config['display_groups'] ?? [];
    }

    public function getDisplayGroupLabelForModule(string $moduleKey): ?string
    {
        $module = $this->getModule($moduleKey);
        if ($module !== null && ! empty($module['display_group'])) {
            $groupKey = (string) $module['display_group'];
            $groups = $this->getDisplayGroups();

            return $groups[$groupKey]['label'] ?? $groupKey;
        }

        foreach ($this->getDisplayGroups() as $group) {
            if (in_array($moduleKey, $group['modules'] ?? [], true)) {
                return $group['label'] ?? null;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    public function getModuleActions(string $key): array
    {
        return $this->getModule($key)['actions'] ?? [];
    }

    public function moduleHasAction(string $key, string $action): bool
    {
        return in_array($action, $this->getModuleActions($key), true);
    }

    public function moduleLabel(string $key): string
    {
        return (string) ($this->getModule($key)['label'] ?? $key);
    }

    /**
     * Route-prefix based resolution (legacy compatible).
     *
     * @return list<string>
     */
    public function getModulePrefixes(string $key): array
    {
        return $this->getModule($key)['prefixes'] ?? [];
    }

    /**
     * Optional explicit map: logical_key => route fragment(s) under module prefixes.
     *
     * @return array<string, list<string>>
     */
    public function getPermissionMap(string $key): array
    {
        return $this->getModule($key)['permission_map'] ?? [];
    }

    /**
     * @return list<string>
     */
    public function getCanonicalActions(): array
    {
        return $this->config['actions'] ?? ['view', 'create', 'update', 'approve'];
    }

    /**
     * @return array<string, list<string>>
     */
    public function getActionSuffixes(): array
    {
        return $this->config['action_actions'] ?? [];
    }

    /**
     * @return array<string, bool>
     */
    private function defaultFeatures(): array
    {
        return (array) ($this->config['default_features'] ?? [
            'approval' => false,
            'workflow' => false,
            'audit' => false,
            'dashboard' => false,
            'notifications' => false,
            'inventory_effect' => false,
        ]);
    }
}
