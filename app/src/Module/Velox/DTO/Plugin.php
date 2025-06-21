<?php

declare(strict_types=1);

namespace App\Module\Velox\DTO;

final readonly class Plugin
{
    /**
     * @param array<string> $dependencies
     */
    public function __construct(
        public string $name,
        public string $ref,
        public string $owner,
        public string $repository,
        public PluginRepository $repositoryType,
        public PluginSource $source,
        public ?string $folder = null,
        public ?string $replace = null,
        public array $dependencies = [],
        public string $description = '',
        public string $category = '',
    ) {}
}
