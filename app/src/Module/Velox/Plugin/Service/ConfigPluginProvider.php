<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Service;

use App\Module\Velox\Plugin\DTO\Plugin;
use App\Module\Velox\Plugin\DTO\PluginCategory;
use App\Module\Velox\Plugin\DTO\PluginSource;

final readonly class ConfigPluginProvider implements PluginProviderInterface
{
    /**
     * @param array<Plugin> $plugins
     */
    public function __construct(
        private array $plugins,
    ) {}

    public function getAllPlugins(): array
    {
        return $this->plugins;
    }

    public function getPluginsByCategory(PluginCategory $category): array
    {
        return \array_filter($this->plugins, static fn(Plugin $plugin) => $plugin->category === $category);
    }

    public function getOfficialPlugins(): array
    {
        return \array_filter($this->plugins, static fn(Plugin $plugin) => $plugin->source === PluginSource::Official);
    }

    public function getCommunityPlugins(): array
    {
        return \array_filter($this->plugins, static fn(Plugin $plugin) => $plugin->source === PluginSource::Community);
    }

    public function searchPlugins(string $query): array
    {
        $query = \strtolower($query);
        return \array_filter(
            $this->plugins,
            static fn(Plugin $plugin) => \str_contains(\strtolower($plugin->name), $query) ||
                \str_contains(\strtolower($plugin->description), $query) ||
                ($plugin->category && \str_contains(\strtolower($plugin->category->value), $query)),
        );
    }

    public function getPluginByName(string $name): ?Plugin
    {
        foreach ($this->plugins as $plugin) {
            if ($plugin->name === $name) {
                return $plugin;
            }
        }

        return null;
    }
}
