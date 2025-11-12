<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryBuilder;

use App\Module\Velox\BinaryBuilder\Service\BinaryCacheService;
use App\Module\Velox\BinaryBuilder\Service\CacheKeyGenerator;
use Psr\Log\LoggerInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Files\FilesInterface;

final class BinaryCacheBootloader extends Bootloader
{
    #[\Override]
    public function defineSingletons(): array
    {
        return [
            // Cache key generator
            CacheKeyGenerator::class => static fn() => new CacheKeyGenerator(),

            // Binary cache service
            BinaryCacheService::class => static fn(
                FilesInterface $files,
                DirectoriesInterface $dirs,
                CacheKeyGenerator $keyGenerator,
                LoggerInterface $logger,
                EnvironmentInterface $env,
            ): BinaryCacheService => new BinaryCacheService(
                files: $files,
                cacheDirectory: $dirs->get('runtime') . 'velox-cache',
                keyGenerator: $keyGenerator,
                logger: $logger,
                ttl: (int) $env->get('BINARY_CACHE_TTL', 2592000), // 30 days default
            ),
        ];
    }
}
