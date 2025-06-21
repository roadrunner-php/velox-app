<?php

declare(strict_types=1);

namespace App\Module\Velox\Preset\DTO;

final readonly class PresetMergeResult
{
    /**
     * @param array<string> $mergedPresets
     * @param array<string> $finalPlugins
     * @param array<string> $conflicts
     * @param array<string> $warnings
     */
    public function __construct(
        public array $mergedPresets,
        public array $finalPlugins,
        public array $conflicts = [],
        public array $warnings = [],
        public bool $isValid = true,
    ) {}
}
