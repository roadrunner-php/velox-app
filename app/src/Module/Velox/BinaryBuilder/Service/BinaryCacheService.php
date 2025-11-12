<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryBuilder\Service;

use App\Module\Velox\BinaryBuilder\DTO\BuildResult;
use App\Module\Velox\BinaryBuilder\DTO\CacheablePluginConfig;
use App\Module\Velox\BinaryBuilder\DTO\CacheMetadata;
use App\Module\Velox\BinaryBuilder\DTO\TargetPlatform;
use App\Module\Velox\Configuration\DTO\VeloxConfig;
use Psr\Log\LoggerInterface;
use Spiral\Files\FilesInterface;

/**
 * Cache management for compiled RoadRunner binaries using local filesystem
 */
final readonly class BinaryCacheService
{
    private const string BINARY_FILENAME = 'rr';
    private const string METADATA_FILENAME = 'metadata.json';

    public function __construct(
        private FilesInterface $files,
        private string $cacheDirectory,
        private CacheKeyGenerator $keyGenerator,
        private LoggerInterface $logger,
        private int $ttl = 2592000, // 30 days default
    ) {}

    /**
     * Generate cache key for configuration
     */
    public function generateKey(VeloxConfig $config, TargetPlatform $platform): string
    {
        return $this->keyGenerator->generate($config, $platform);
    }

    /**
     * Check if binary exists in cache
     */
    public function has(string $cacheKey): bool
    {
        try {
            $binaryPath = $this->getBinaryPath($cacheKey);
            $metadataPath = $this->getMetadataPath($cacheKey);

            // Check if both binary and metadata exist
            if (!$this->files->exists($binaryPath) || !$this->files->exists($metadataPath)) {
                return false;
            }

            // Check TTL if configured
            if ($this->ttl > 0) {
                $metadata = $this->loadMetadata($cacheKey);
                if ($metadata === null) {
                    return false;
                }

                $age = \time() - $metadata->cachedAt->getTimestamp();
                if ($age > $this->ttl) {
                    $this->logger->info('Cache entry expired', [
                        'cache_key' => $cacheKey,
                        'age_seconds' => $age,
                        'ttl_seconds' => $this->ttl,
                    ]);
                    return false;
                }
            }

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Cache check failed', [
                'cache_key' => $cacheKey,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Retrieve binary from cache and copy to output path
     */
    public function get(string $cacheKey, string $outputPath): ?BuildResult
    {
        try {
            $binaryPath = $this->getBinaryPath($cacheKey);
            $metadata = $this->loadMetadata($cacheKey);

            if ($metadata === null) {
                $this->logger->warning('Cache metadata missing', ['cache_key' => $cacheKey]);
                return null;
            }

            // Verify binary integrity
            if (!$this->verifyBinary($cacheKey, $metadata)) {
                $this->logger->warning('Cache binary verification failed', ['cache_key' => $cacheKey]);
                return null;
            }

            // Copy binary to output path
            $this->files->copy($binaryPath, $outputPath);
            \chmod($outputPath, 0755); // Make executable

            $this->logger->info('Cache hit', [
                'cache_key' => $cacheKey,
                'binary_size' => $metadata->binarySizeBytes,
            ]);

            return new BuildResult(
                success: true,
                binaryPath: $outputPath,
                buildTimeSeconds: 0.0, // No build time for cache hit
                binarySizeBytes: $metadata->binarySizeBytes,
                logs: ['Binary retrieved from cache'],
                errors: [],
                configPath: null,
                buildHash: $cacheKey,
                fromCache: true,
                cacheKey: $cacheKey,
            );
        } catch (\Exception $e) {
            $this->logger->error('Cache retrieval failed', [
                'cache_key' => $cacheKey,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Store binary in cache after successful build
     */
    public function put(string $cacheKey, string $binaryPath, VeloxConfig $config, TargetPlatform $platform): void
    {
        try {
            $binarySize = \filesize($binaryPath);
            if ($binarySize === false || $binarySize === 0) {
                throw new \RuntimeException('Invalid binary file');
            }

            // Ensure cache directory exists
            $cacheDir = $this->getCacheDirectory($cacheKey);
            $this->files->ensureDirectory($cacheDir);

            // Store binary
            $storageBinaryPath = $this->getBinaryPath($cacheKey);
            $this->files->copy($binaryPath, $storageBinaryPath);
            \chmod($storageBinaryPath, 0755); // Make executable

            // Create and store metadata
            $metadata = new CacheMetadata(
                cacheKey: $cacheKey,
                rrVersion: $config->roadrunner->ref,
                platform: $platform,
                plugins: \array_map(
                    CacheablePluginConfig::fromPlugin(...),
                    $config->getAllPlugins(),
                ),
                cachedAt: new \DateTimeImmutable(),
                binarySizeBytes: $binarySize,
            );

            $metadataPath = $this->getMetadataPath($cacheKey);
            $this->files->write($metadataPath, $metadata->toJson());

            $this->logger->info('Binary cached successfully', [
                'cache_key' => $cacheKey,
                'binary_size' => $binarySize,
            ]);
        } catch (\Exception $e) {
            // Log error but don't throw - cache storage failure shouldn't fail the build
            $this->logger->error('Cache storage failed', [
                'cache_key' => $cacheKey,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Delete cache entry
     */
    public function delete(string $cacheKey): void
    {
        try {
            $cacheDir = $this->getCacheDirectory($cacheKey);

            if ($this->files->isDirectory($cacheDir)) {
                $this->files->deleteDirectory($cacheDir);
            }

            $this->logger->info('Cache entry deleted', ['cache_key' => $cacheKey]);
        } catch (\Exception $e) {
            $this->logger->error('Cache deletion failed', [
                'cache_key' => $cacheKey,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Load metadata from cache
     */
    private function loadMetadata(string $cacheKey): ?CacheMetadata
    {
        try {
            $metadataPath = $this->getMetadataPath($cacheKey);
            $content = $this->files->read($metadataPath);
            $data = \json_decode($content, true, 512, \JSON_THROW_ON_ERROR);

            return CacheMetadata::fromArray($data);
        } catch (\Exception $e) {
            $this->logger->error('Metadata loading failed', [
                'cache_key' => $cacheKey,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Verify binary integrity
     */
    private function verifyBinary(string $cacheKey, CacheMetadata $metadata): bool
    {
        try {
            $binaryPath = $this->getBinaryPath($cacheKey);
            $size = $this->files->size($binaryPath);

            return $size === $metadata->binarySizeBytes && $size > 0;
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Get cache directory for a cache key
     */
    private function getCacheDirectory(string $cacheKey): string
    {
        return $this->cacheDirectory . '/' . $cacheKey;
    }

    /**
     * Get storage path for binary
     */
    private function getBinaryPath(string $cacheKey): string
    {
        return $this->getCacheDirectory($cacheKey) . '/' . self::BINARY_FILENAME;
    }

    /**
     * Get storage path for metadata
     */
    private function getMetadataPath(string $cacheKey): string
    {
        return $this->getCacheDirectory($cacheKey) . '/' . self::METADATA_FILENAME;
    }
}
