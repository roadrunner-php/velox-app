<?php

declare(strict_types=1);

namespace App\Module\Velox\Preset\Service;

use App\Module\Velox\Preset\DTO\PresetDefinition;

final readonly class ConfigPresetProvider implements PresetProviderInterface
{
    /**
     * @param array<PresetDefinition> $presets
     */
    public function __construct(
        private array $presets,
    ) {}

    public function getAllPresets(): array
    {
        return $this->presets;
    }

    public function getOfficialPresets(): array
    {
        return \array_filter($this->presets, static fn(PresetDefinition $preset) => $preset->isOfficial);
    }

    public function getCommunityPresets(): array
    {
        return \array_filter($this->presets, static fn(PresetDefinition $preset) => !$preset->isOfficial);
    }

    public function getPresetsByTags(array $tags): array
    {
        if (empty($tags)) {
            return [];
        }

        return \array_filter(
            $this->presets,
            static fn(PresetDefinition $preset) => !empty(\array_intersect($preset->tags, $tags)),
        );
    }

    public function searchPresets(string $query): array
    {
        $query = \strtolower($query);
        return \array_filter(
            $this->presets,
            static fn(PresetDefinition $preset) => \str_contains(\strtolower($preset->name), $query) ||
                \str_contains(\strtolower($preset->displayName), $query) ||
                \str_contains(\strtolower($preset->description), $query),
        );
    }

    public function getPresetByName(string $name): ?PresetDefinition
    {
        foreach ($this->presets as $preset) {
            if ($preset->name === $name) {
                return $preset;
            }
        }

        return null;
    }
}
