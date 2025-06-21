<?php

declare(strict_types=1);

namespace App\Module\Velox\DTO;

final readonly class GitLabEndpoint implements \JsonSerializable
{
    public function __construct(
        public string $endpoint = 'https://gitlab.com',
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'endpoint' => $this->endpoint,
        ];
    }
}
