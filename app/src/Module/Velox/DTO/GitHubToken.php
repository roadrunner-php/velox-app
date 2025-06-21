<?php

declare(strict_types=1);

namespace App\Module\Velox\DTO;

final readonly class GitHubToken
{
    public function __construct(
        public string $token,
    ) {}
}
