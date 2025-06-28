<?php

declare(strict_types=1);

namespace App\Module\Github\Contributors;

use Psr\SimpleCache\CacheInterface;

final readonly class CachedContributors implements ContributorsRepositoryInterface
{
    public function __construct(
        private ContributorsRepositoryInterface $repository,
        private CacheInterface $cache,
        private int $ttl = 3600,
    ) {}

    public function findAll(int $perPage = 30, int $page = 1): array
    {
        if ($this->cache->has('github_contributors')) {
            return $this->cache->get('github_contributors');
        }

        $contributors = $this->repository->findAll(perPage: $perPage, page: $page);

        $this->cache->set('github_contributors', $contributors, $this->ttl);

        return $contributors;
    }
}
