<?php

declare(strict_types=1);

namespace App\Module\Velox\Service;

use App\Module\Velox\DTO\Plugin;
use App\Module\Velox\DTO\PluginCategory;

final readonly class CompositePluginProvider implements PluginProviderInterface
{
    /**
     * @param array<PluginProviderInterface> $providers
     */
    public function __construct(
        private array $providers,
    ) {}

    public function getAllPlugins(): array
    {
        $plugins = [];
        foreach ($this->providers as $provider) {
            $plugins = [...$plugins, ...$provider->getAllPlugins()];
        }
        return $this->deduplicatePlugins($plugins);
    }

    public function getPluginsByCategory(PluginCategory $category): array
    {
        $plugins = [];
        foreach ($this->providers as $provider) {
            $plugins = [...$plugins, ...$provider->getPluginsByCategory($category)];
        }
        return $this->deduplicatePlugins($plugins);
    }

    public function getOfficialPlugins(): array
    {
        $plugins = [];
        foreach ($this->providers as $provider) {
            $plugins = [...$plugins, ...$provider->getOfficialPlugins()];
        }
        return $this->deduplicatePlugins($plugins);
    }

    public function getCommunityPlugins(): array
    {
        $plugins = [];
        foreach ($this->providers as $provider) {
            $plugins = [...$plugins, ...$provider->getCommunityPlugins()];
        }
        return $this->deduplicatePlugins($plugins);
    }

    public function searchPlugins(string $query): array
    {
        $plugins = [];
        foreach ($this->providers as $provider) {
            $plugins = [...$plugins, ...$provider->searchPlugins($query)];
        }
        return $this->deduplicatePlugins($plugins);
    }

    public function getPluginByName(string $name): ?Plugin
    {
        foreach ($this->providers as $provider) {
            $plugin = $provider->getPluginByName($name);
            if ($plugin !== null) {
                return $plugin;
            }
        }
        return null;
    }

    /**
     * @param array<Plugin> $plugins
     * @return array<Plugin>
     */
    private function deduplicatePlugins(array $plugins): array
    {
        $unique = [];
        foreach ($plugins as $plugin) {
            $unique[$plugin->name] = $plugin;
        }
        return \array_values($unique);
    }
}
