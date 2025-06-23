<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Endpoint\Http\v1\Plugin;

use App\Module\Velox\ConfigurationBuilder;
use App\Module\Velox\Plugin\DTO\Plugin;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Spiral\Http\ResponseWrapper;
use Spiral\Router\Annotation\Route;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/v1/plugins/generate-config',
    description: 'Generate a complete RoadRunner configuration file in the specified format from a list of selected plugins. The system automatically resolves dependencies and includes all required plugins.',
    summary: 'Generate RoadRunner configuration from selected plugins',
    requestBody: new OA\RequestBody(
        description: 'Plugin selection and format configuration',
        required: true,
        content: new OA\JsonContent(ref: GenerateConfigFilter::class),
    ),
    tags: ['plugins', 'configuration'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Configuration generated successfully',
            content: [
                'text/plain' => new OA\MediaType(
                    mediaType: 'text/plain',
                    schema: new OA\Schema(
                        description: 'Generated configuration content in the requested format',
                        type: 'string',
                    ),
                ),
            ],
        ),
        new OA\Response(
            response: 422,
            description: 'Validation error - invalid plugins or format specified',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'error',
                        type: 'string',
                        example: 'Plugin validation failed',
                    ),
                    new OA\Property(
                        property: 'details',
                        properties: [
                            new OA\Property(
                                property: 'plugins',
                                type: 'array',
                                items: new OA\Items(type: 'string'),
                                example: ['Unknown plugin: invalid-plugin'],
                            ),
                        ],
                        type: 'object',
                    ),
                ],
            ),
        ),
        new OA\Response(
            response: 500,
            description: 'Configuration generation failed',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'error',
                        type: 'string',
                        example: 'Failed to generate configuration',
                    ),
                ],
            ),
        ),
    ],
)]
final readonly class GenerateConfigAction
{
    #[Route(
        route: 'v1/plugins/generate-config',
        name: 'plugin.generate-config',
        methods: ['POST'],
        group: 'api',
    )]
    public function __invoke(
        ConfigurationBuilder $builder,
        GenerateConfigFilter $filter,
        ResponseWrapper $response,
    ): ResponseInterface {
        $pluginNames = $filter->plugins;
        $format = $filter->format;

        $deps = $builder->resolveDependencies($pluginNames);

        $pluginNames = \array_unique([
            ...$pluginNames,
            ...\array_values(
                \array_map(
                    static fn(Plugin $plugin): string => $plugin->name,
                    $deps->requiredPlugins,
                ),
            ),
        ]);

        // Generate configuration
        $config = $builder->buildConfiguration($pluginNames, '${RT_TOKEN}');

        $result = match ($format) {
            ConfigFormat::TOML => $builder->generateToml($config),
            ConfigFormat::JSON => \json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            ConfigFormat::Docker, ConfigFormat::Dockerfile => $builder->generateDockerfile($config),
        };

        return $response
            ->create(200)
            ->withHeader('Content-Type', 'text/plain')
            ->withBody(Stream::create($result));
    }
}
