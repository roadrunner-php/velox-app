<?php

declare(strict_types=1);

namespace App\Module\Velox\DTO;

final readonly class GitHubConfig
{
    /**
     * @param array<Plugin> $plugins
     */
    public function __construct(
        public ?GitHubToken $token = null,
        public array $plugins = [],
    ) {}
}
