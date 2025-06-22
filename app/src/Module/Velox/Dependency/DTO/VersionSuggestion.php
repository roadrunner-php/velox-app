<?php

declare(strict_types=1);

namespace App\Module\Velox\Dependency\DTO;

final readonly class VersionSuggestion implements \JsonSerializable
{
    public function __construct(
        public string $pluginName,
        public string $suggestedVersion,
        public string $currentVersion,
        public string $reason,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'plugin_name' => $this->pluginName,
            'suggested_version' => $this->suggestedVersion,
            'current_version' => $this->currentVersion,
            'reason' => $this->reason,
        ];
    }
}
