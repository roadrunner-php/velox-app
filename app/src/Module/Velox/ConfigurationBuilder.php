<?php

declare(strict_types=1);

namespace App\Module\Velox;

use App\Module\Velox\Configuration\DTO\ValidationResult;
use App\Module\Velox\Configuration\DTO\VeloxConfig;
use App\Module\Velox\Configuration\Service\ConfigurationGeneratorService;
use App\Module\Velox\Configuration\Service\ConfigurationValidatorService;
use App\Module\Velox\Dependency\DTO\DependencyResolution;
use App\Module\Velox\Dependency\Service\DependencyResolverService;
use App\Module\Velox\Plugin\DTO\Plugin;
use App\Module\Velox\Plugin\DTO\PluginCategory;
use App\Module\Velox\Plugin\Service\PluginProviderInterface;

final readonly class ConfigurationBuilder
{
    public function __construct(
        private PluginProviderInterface $pluginProvider,
        private DependencyResolverService $dependencyResolver,
        private ConfigurationValidatorService $validator,
        private ConfigurationGeneratorService $generator,
    ) {}

    /**
     * Build configuration from selected plugin names
     *
     * @param array<string> $selectedPluginNames
     */
    public function buildConfiguration(
        array $selectedPluginNames,
    ): VeloxConfig {
        return $this->generator->buildConfigFromSelection(
            selectedPluginNames: $selectedPluginNames,
        );
    }

    /**
     * Get all available plugins
     *
     * @return array<Plugin>
     */
    public function getAvailablePlugins(): array
    {
        return $this->pluginProvider->getAllPlugins();
    }

    /**
     * Get plugins by category
     *
     * @return array<Plugin>
     */
    public function getPluginsByCategory(PluginCategory $category): array
    {
        return $this->pluginProvider->getPluginsByCategory($category);
    }

    /**
     * Search plugins by query
     *
     * @return array<Plugin>
     */
    public function searchPlugins(string $query): array
    {
        return $this->pluginProvider->searchPlugins($query);
    }

    /**
     * Resolve dependencies for selected plugins
     *
     * @param array<string> $selectedPluginNames
     */
    public function resolveDependencies(array $selectedPluginNames): DependencyResolution
    {
        $plugins = [];
        foreach ($selectedPluginNames as $pluginName) {
            $plugin = $this->pluginProvider->getPluginByName($pluginName);
            if ($plugin !== null) {
                $plugins[] = $plugin;
            }
        }

        return $this->dependencyResolver->resolveDependencies($plugins);
    }

    /**
     * Validate configuration
     */
    public function validateConfiguration(VeloxConfig $config): ValidationResult
    {
        return $this->validator->validateConfiguration($config);
    }

    /**
     * Generate TOML configuration
     */
    public function generateToml(VeloxConfig $config): string
    {
        return $this->generator->generateToml($config);
    }

    /**
     * Generate Dockerfile
     */
    public function generateDockerfile(VeloxConfig $config, string $baseImage = 'php:8.3-cli'): string
    {
        return $this->generator->generateDockerfile($config, $baseImage);
    }

    /**
     * Get plugin statistics
     *
     * @return array{total: int, official: int, community: int, categories: array<string, int>}
     */
    public function getPluginStatistics(): array
    {
        $allPlugins = $this->pluginProvider->getAllPlugins();
        $categories = [];

        foreach ($allPlugins as $plugin) {
            if ($plugin->category !== null) {
                $categoryName = $plugin->category->value;
                $categories[$categoryName] = ($categories[$categoryName] ?? 0) + 1;
            }
        }

        return [
            'total' => \count($allPlugins),
            'official' => \count($this->pluginProvider->getOfficialPlugins()),
            'community' => \count($this->pluginProvider->getCommunityPlugins()),
            'categories' => $categories,
        ];
    }
}
