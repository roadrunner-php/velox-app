<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryBuilder\Service;

use App\Module\Velox\BinaryBuilder\DTO\CacheablePluginConfig;
use App\Module\Velox\BinaryBuilder\DTO\TargetPlatform;
use App\Module\Velox\Configuration\DTO\VeloxConfig;

/**
 * Generates deterministic cache keys from plugin configurations
 */
final readonly class CacheKeyGenerator
{
    /**
     * Generate cache key from VeloxConfig and target platform
     */
    public function generate(VeloxConfig $config, TargetPlatform $platform): string
    {
        $cacheableData = $this->extractCacheableData($config, $platform);
        $hash = $this->hashData($cacheableData);

        return "rr_binary_{$hash}_{$platform->toString()}";
    }

    /**
     * Extract only data that affects binary output
     */
    private function extractCacheableData(VeloxConfig $config, TargetPlatform $platform): array
    {
        // Get all plugins and convert to cacheable format
        $plugins = \array_map(
            CacheablePluginConfig::fromPlugin(...),
            $config->getAllPlugins(),
        );

        // Sort plugins by name for deterministic ordering
        \usort($plugins, static fn($a, $b) => $a->name <=> $b->name);

        return [
            'rr_version' => $config->roadrunner->ref,
            'platform' => $platform->toArray(),
            'plugins' => $plugins,
        ];
    }

    /**
     * Generate SHA-256 hash from normalized data
     */
    private function hashData(array $data): string
    {
        $normalized = \json_encode($data, \JSON_THROW_ON_ERROR);
        return \hash('sha256', $normalized);
    }
}
