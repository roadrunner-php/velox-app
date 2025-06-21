<?php

declare(strict_types=1);

namespace App\Module\Velox\Dependency\DTO;

final readonly class ConflictInfo
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
}
