<?php

declare(strict_types=1);

namespace App\Module\Github\Contributors;

use App\Module\Github\Client;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Cache\CacheStorageProviderInterface;

final class ContributorsBootloader extends Bootloader
{
    public function defineSingletons(): array
    {
        return [
            ContributorsRepositoryInterface::class => static fn(
                EnvironmentInterface $env,
                CacheStorageProviderInterface $storageProvider,
                Client $client,
            ): ContributorsRepositoryInterface => new CachedContributors(
                repository: new GithubContributorRepository($client),
                cache: $storageProvider->storage('github'),
                ttl: (int) $env->get('GITHUB_CONTRIBUTORS_CACHE_TTL', 3600),
            ),
        ];
    }
}
