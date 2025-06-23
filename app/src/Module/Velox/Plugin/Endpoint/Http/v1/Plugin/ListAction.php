<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Endpoint\Http\v1\Plugin;

use App\Application\HTTP\Response\ResourceInterface;
use App\Module\Velox\ConfigurationBuilder;
use Spiral\Router\Annotation\Route;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/plugins',
    description: 'Retrieve a filtered list of available RoadRunner plugins with optional filtering by category, source, and search terms.',
    summary: 'List all available RoadRunner plugins',
    tags: ['plugins'],
    parameters: [
        new OA\Parameter(
            name: 'category',
            description: 'Filter plugins by category',
            in: 'query',
            required: false,
            schema: new OA\Schema(
                type: 'string',
                enum: [
                    'core',
                    'logging',
                    'http',
                    'jobs',
                    'kv',
                    'metrics',
                    'grpc',
                    'monitoring',
                    'network',
                    'broadcasting',
                    'workflow',
                    'observability',
                ],
                example: 'http',
            ),
        ),
        new OA\Parameter(
            name: 'source',
            description: 'Filter plugins by source type',
            in: 'query',
            required: false,
            schema: new OA\Schema(
                type: 'string',
                enum: ['official', 'community'],
                example: 'official',
            ),
        ),
        new OA\Parameter(
            name: 'search',
            description: 'Search plugins by name or description',
            in: 'query',
            required: false,
            schema: new OA\Schema(
                type: 'string',
                maxLength: 100,
                example: 'http',
            ),
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'List of plugins with metadata and filtering information',
            content: new OA\JsonContent(ref: PluginCollectionResource::class),
        ),
        new OA\Response(
            response: 422,
            description: 'Validation error for invalid filter parameters',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'error', type: 'string', example: 'Invalid category filter'),
                    new OA\Property(property: 'details', type: 'object'),
                ],
            ),
        ),
    ],
)]
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
