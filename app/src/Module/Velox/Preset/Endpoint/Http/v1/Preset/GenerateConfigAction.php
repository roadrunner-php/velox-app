<?php

declare(strict_types=1);

namespace App\Module\Velox\Preset\Endpoint\Http\v1\Preset;

use App\Module\Velox\ConfigurationBuilder;
use App\Module\Velox\Plugin\Endpoint\Http\v1\Plugin\ConfigFormat;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Spiral\Http\Exception\ClientException;
use Spiral\Http\ResponseWrapper;
use Spiral\Router\Annotation\Route;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/v1/presets/generate-config',
    description: 'Generate a complete RoadRunner configuration file from one or more preset configurations. Presets are predefined plugin combinations optimized for specific use cases. The system automatically merges presets and resolves conflicts.',
    summary: 'Generate RoadRunner configuration from selected presets',
    requestBody: new OA\RequestBody(
        description: 'Preset selection and format configuration',
        required: true,
        content: new OA\JsonContent(ref: GenerateConfigFromPresetsFilter::class),
    ),
    tags: ['presets', 'configuration'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Configuration generated successfully from presets',
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
            description: 'Preset validation failed - invalid presets specified or preset conflicts detected',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'error',
                        type: 'string',
                        example: 'Preset validation failed: conflicting presets detected',
                    ),
                    new OA\Property(
                        property: 'details',
                        properties: [
                            new OA\Property(
                                property: 'presets',
                                type: 'array',
                                items: new OA\Items(type: 'string'),
                                example: ['Unknown preset: invalid-preset'],
                            ),
                            new OA\Property(
                                property: 'conflicts',
                                type: 'array',
                                items: new OA\Items(type: 'string'),
                                example: ['web and api presets have conflicting middleware configurations'],
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
                        example: 'Failed to generate configuration from presets',
                    ),
                ],
            ),
        ),
    ],
)]
final readonly class GenerateConfigAction
{
    #[Route(
        route: 'v1/presets/generate-config',
        name: 'preset.generate-config',
        methods: ['POST'],
        group: 'api',
    )]
    public function __invoke(
        ConfigurationBuilder $builder,
        GenerateConfigFromPresetsFilter $filter,
        ResponseWrapper $response,
    ): ResponseInterface {
        // Validate presets first
        $validationResult = $builder->validatePresets($filter->presets);
        if (!$validationResult->isValid) {
            throw new ClientException(
                422,
                'Preset validation failed: ' . \implode(', ', $validationResult->errors),
            );
        }

        // Generate configuration
        $config = $builder->buildConfigurationFromPresets($filter->presets, '${RT_TOKEN}');

        $result = match ($filter->format) {
            ConfigFormat::TOML => $builder->generateToml($config),
            ConfigFormat::JSON => \json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            ConfigFormat::Dockerfile, ConfigFormat::Docker => $builder->generateDockerfile($config),
        };

        return $response
            ->create(200)
            ->withHeader('Content-Type', 'text/plain')
            ->withBody(Stream::create($result));
    }
}
