<?php

declare(strict_types=1);

namespace App\Module\Velox\Configuration\DTO;

use App\Module\Velox\Plugin\DTO\Plugin;

final readonly class VeloxConfig implements \JsonSerializable
{
    public function __construct(
        public RoadRunnerConfig $roadrunner = new RoadRunnerConfig(),
        public DebugConfig $debug = new DebugConfig(),
        public GitHubConfig $github = new GitHubConfig(),
        public GitLabConfig $gitlab = new GitLabConfig(),
        public LogConfig $log = new LogConfig(),
    ) {}

    /**
     * Get all plugins from both GitHub and GitLab configurations
     *
     * @return array<Plugin>
     */
    public function getAllPlugins(): array
    {
        return [...$this->github->plugins, ...$this->gitlab->plugins];
    }

    /**
     * Check if a plugin exists by name
     */
    public function hasPlugin(string $name): bool
    {
        foreach ($this->getAllPlugins() as $plugin) {
            if ($plugin->name === $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get plugin dependencies recursively
     *
     * @return array<string>
     */
    public function getPluginDependencies(string $pluginName): array
    {
        $plugin = $this->getPlugin($pluginName);
        if (!$plugin) {
            return [];
        }

        $dependencies = [];
        foreach ($plugin->dependencies as $dependency) {
            $dependencies[] = $dependency;
            $dependencies = [...$dependencies, ...$this->getPluginDependencies($dependency)];
        }

        return \array_unique($dependencies);
    }

    /**
     * Get plugin by name
     */
    public function getPlugin(string $name): ?Plugin
    {
        foreach ($this->getAllPlugins() as $plugin) {
            if ($plugin->name === $name) {
                return $plugin;
            }
        }

        return null;
    }

    public function jsonSerialize(): array
    {
        $data = [
            'roadrunner' => $this->roadrunner,
            'log' => $this->log,
        ];

        if ($this->debug->enabled) {
            $data['debug'] = $this->debug;
        }

        if (!empty($this->github->plugins) || $this->github->token !== null) {
            $data['github'] = $this->github;
        }

        if (!empty($this->gitlab->plugins) || $this->gitlab->token !== null) {
            $data['gitlab'] = $this->gitlab;
        }

        return $data;
    }
}
