<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Endpoint\Http\v1\Plugin\Category;

use App\Application\HTTP\Response\ResourceCollection;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: PluginCategoriesCollectionResource::class,
    description: 'Collection of plugin categories with metadata',
    properties: [
        new OA\Property(
            property: 'data',
            description: 'Array of plugin category objects',
            type: 'array',
            items: new OA\Items(ref: PluginCategoryResource::class),
        ),
        new OA\Property(
            property: 'meta',
            description: 'Metadata about the categories collection',
            properties: [
                new OA\Property(
                    property: 'total',
                    description: 'Total number of available categories',
                    type: 'integer',
                    example: 11,
                ),
            ],
            type: 'object',
        ),
    ],
    example: [
        'data' => [
            ['value' => 'core', 'label' => 'Core'],
            ['value' => 'http', 'label' => 'HTTP'],
            ['value' => 'jobs', 'label' => 'Jobs'],
        ],
        'meta' => [
            'total' => 11,
        ],
    ]
)]
final class PluginCategoriesCollectionResource extends ResourceCollection
{
    public function __construct(iterable $data, string|\Closure $resource = PluginCategoryResource::class, ...$args)
    {
        parent::__construct($data, $resource, $args);
    }

    #[\Override]
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
