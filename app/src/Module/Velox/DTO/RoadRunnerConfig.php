<?php

declare(strict_types=1);

namespace App\Module\Velox\DTO;

final readonly class RoadRunnerConfig implements \JsonSerializable
{
    public function __construct(
        public string $ref = 'master',
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'ref' => $this->ref,
        ];
    }
}
