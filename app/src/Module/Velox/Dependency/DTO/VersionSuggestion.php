<?php

declare(strict_types=1);

namespace App\Module\Velox\Dependency\DTO;

final readonly class VersionSuggestion
{
    public function __construct(
        public string $pluginName,
        public string $suggestedVersion,
        public string $currentVersion,
        public string $reason,
    ) {}
}
