<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryBuilder\DTO;

use App\Module\Velox\Plugin\DTO\Plugin;

/**
 * Normalized plugin configuration for cache key generation
 * Contains only fields that affect binary output
 */
final readonly class CacheablePluginConfig implements \JsonSerializable
{
    public function __construct(
        public string $name,
        public string $ref,
        public string $owner,
        public string $repository,
    ) {}

    public static function fromPlugin(Plugin $plugin): self
    {
        return new self(
            name: $plugin->name,
            ref: $plugin->ref,
            owner: $plugin->owner,
            repository: $plugin->repository,
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'ref' => $this->ref,
            'owner' => $this->owner,
            'repository' => $this->repository,
        ];
    }
}
