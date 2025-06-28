<?php

declare(strict_types=1);

namespace App\Module\Github\Contributors\Endpoint\Http\v1;

use Spiral\Filters\Attribute\Input\Query;
use Spiral\Filters\Model\Filter;
use Spiral\Filters\Model\FilterDefinitionInterface;
use Spiral\Filters\Model\HasFilterDefinition;
use Spiral\Validator\FilterDefinition;

final class ListContributorsFilter extends Filter implements HasFilterDefinition
{
    #[Query(key: 'per_page')]
    public int $perPage = 30;

    #[Query]
    public int $page = 1;

    public function filterDefinition(): FilterDefinitionInterface
    {
        return new FilterDefinition([
            'perPage' => [
                'numeric',
                ['number::higher', 1],
                ['number::lower', 100],
            ],
            'page' => [
                'numeric',
                ['number::higher', 1],
            ],
        ]);
    }
}
