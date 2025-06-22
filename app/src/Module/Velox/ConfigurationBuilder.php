<?php

declare(strict_types=1);

namespace App\Module\Velox;

use App\Module\Velox\BinaryBuilder\DTO\BuildResult;
use App\Module\Velox\BinaryBuilder\Service\BinaryBuilderService;
use App\Module\Velox\Configuration\DTO\ValidationResult;
use App\Module\Velox\Configuration\DTO\VeloxConfig;
use App\Module\Velox\Configuration\Exception\ValidationException;
use App\Module\Velox\Configuration\Service\ConfigurationGeneratorService;
use App\Module\Velox\Configuration\Service\ConfigurationValidatorService;
use App\Module\Velox\Dependency\DTO\DependencyResolution;
use App\Module\Velox\Dependency\Service\DependencyResolverService;
use App\Module\Velox\Plugin\DTO\Plugin;
use App\Module\Velox\Plugin\DTO\PluginCategory;
use App\Module\Velox\Plugin\Service\PluginProviderInterface;
use App\Module\Velox\Preset\DTO\PresetDefinition;
use App\Module\Velox\Preset\DTO\PresetMergeResult;
use App\Module\Velox\Preset\DTO\PresetValidationResult;
use App\Module\Velox\Preset\Service\PresetMergerService;
use App\Module\Velox\Preset\Service\PresetProviderInterface;
use App\Module\Velox\Preset\Service\PresetValidatorService;

final readonly class ConfigurationBuilder
{
    public function __construct(
        private PluginProviderInterface $pluginProvider,
        private DependencyResolverService $dependencyResolver,
        private ConfigurationValidatorService $validator,
        private ConfigurationGeneratorService $generator,
        private PresetProviderInterface $presetProvider,
        private PresetMergerService $presetMerger,
        private PresetValidatorService $presetValidator,
        private BinaryBuilderService $binaryBuilder,
    ) {}

    /**
     * Build configuration from selected plugin names
     *
     * @param array<string> $selectedPluginNames
     */
    public function buildConfiguration(array $selectedPluginNames, ?string $githubToken = null): VeloxConfig
    {
        return $this->generator->buildConfigFromSelection(
            selectedPluginNames: $selectedPluginNames,
            githubToken: $githubToken,
        );
    }

    /**
     * Build configuration from selected presets
     *
     * @param array<string> $presetNames
     * @throws ValidationException
     */
    public function buildConfigurationFromPresets(array $presetNames, ?string $githubToken = null): VeloxConfig
    {
        $mergeResult = $this->presetMerger->mergePresets($presetNames);

        if (!$mergeResult->isValid) {
            throw new ValidationException(
                'Preset merge failed: ' . \implode(', ', $mergeResult->conflicts),
                $mergeResult->conflicts,
            );
        }

        return $this->buildConfiguration($mergeResult->finalPlugins, $githubToken);
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
    public function generateToml(VeloxConfig $config, ?string $githubToken = null): string
    {
        return $this->generator->generateToml($config, $githubToken);
    }

    /**
     * Generate Dockerfile
     */
    public function generateDockerfile(VeloxConfig $config, string $baseImage = 'php:8.3-cli'): string
    {
        return $this->generator->generateDockerfile(config: $config, baseImage: $baseImage);
    }

    /**
     * Get all available presets
     *
     * @return array<PresetDefinition>
     */
    public function getAvailablePresets(): array
    {
        return $this->presetProvider->getAllPresets();
    }

    /**
     * Get presets by tags
     *
     * @param array<string> $tags
     * @return array<PresetDefinition>
     */
    public function getPresetsByTags(array $tags): array
    {
        return $this->presetProvider->getPresetsByTags($tags);
    }

    /**
     * Search presets
     *
     * @return array<PresetDefinition>
     */
    public function searchPresets(string $query): array
    {
        return $this->presetProvider->searchPresets($query);
    }

    /**
     * Validate preset combination
     *
     * @param array<string> $presetNames
     */
    public function validatePresets(array $presetNames): PresetValidationResult
    {
        return $this->presetValidator->validatePresets($presetNames);
    }

    /**
     * Merge multiple presets
     *
     * @param array<string> $presetNames
     */
    public function mergePresets(array $presetNames): PresetMergeResult
    {
        return $this->presetMerger->mergePresets($presetNames);
    }

    // ========== Binary Building Methods ==========

    /**
     * Build RoadRunner binary from configuration
     */
    public function buildBinary(VeloxConfig $config, string $outputDirectory): BuildResult
    {
        return $this->binaryBuilder->buildBinary($config, $outputDirectory);
    }
}
