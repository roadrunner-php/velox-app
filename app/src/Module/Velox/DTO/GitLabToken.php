<?php

declare(strict_types=1);

namespace App\Module\Velox\DTO;

final readonly class GitLabToken implements \JsonSerializable
{
    public function __construct(
        public string $token,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'token' => $this->token,
        ];
    }
}
