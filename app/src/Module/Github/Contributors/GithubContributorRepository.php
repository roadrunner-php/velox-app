<?php

declare(strict_types=1);

namespace App\Module\Github\Contributors;

use App\Module\Github\Client;

final readonly class GithubContributorRepository implements ContributorsRepositoryInterface
{
    public function __construct(
        private Client $client,
    ) {}

    public function findAll(int $perPage = 30, int $page = 1): array
    {
        return $this->client->getContributors(perPage: $perPage, page: $page);
    }
}
