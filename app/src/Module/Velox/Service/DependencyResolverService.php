<?php

declare(strict_types=1);

namespace App\Module\Velox\Service;

use App\Module\Velox\DTO\ConflictInfo;
use App\Module\Velox\DTO\DependencyResolution;
use App\Module\Velox\DTO\Plugin;
use App\Module\Velox\DTO\PluginCategory;
use App\Module\Velox\DTO\VersionSuggestion;
use App\Module\Velox\Exception\DependencyConflictException;
use App\Module\Velox\Exception\PluginNotFoundException;

final readonly class DependencyResolverService
{
    public function __construct(
        private PluginProviderInterface $pluginProvider,
    ) {}

    /**
     * @param array<Plugin> $selectedPlugins
     */
    public function resolveDependencies(array $selectedPlugins): DependencyResolution
    {
        $resolved = [];
        $conflicts = [];
        $visited = [];

        foreach ($selectedPlugins as $plugin) {
            try {
                $this->resolveDependenciesRecursive($plugin, $resolved, $visited);
            } catch (DependencyConflictException $e) {
                $conflicts[] = new ConflictInfo(
                    pluginName: $plugin->name,
                    conflictType: 'circular_dependency',
                    message: $e->getMessage(),
                    conflictingPlugins: $e->conflictingPlugins,
                );
            } catch (PluginNotFoundException $e) {
                $conflicts[] = new ConflictInfo(
                    pluginName: $plugin->name,
                    conflictType: 'missing_dependency',
                    message: $e->getMessage(),
                    conflictingPlugins: [],
                );
            }
        }

        // Remove duplicates
        $resolved = \array_unique($resolved, SORT_REGULAR);

        return new DependencyResolution(
            requiredPlugins: $resolved,
            conflicts: $conflicts,
            isValid: empty($conflicts),
        );
    }

    /**
     * @param array<Plugin> $selectedPlugins
     * @return array<ConflictInfo>
     */
    public function detectConflicts(array $selectedPlugins): array
    {
        $conflicts = [];

        // Check for version conflicts
        $pluginVersions = [];
        foreach ($selectedPlugins as $plugin) {
            if (isset($pluginVersions[$plugin->name])) {
                if ($pluginVersions[$plugin->name] !== $plugin->ref) {
                    $conflicts[] = new ConflictInfo(
                        pluginName: $plugin->name,
                        conflictType: 'version_conflict',
                        message: "Plugin {$plugin->name} has conflicting versions: {$pluginVersions[$plugin->name]} and {$plugin->ref}",
                        conflictingPlugins: [$plugin->name],
                    );
                }
            }
            $pluginVersions[$plugin->name] = $plugin->ref;
        }

        // Check for incompatible plugins (example: different job drivers might conflict)
        $jobDrivers = \array_filter(
            $selectedPlugins,
            static fn(Plugin $plugin) => $plugin->category === PluginCategory::Jobs && $plugin->name !== 'jobs',
        );

        if (\count($jobDrivers) > 3) {
            $conflicts[] = new ConflictInfo(
                pluginName: 'jobs',
                conflictType: 'resource_conflict',
                message: 'Too many job drivers selected, this might cause resource conflicts',
                conflictingPlugins: \array_map(static fn(Plugin $plugin) => $plugin->name, $jobDrivers),
                severity: 'warning',
            );
        }

        return $conflicts;
    }

    /**
     * @param array<Plugin> $plugins
     * @return array<VersionSuggestion>
     */
    public function suggestCompatibleVersions(array $plugins): array
    {
        $suggestions = [];

        foreach ($plugins as $plugin) {
            // Check if plugin version is outdated
            if (\str_starts_with($plugin->ref, 'v4.')) {
                $suggestions[] = new VersionSuggestion(
                    pluginName: $plugin->name,
                    suggestedVersion: 'v5.0.2',
                    currentVersion: $plugin->ref,
                    reason: 'Version v4.x is deprecated, please use v5.x for compatibility',
                );
            }

            // Check for master branch usage
            if ($plugin->ref === 'master') {
                $suggestions[] = new VersionSuggestion(
                    pluginName: $plugin->name,
                    suggestedVersion: 'v5.0.2',
                    currentVersion: $plugin->ref,
                    reason: 'Using master branch in production is not recommended, use a stable version',
                );
            }
        }

        return $suggestions;
    }

    /**
     * @param array<Plugin> $plugins
     */
    public function validatePluginCombination(array $plugins): bool
    {
        $conflicts = $this->detectConflicts($plugins);
        $errorConflicts = \array_filter(
            $conflicts,
            static fn(ConflictInfo $conflict) => $conflict->severity === 'error',
        );

        return empty($errorConflicts);
    }

    /**
     * @param array<Plugin> $resolved
     * @param array<string> $visited
     * @throws DependencyConflictException
     * @throws PluginNotFoundException
     */
    private function resolveDependenciesRecursive(Plugin $plugin, array &$resolved, array &$visited): void
    {
        if (\in_array($plugin->name, $visited)) {
            throw new DependencyConflictException(
                "Circular dependency detected for plugin: {$plugin->name}",
                $visited,
            );
        }

        $visited[] = $plugin->name;

        foreach ($plugin->dependencies as $dependencyName) {
            $dependency = $this->pluginProvider->getPluginByName($dependencyName);

            if ($dependency === null) {
                throw new PluginNotFoundException($dependencyName);
            }

            $this->resolveDependenciesRecursive($dependency, $resolved, $visited);
        }

        $resolved[] = $plugin;
        $visited = \array_filter($visited, static fn(string $name) => $name !== $plugin->name);
    }
}
