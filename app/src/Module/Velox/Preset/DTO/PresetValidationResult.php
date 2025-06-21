<?php

declare(strict_types=1);

namespace App\Module\Velox\Preset\DTO;

final readonly class PresetValidationResult
{
    /**
     * @param array<string> $errors
     * @param array<string> $warnings
     * @param array<string> $recommendations
     */
    public function __construct(
        public bool $isValid,
        public array $errors = [],
        public array $warnings = [],
        public array $recommendations = [],
    ) {}
}
