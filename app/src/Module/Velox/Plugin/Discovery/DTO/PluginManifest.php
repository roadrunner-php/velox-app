<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Discovery\DTO;

use App\Module\Velox\Plugin\DTO\PluginCategory;

/**
 * Represents parsed .velox.yaml manifest
 */
final readonly class PluginManifest
{
    public function __construct(
        public string $name,
        public string $version,
        public string $owner,
        public string $repository,
        public string $description,
        public PluginCategory $category,
        public array $dependencies = [],
        public ?string $repositoryType = 'github',
        public ?string $folder = null,
        public ?string $replace = null,
        public ?string $docsUrl = null,
        public ?array $author = null,
        public ?string $license = null,
        public array $keywords = [],
    ) {}

    public static function fromArray(array $data, string $repositoryName, string $version): self
    {
        return new self(
            name: $data['name'] ?? $repositoryName,
            version: $version,
            owner: $data['owner'] ?? 'roadrunner-plugins',
            repository: $data['repository'] ?? $repositoryName,
            description: $data['description'] ?? '',
            category: PluginCategory::from($data['category'] ?? 'core'),
            dependencies: $data['dependencies'] ?? [],
            repositoryType: $data['repositoryType'] ?? 'github',
            folder: $data['folder'] ?? null,
            replace: $data['replace'] ?? null,
            docsUrl: $data['docsUrl'] ?? null,
            author: $data['author'] ?? null,
            license: $data['license'] ?? null,
            keywords: $data['keywords'] ?? [],
        );
    }
}
