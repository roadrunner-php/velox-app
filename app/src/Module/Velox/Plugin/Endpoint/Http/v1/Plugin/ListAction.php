<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Endpoint\Http\v1\Plugin;

use App\Application\HTTP\Response\ResourceInterface;
use App\Module\Velox\ConfigurationBuilder;
use Spiral\Router\Annotation\Route;

final readonly class ListAction
{
    #[Route(route: 'v1/plugins', name: 'plugin.list', methods: ['GET'], group: 'api')]
    public function __invoke(ConfigurationBuilder $builder, ListPluginsFilter $filter): ResourceInterface
    {
        // Start with all plugins
        $plugins = $builder->getAvailablePlugins();

        // Apply category filter
        $categoryEnum = $filter->category;
        if ($categoryEnum !== null) {
            $plugins = $builder->getPluginsByCategory($categoryEnum);
        }

        // Apply source filter
        $sourceEnum = $filter->source;
        if ($sourceEnum !== null) {
            $plugins = \array_filter(
                $plugins,
                static fn($plugin) => $plugin->source === $sourceEnum,
            );
        }

        // Apply search filter
        if ($filter->search !== null && $filter->search !== '') {
            $plugins = $builder->searchPlugins($filter->search);
        }

        return new PluginCollectionResource($plugins);
    }
}
