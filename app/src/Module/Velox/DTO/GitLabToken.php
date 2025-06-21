<?php

declare(strict_types=1);

namespace App\Module\Velox\DTO;

final readonly class GitLabToken
{
    public function __construct(
        public string $token,
    ) {}
}
