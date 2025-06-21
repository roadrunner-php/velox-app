<?php

declare(strict_types=1);

namespace App\Module\Velox\DTO;

final readonly class DebugConfig implements \JsonSerializable
{
    public function __construct(
        public bool $enabled = false,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'enabled' => $this->enabled,
        ];
    }
}
