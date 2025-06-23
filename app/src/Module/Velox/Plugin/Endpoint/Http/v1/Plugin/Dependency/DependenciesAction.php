<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Endpoint\Http\v1\Plugin\Dependency;

use App\Application\HTTP\Response\ResourceInterface;
use App\Module\Velox\ConfigurationBuilder;
use Spiral\Http\Exception\ClientException\NotFoundException;
use Spiral\Router\Annotation\Route;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/plugin/{name}/dependencies',
    description: 'Retrieve all dependencies required by a specific plugin, including transitive dependencies, and any potential conflicts that may arise when using this plugin.',
    summary: 'Get plugin dependencies and conflicts',
    tags: ['plugins', 'dependencies'],
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
            description: 'Plugin dependencies resolved successfully',
            content: new OA\JsonContent(ref: PluginDependenciesResource::class),
        ),
        new OA\Response(
            response: 404,
            description: 'Plugin not found',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'error',
                        type: 'string',
                        example: "Plugin with name 'unknown-plugin' not found.",
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
final readonly class DependenciesAction
{
    #[Route(route: 'v1/plugin/<name>/dependencies', name: 'plugin.dependencies', methods: ['GET'], group: 'api')]
    public function __invoke(ConfigurationBuilder $builder, GetDependenciesFilter $filter): ResourceInterface
    {
        // Find the plugin
        $plugin = null;
        foreach ($builder->getAvailablePlugins() as $p) {
            if ($p->name === $filter->name) {
                $plugin = $p;
                break;
            }
        }

        if ($plugin === null) {
            throw new NotFoundException(
                message: "Plugin with name '{$filter->name}' not found.",
            );
        }

        return new PluginDependenciesResource(
            $filter->name,
            $builder->resolveDependencies([$filter->name]),
        );
    }
}
