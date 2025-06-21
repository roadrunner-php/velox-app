<?php

declare(strict_types=1);

namespace App\Module\Velox\DTO;

final readonly class DebugConfig
{
    public function __construct(
        public bool $enabled = false,
    ) {}
}
