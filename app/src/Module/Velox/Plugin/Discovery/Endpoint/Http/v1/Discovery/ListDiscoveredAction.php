<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Discovery\Endpoint\Http\v1\Discovery;

use App\Application\HTTP\Response\ResourceInterface;
use App\Module\Velox\Plugin\Discovery\Repository\DiscoveredPluginRepositoryInterface;
use OpenApi\Attributes as OA;
use Spiral\Router\Annotation\Route;

/**
 * Lists all discovered community plugins
 */
#[OA\Get(
    path: '/api/v1/plugins/discovered',
    description: 'Returns all plugins discovered from the roadrunner-plugins organization',
    summary: 'List discovered community plugins',
    tags: ['discovery'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'List of discovered plugins with metadata',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(
                            ref: \App\Module\Velox\Plugin\Endpoint\Http\v1\Plugin\PluginResource::class,
                        ),
                    ),
                    new OA\Property(
                        property: 'meta',
                        properties: [
                            new OA\Property(property: 'total', type: 'integer', example: 10),
                            new OA\Property(property: 'last_scan', type: 'string', format: 'date-time'),
                            new OA\Property(property: 'scan_duration_ms', type: 'number'),
                        ],
                        type: 'object',
                    ),
                ],
            ),
        ),
    ],
)]
final readonly class ListDiscoveredAction
{
    #[Route(
        route: 'v1/plugins/discovered',
        name: 'discovery.list',
        methods: ['GET'],
        group: 'api',
    )]
    public function __invoke(
        DiscoveredPluginRepositoryInterface $repository,
    ): ResourceInterface {
        $plugins = $repository->findAll();
        $statistics = $repository->getMetadata();

        return new DiscoveredPluginCollectionResource($plugins, $statistics);
    }
}
