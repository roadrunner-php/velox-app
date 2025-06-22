<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Endpoint\Http\v1\Plugin;

use App\Application\HTTP\Response\ResourceInterface;
use App\Module\Velox\ConfigurationBuilder;
use App\Module\Velox\Plugin\DTO\PluginCategory;
use App\Module\Velox\Plugin\DTO\PluginSource;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Router\Annotation\Route;

final readonly class ListAction
{
    #[Route(route: 'v1/plugins', name: 'plugin.list', methods: ['GET'], group: 'api')]
    public function __invoke(ConfigurationBuilder $builder, ServerRequestInterface $request): ResourceInterface
    {
        $params = $request->getQueryParams();

        // Get filter parameters
        $category = $params['category'] ?? null;
        $source = $params['source'] ?? null;
        $search = $params['search'] ?? null;

        // Start with all plugins
        $plugins = $builder->getAvailablePlugins();

        // Apply category filter
        if ($category !== null && $category !== '') {
            $categoryEnum = PluginCategory::tryFrom($category);
            if ($categoryEnum !== null) {
                $plugins = $builder->getPluginsByCategory($categoryEnum);
            }
        }

        // Apply source filter
        if ($source !== null && $source !== '') {
            $sourceEnum = PluginSource::tryFrom($source);
            if ($sourceEnum !== null) {
                if ($sourceEnum === PluginSource::Official) {
                    $plugins = \array_filter(
                        $plugins,
                        static fn($plugin) => $plugin->source === PluginSource::Official,
                    );
                } else {
                    $plugins = \array_filter(
                        $plugins,
                        static fn($plugin) => $plugin->source === PluginSource::Community,
                    );
                }
            }
        }

        // Apply search filter
        if ($search !== null && $search !== '') {
            $plugins = $builder->searchPlugins($search);
        }

        return new PluginCollectionResource($plugins);
    }
}
