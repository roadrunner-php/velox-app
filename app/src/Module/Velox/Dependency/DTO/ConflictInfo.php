<?php

declare(strict_types=1);

namespace App\Module\Velox\Dependency\DTO;

final readonly class ConflictInfo implements \JsonSerializable
{
    /**
     * @param array<string> $conflictingPlugins
     */
    public function __construct(
        public string $pluginName,
        public string $conflictType,
        public string $message,
        public array $conflictingPlugins = [],
        public string $severity = 'error',
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'plugin_name' => $this->pluginName,
            'conflict_type' => $this->conflictType,
            'message' => $this->message,
            'conflicting_plugins' => $this->conflictingPlugins,
            'severity' => $this->severity,
        ];
    }
}
