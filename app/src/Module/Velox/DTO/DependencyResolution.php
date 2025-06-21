<?php

declare(strict_types=1);

namespace App\Module\Velox\DTO;

final readonly class DependencyResolution
{
    /**
     * @param array<Plugin> $requiredPlugins
     * @param array<Plugin> $optionalPlugins
     * @param array<ConflictInfo> $conflicts
     */
    public function __construct(
        public array $requiredPlugins = [],
        public array $optionalPlugins = [],
        public array $conflicts = [],
        public bool $isValid = true,
    ) {}
}
