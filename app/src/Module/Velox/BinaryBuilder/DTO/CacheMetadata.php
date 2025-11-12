<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryBuilder\DTO;

/**
 * Metadata stored alongside cached binary
 */
final readonly class CacheMetadata implements \JsonSerializable
{
    /**
     * @param array<CacheablePluginConfig> $plugins
     */
    public function __construct(
        public string $cacheKey,
        public string $rrVersion,
        public TargetPlatform $platform,
        public array $plugins,
        public \DateTimeImmutable $cachedAt,
        public int $binarySizeBytes,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            cacheKey: $data['cache_key'],
            rrVersion: $data['rr_version'],
            platform: TargetPlatform::fromStrings($data['platform']['os'], $data['platform']['arch']),
            plugins: \array_map(
                static fn(array $p) => new CacheablePluginConfig($p['name'], $p['ref'], $p['owner'], $p['repository']),
                $data['plugins'],
            ),
            cachedAt: new \DateTimeImmutable($data['cached_at']),
            binarySizeBytes: $data['binary_size_bytes'],
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'cache_key' => $this->cacheKey,
            'rr_version' => $this->rrVersion,
            'platform' => $this->platform->toArray(),
            'plugins' => $this->plugins,
            'cached_at' => $this->cachedAt->format(\DateTimeInterface::ATOM),
            'binary_size_bytes' => $this->binarySizeBytes,
        ];
    }

    public function toJson(): string
    {
        return \json_encode($this, \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT);
    }
}
