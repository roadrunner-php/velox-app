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
        public ConflictType $conflictType,
        public string $message,
        public array $conflictingPlugins = [],
        public ConflictSeverity $severity = ConflictSeverity::Error,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'plugin_name' => $this->pluginName,
            'conflict_type' => $this->conflictType->value,
            'message' => $this->message,
            'conflicting_plugins' => $this->conflictingPlugins,
            'severity' => $this->severity->value,
        ];
    }
}
