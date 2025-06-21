<?php

declare(strict_types=1);

namespace App\Module\Velox\DTO;

final readonly class RoadRunnerConfig
{
    public function __construct(
        public string $ref = 'master',
    ) {}
}
