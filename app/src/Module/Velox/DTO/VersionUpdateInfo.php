<?php

declare(strict_types=1);

namespace App\Module\Velox\DTO;

final readonly class VersionUpdateInfo
{
    public function __construct(
        public string $pluginName,
        public string $currentVersion,
        public string $latestVersion,
        public string $updateType,
        public bool $isRecommended,
        public string $reason,
    ) {}
}
