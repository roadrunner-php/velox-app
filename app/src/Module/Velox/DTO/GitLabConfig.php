<?php

declare(strict_types=1);

namespace App\Module\Velox\DTO;

final readonly class GitLabConfig
{
    /**
     * @param array<Plugin> $plugins
     */
    public function __construct(
        public ?GitLabToken $token = null,
        public ?GitLabEndpoint $endpoint = null,
        public array $plugins = [],
    ) {}
}
