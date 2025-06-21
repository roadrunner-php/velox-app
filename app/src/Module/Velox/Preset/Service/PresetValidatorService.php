<?php

declare(strict_types=1);

namespace App\Module\Velox\Preset\Service;

use App\Module\Velox\Plugin\Service\PluginProviderInterface;
use App\Module\Velox\Preset\DTO\PresetValidationResult;

final readonly class PresetValidatorService
{
    public function __construct(
        private PresetProviderInterface $presetProvider,
        private PluginProviderInterface $pluginProvider,
    ) {}

    /**
     * Validate preset combinations
     *
     * @param array<string> $presetNames
     */
    public function validatePresets(array $presetNames): PresetValidationResult
    {
        $errors = [];
        $warnings = [];

        // Check if all presets exist
        foreach ($presetNames as $presetName) {
            $preset = $this->presetProvider->getPresetByName($presetName);
            if ($preset === null) {
                $errors[] = "Preset '{$presetName}' not found";
            }
        }

        if (!empty($errors)) {
            return new PresetValidationResult(
                isValid: false,
                errors: $errors,
            );
        }

        // Validate plugin availability
        $allPlugins = [];
        foreach ($presetNames as $presetName) {
            $preset = $this->presetProvider->getPresetByName($presetName);
            $allPlugins = [...$allPlugins, ...$preset->pluginNames];
        }

        $uniquePlugins = \array_unique($allPlugins);
        foreach ($uniquePlugins as $pluginName) {
            $plugin = $this->pluginProvider->getPluginByName($pluginName);
            if ($plugin === null) {
                $errors[] = "Plugin '{$pluginName}' required by presets but not available";
            }
        }

        return new PresetValidationResult(
            isValid: empty($errors),
            errors: $errors,
            warnings: $warnings,
        );
    }
}
