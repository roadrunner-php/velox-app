<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryGeneration\Endpoint\Http\v1;

use App\Module\Velox\BinaryGeneration\DTO\PlatformType;
use App\Module\Velox\BinaryGeneration\Service\PlatformDetectionService;
use App\Module\Velox\Plugin\DTO\Plugin;
use App\Module\Velox\Plugin\Service\PluginProviderInterface;
use App\Module\Velox\Preset\DTO\PresetDefinition;
use App\Module\Velox\Preset\Service\PresetProviderInterface;
use Spiral\Filters\Attribute\Input\Query;
use Spiral\Filters\Model\Filter;
use Spiral\Filters\Model\FilterDefinitionInterface;
use Spiral\Filters\Model\HasFilterDefinition;
use Spiral\Validator\FilterDefinition;

final class GenerateScriptFilter extends Filter implements HasFilterDefinition
{
    #[Query]
    public ?string $presets = null;

    #[Query]
    public ?string $plugins = null;

    #[Query]
    public string $platform = 'auto';

    #[Query]
    public ?string $github_token = null;

    public function __construct(
        private readonly PluginProviderInterface $pluginProvider,
        private readonly PresetProviderInterface $presetProvider,
    ) {}

    public function filterDefinition(): FilterDefinitionInterface
    {
        $pluginNames = \array_map(
            static fn(Plugin $plugin): string => $plugin->name,
            $this->pluginProvider->getAllPlugins(),
        );

        $presetNames = \array_map(
            static fn(PresetDefinition $preset): string => $preset->name,
            $this->presetProvider->getAllPresets(),
        );

        $supportedPlatforms = \array_map(
            static fn(PlatformType $platform): string => $platform->value,
            PlatformType::cases(),
        );
        $supportedPlatforms[] = 'auto'; // Add auto-detection option

        return new FilterDefinition([
            'presets' => [
                'string',
                ['string::shorter', 500], // Allow multiple comma-separated presets
                function ($value) use ($presetNames) {
                    if (empty($value)) {
                        return true; // Optional field
                    }

                    $selectedPresets = $this->parseCommaSeparated($value);
                    $invalidPresets = \array_diff($selectedPresets, $presetNames);

                    if (!empty($invalidPresets)) {
                        return 'Invalid presets: ' . \implode(', ', $invalidPresets);
                    }

                    return true;
                },
            ],
            'plugins' => [
                'string',
                ['string::shorter', 1000], // Allow multiple comma-separated plugins
                function ($value) use ($pluginNames) {
                    if (empty($value)) {
                        return true; // Optional field
                    }

                    $selectedPlugins = $this->parseCommaSeparated($value);
                    $invalidPlugins = \array_diff($selectedPlugins, $pluginNames);

                    if (!empty($invalidPlugins)) {
                        return 'Invalid plugins: ' . \implode(', ', $invalidPlugins);
                    }

                    return true;
                },
            ],
            'platform' => [
                'string',
                [
                    'in_array',
                    $supportedPlatforms,
                    'error' => 'Unsupported platform. Supported: ' . \implode(', ', $supportedPlatforms),
                ],
            ],
            'github_token' => [
                'string',
                ['string::shorter', 200],
            ],
        ]);
    }

    /**
     * Get presets as array, handling comma-separated values
     *
     * @return array<string>|null
     */
    public function getPresetsArray(): ?array
    {
        if ($this->presets === null || $this->presets === '') {
            return null;
        }

        return $this->parseCommaSeparated($this->presets);
    }

    /**
     * Get plugins as array, handling comma-separated values
     *
     * @return array<string>|null
     */
    public function getPluginsArray(): ?array
    {
        if ($this->plugins === null || $this->plugins === '') {
            return null;
        }

        return $this->parseCommaSeparated($this->plugins);
    }

    /**
     * Check if request has any presets or plugins
     */
    public function hasConfiguration(): bool
    {
        return $this->getPresetsArray() !== null || $this->getPluginsArray() !== null;
    }

    /**
     * Get priority selection (presets take precedence over plugins)
     *
     * @return array<string>
     */
    public function getPrioritySelection(): array
    {
        $presets = $this->getPresetsArray();
        if ($presets !== null) {
            return $presets;
        }

        return $this->getPluginsArray() ?? [];
    }

    /**
     * Parse comma-separated string into array
     *
     * @return array<string>
     */
    private function parseCommaSeparated(string $value): array
    {
        return \array_filter(
            \array_map('trim', \explode(',', $value)),
            static fn(string $item): bool => $item !== '',
        );
    }
}
