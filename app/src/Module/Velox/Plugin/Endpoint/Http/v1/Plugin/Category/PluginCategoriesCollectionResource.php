<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Endpoint\Http\v1\Plugin\Category;

use App\Application\HTTP\Response\ResourceCollection;

final class PluginCategoriesCollectionResource extends ResourceCollection
{
    public function __construct(iterable $data, string|\Closure $resource = PluginCategoryResource::class, ...$args)
    {
        parent::__construct($data, $resource, $args);
    }

    protected function wrapData(array $data): array
    {
        return [
            'data' => $data,
            'meta' => [
                'total' => \count($data),
            ],
        ];
    }
}
