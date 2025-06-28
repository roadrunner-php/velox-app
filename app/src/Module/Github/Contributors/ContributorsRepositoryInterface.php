<?php

declare(strict_types=1);

namespace App\Module\Github\Contributors;

use App\Module\Github\Contributors\Dto\Contributor;

interface ContributorsRepositoryInterface
{
    /**
     * @param positive-int $perPage
     * @param positive-int $page
     * @return Contributor[]
     */
    public function findAll(
        int $perPage = 30,
        int $page = 1,
    ): array;
}
