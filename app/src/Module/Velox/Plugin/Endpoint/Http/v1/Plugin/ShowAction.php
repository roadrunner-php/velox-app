<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Endpoint\Http\v1\Plugin;

use App\Application\HTTP\Response\ErrorResource;
use App\Application\HTTP\Response\ResourceInterface;
use App\Module\Velox\ConfigurationBuilder;
use Spiral\Router\Annotation\Route;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/plugin/{name}',
    description: 'Retrieve detailed information about a specific RoadRunner plugin by its name.',
    summary: 'Get detailed information about a specific plugin',
    tags: ['plugins'],
    parameters: [
        new OA\Parameter(
            name: 'name',
            description: 'The unique name/identifier of the plugin',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'string',
                maxLength: 100,
                pattern: '^[a-zA-Z0-9_-]+$',
                example: 'http',
            ),
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Plugin information retrieved successfully',
            content: new OA\JsonContent(ref: PluginResource::class),
        ),
        new OA\Response(
            response: 404,
            description: 'Plugin not found',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'error',
                        type: 'string',
                        example: "Plugin 'unknown-plugin' not found",
                    ),
                    new OA\Property(
                        property: 'code',
                        type: 'integer',
                        example: 404,
                    ),
                ],
            ),
        ),
        new OA\Response(
            response: 422,
            description: 'Invalid plugin name format',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'error',
                        type: 'string',
                        example: 'Plugin name validation failed',
                    ),
                    new OA\Property(
                        property: 'details',
                        type: 'object',
                    ),
                ],
            ),
        ),
    ]
)]
final readonly class ShowAction
{
    #[Route(route: 'v1/plugin/<name>', name: 'plugin.show', methods: ['GET'], group: 'api')]
    public function __invoke(ConfigurationBuilder $builder, ShowPluginFilter $filter): ResourceInterface
    {
        $plugins = $builder->getAvailablePlugins();

        // Find plugin by name
        foreach ($plugins as $plugin) {
            if ($plugin->name === $filter->name) {
                return new PluginResource($plugin);
            }
        }

        return new ErrorResource(
            new \Exception("Plugin '{$filter->name}' not found", 404),
        );
    }
}
