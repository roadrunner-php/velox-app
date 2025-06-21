<?php

declare(strict_types=1);

namespace App\Module\Velox\Preset\Service;

use App\Module\Velox\Preset\DTO\PresetDefinition;
use App\Module\Velox\Preset\DTO\PresetMergeResult;
use App\Module\Velox\Preset\Exception\PresetException;

final readonly class PresetMergerService
{
    public function __construct(
        private PresetProviderInterface $presetProvider,
    ) {}

    /**
     * Merge multiple presets into a single plugin list
     *
     * @param array<string> $presetNames
     */
    public function mergePresets(array $presetNames): PresetMergeResult
    {
        if (empty($presetNames)) {
            return new PresetMergeResult(
                mergedPresets: [],
                finalPlugins: [],
            );
        }

        $presets = [];
        $missingPresets = [];

        // Collect all presets
        foreach ($presetNames as $presetName) {
            $preset = $this->presetProvider->getPresetByName($presetName);
            if ($preset === null) {
                $missingPresets[] = $presetName;
                continue;
            }
            $presets[] = $preset;
        }

        if (!empty($missingPresets)) {
            throw new PresetException(
                'Presets not found: ' . \implode(', ', $missingPresets),
                $missingPresets,
            );
        }

        // Sort presets by priority (higher priority first)
        \usort($presets, static fn($a, $b) => $b->priority <=> $a->priority);

        $finalPlugins = [];
        $conflicts = [];
        $warnings = [];

        // Merge plugins from all presets
        foreach ($presets as $preset) {
            foreach ($preset->pluginNames as $pluginName) {
                if (!\in_array($pluginName, $finalPlugins)) {
                    $finalPlugins[] = $pluginName;
                }
            }
        }

        // Check for potential conflicts
        $conflictWarnings = $this->detectPresetConflicts($presets);
        if (!empty($conflictWarnings)) {
            $warnings = [...$warnings, ...$conflictWarnings];
        }

        return new PresetMergeResult(
            mergedPresets: $presetNames,
            finalPlugins: $finalPlugins,
            conflicts: $conflicts,
            warnings: $warnings,
            isValid: empty($conflicts),
        );
    }

    /**
     * Check if presets can be merged together
     *
     * @param array<string> $presetNames
     */
    public function canMergePresets(array $presetNames): bool
    {
        try {
            $result = $this->mergePresets($presetNames);
            return $result->isValid;
        } catch (PresetException) {
            return false;
        }
    }

    /**
     * Get recommended presets based on selected plugins
     *
     * @param array<string> $selectedPlugins
     * @return array<string>
     */
    public function getRecommendedPresets(array $selectedPlugins): array
    {
        $allPresets = $this->presetProvider->getAllPresets();
        $recommendations = [];

        foreach ($allPresets as $preset) {
            $matchingPlugins = \array_intersect($preset->pluginNames, $selectedPlugins);
            $matchPercentage = \count($matchingPlugins) / \count($preset->pluginNames);

            // Recommend if more than 70% of preset plugins are already selected
            if ($matchPercentage > 0.7) {
                $recommendations[] = $preset->name;
            }
        }

        return $recommendations;
    }

    /**
     * Detect potential conflicts between presets
     *
     * @param array<PresetDefinition> $presets
     * @return array<string>
     */
    private function detectPresetConflicts(array $presets): array
    {
        $warnings = [];

        // Check for overlapping functionality
        $functionalityGroups = [
            'job-drivers' => ['amqp', 'sqs', 'beanstalk', 'nats', 'kafka', 'googlepubsub'],
            'kv-storage' => ['redis', 'memory', 'boltdb', 'memcached'],
            'http-middleware' => ['gzip', 'headers', 'static', 'proxy', 'send'],
        ];

        foreach ($functionalityGroups as $groupName => $groupPlugins) {
            $selectedFromGroup = [];
            foreach ($presets as $preset) {
                $matchingPlugins = \array_intersect($preset->pluginNames, $groupPlugins);
                if (!empty($matchingPlugins)) {
                    $selectedFromGroup = [...$selectedFromGroup, ...$matchingPlugins];
                }
            }

            if (\count(\array_unique($selectedFromGroup)) > 1) {
                $warnings[] = "Multiple {$groupName} selected, ensure they are compatible: " .
                    \implode(', ', \array_unique($selectedFromGroup));
            }
        }

        return $warnings;
    }
}
