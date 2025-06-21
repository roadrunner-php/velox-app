<?php

declare(strict_types=1);

namespace App\Module\Velox\Preset\DTO;

final readonly class PresetDefinition
{
    /**
     * @param array<string> $pluginNames
     * @param array<string> $tags
     */
    public function __construct(
        public string $name,
        public string $displayName,
        public string $description,
        public array $pluginNames,
        public array $tags = [],
        public bool $isOfficial = true,
        public int $priority = 0,
    ) {}
}
