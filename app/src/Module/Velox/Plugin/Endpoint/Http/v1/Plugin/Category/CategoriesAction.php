<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Endpoint\Http\v1\Plugin\Category;

use App\Application\HTTP\Response\ResourceInterface;
use App\Module\Velox\Plugin\DTO\PluginCategory;
use Spiral\Router\Annotation\Route;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/plugins/categories',
    description: 'Retrieve a complete list of all available plugin categories with their display names and values. Categories are used to group plugins by functionality.',
    summary: 'List all available plugin categories',
    tags: ['plugins', 'categories'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'List of all available plugin categories',
            content: new OA\JsonContent(ref: PluginCategoriesCollectionResource::class),
        ),
    ]
)]
final class CategoriesAction
{
    #[Route(route: 'v1/plugins/categories', name: 'plugin.categories', methods: ['GET'], group: 'api')]
    public function __invoke(): ResourceInterface
    {
        $categories = PluginCategory::cases();

        return new PluginCategoriesCollectionResource($categories);
    }
}
