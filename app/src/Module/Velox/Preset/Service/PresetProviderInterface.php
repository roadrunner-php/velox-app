<?php

declare(strict_types=1);

namespace App\Module\Velox\Preset\Service;

use App\Module\Velox\Preset\DTO\PresetDefinition;

interface PresetProviderInterface
{
    /**
     * @return array<PresetDefinition>
     */
    public function getAllPresets(): array;

    /**
     * @return array<PresetDefinition>
     */
    public function getOfficialPresets(): array;

    /**
     * @return array<PresetDefinition>
     */
    public function getCommunityPresets(): array;

    /**
     * @param array<string> $tags
     * @return array<PresetDefinition>
     */
    public function getPresetsByTags(array $tags): array;

    /**
     * @return array<PresetDefinition>
     */
    public function searchPresets(string $query): array;

    public function getPresetByName(string $name): ?PresetDefinition;
}
