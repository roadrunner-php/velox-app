<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Endpoint\Http\v1\Plugin\Category;

use App\Application\HTTP\Response\ResourceInterface;
use App\Module\Velox\Plugin\DTO\PluginCategory;
use Spiral\Router\Annotation\Route;

final class CategoriesAction
{
    #[Route(route: 'v1/plugins/categories', name: 'plugin.categories', methods: ['GET'], group: 'api')]
    public function __invoke(): ResourceInterface
    {
        $categories = PluginCategory::cases();

        return new PluginCategoriesCollectionResource($categories);
    }
}
