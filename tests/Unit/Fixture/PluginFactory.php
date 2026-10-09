<?php

declare(strict_types=1);

namespace Tests\Unit\Fixture;

use App\Module\Velox\Plugin\DTO\Plugin;
use App\Module\Velox\Plugin\DTO\PluginCategory;
use App\Module\Velox\Plugin\DTO\PluginRepository;
use App\Module\Velox\Plugin\DTO\PluginSource;

final class PluginFactory
{
    /**
     * @param array<string> $dependencies
     */
    public static function plugin(
        string $name,
        array $dependencies = [],
        string $ref = 'v5.0.2',
        ?PluginCategory $category = null,
        PluginSource $source = PluginSource::Official,
        PluginRepository $repositoryType = PluginRepository::Github,
        string $description = '',
        ?string $folder = null,
        ?string $replace = null,
        string $owner = 'roadrunner-server',
    ): Plugin {
        return new Plugin(
            name: $name,
            ref: $ref,
            owner: $owner,
            repository: $name,
            repositoryType: $repositoryType,
            source: $source,
            folder: $folder,
            replace: $replace,
            dependencies: $dependencies,
            description: $description,
            category: $category,
        );
    }
}
