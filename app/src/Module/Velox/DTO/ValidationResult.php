<?php

declare(strict_types=1);

namespace App\Module\Velox\DTO;

final readonly class ValidationResult
{
    /**
     * @param array<string> $errors
     * @param array<string> $warnings
     */
    public function __construct(
        public bool $isValid,
        public array $errors = [],
        public array $warnings = [],
    ) {}
}
