<?php

declare(strict_types=1);

namespace App\Module\Velox\DTO;

final readonly class PluginMetadata
{
    /**
     * @param array<string> $configExamples
     * @param array<string> $tags
     */
    public function __construct(
        public string $name,
        public string $description,
        public string $documentation,
        public array $configExamples = [],
        public array $tags = [],
        public string $maintainer = '',
        public bool $isOfficial = false,
        public string $minRoadRunnerVersion = '',
    ) {}
}
