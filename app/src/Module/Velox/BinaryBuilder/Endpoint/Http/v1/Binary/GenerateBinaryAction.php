<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryBuilder\Endpoint\Http\v1\Binary;

use App\Module\Velox\BinaryBuilder\Converter\ConfigToRequestConverter;
use App\Module\Velox\BinaryBuilder\DTO\TargetPlatform;
use App\Module\Velox\ConfigurationBuilder;
use App\Module\Velox\Plugin\DTO\Plugin;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use Spiral\Http\ResponseWrapper;
use Spiral\Router\Annotation\Route;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/v1/binary/generate',
    requestBody: new OA\RequestBody(
        description: 'Binary generation configuration with plugin selection and optional platform targeting',
        required: true,
        content: new OA\JsonContent(ref: GenerateBinaryFilter::class),
    ),
    tags: ['binary', 'build'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Binary file stream (handled by Velox middleware). PHP worker released immediately; actual build/cache/stream happens in Go.',
            headers: [
                new OA\Header(
                    header: 'Content-Type',
                    description: 'Binary content type',
                    schema: new OA\Schema(type: 'string', example: 'application/octet-stream'),
                ),
                new OA\Header(
                    header: 'Content-Disposition',
                    description: 'Attachment filename based on target platform',
                    schema: new OA\Schema(type: 'string', example: 'attachment; filename="roadrunner-linux-amd64"'),
                ),
                new OA\Header(
                    header: 'Content-Length',
                    description: 'Binary size in bytes (only for cache hits)',
                    schema: new OA\Schema(type: 'integer', example: 52428800),
                ),
                new OA\Header(
                    header: 'X-Build-Request-ID',
                    description: 'Unique build request identifier for tracing',
                    schema: new OA\Schema(type: 'string', format: 'uuid'),
                ),
                new OA\Header(
                    header: 'X-Cache-Status',
                    description: 'Cache hit/miss status',
                    schema: new OA\Schema(type: 'string', enum: ['HIT', 'MISS']),
                ),
                new OA\Header(
                    header: 'X-Cache-Age',
                    description: 'Age of cached binary in seconds (cache hits only)',
                    schema: new OA\Schema(type: 'integer', example: 3600),
                ),
                new OA\Header(
                    header: 'X-Build-Time',
                    description: 'Build duration in seconds (cache misses only)',
                    schema: new OA\Schema(type: 'number', example: 58.3),
                ),
            ],
            content: [
                'application/octet-stream' => new OA\MediaType(
                    mediaType: 'application/octet-stream',
                    schema: new OA\Schema(
                        description: 'RoadRunner binary executable',
                        type: 'string',
                        format: 'binary',
                    ),
                ),
            ],
        ),
        new OA\Response(
            response: 422,
            description: 'Validation error - invalid plugins, dependency conflicts, or unsupported platform',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'error',
                        type: 'string',
                        example: 'Dependency resolution failed',
                    ),
                    new OA\Property(
                        property: 'details',
                        properties: [
                            new OA\Property(
                                property: 'conflicts',
                                type: 'array',
                                items: new OA\Items(type: 'string'),
                                example: ['Circular dependency detected: plugin-a -> plugin-b -> plugin-a'],
                            ),
                        ],
                        type: 'object',
                    ),
                ],
            ),
        ),
        new OA\Response(
            response: 502,
            description: 'Velox server error (handled by middleware)',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'error',
                        type: 'string',
                        example: 'build_failed',
                    ),
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'velox server returned: plugin version conflict',
                    ),
                    new OA\Property(
                        property: 'request_id',
                        type: 'string',
                        format: 'uuid',
                    ),
                ],
            ),
        ),
        new OA\Response(
            response: 504,
            description: 'Build timeout (handled by middleware)',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'error',
                        type: 'string',
                        example: 'timeout',
                    ),
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'build exceeded 5m timeout',
                    ),
                    new OA\Property(
                        property: 'request_id',
                        type: 'string',
                        format: 'uuid',
                    ),
                ],
            ),
        ),
    ],
)]
final readonly class GenerateBinaryAction
{
    #[Route(
        route: 'v1/binary/generate',
        name: 'binary.generate',
        methods: ['POST'],
        group: 'api',
    )]
    public function __invoke(
        ConfigurationBuilder $configBuilder,
        ConfigToRequestConverter $converter,
        GenerateBinaryFilter $filter,
        ResponseWrapper $response,
    ): ResponseInterface {
        $pluginNames = $filter->plugins;

        // Resolve dependencies
        $deps = $configBuilder->resolveDependencies($pluginNames);

        if (!$deps->isValid) {
            return $response->json([
                'error' => 'Dependency resolution failed',
                'details' => [
                    'conflicts' => \array_map(
                        static fn($conflict) => $conflict->message,
                        $deps->conflicts,
                    ),
                ],
            ], 422);
        }

        // Merge requested plugins with required dependencies
        $allPluginNames = \array_unique([
            ...$pluginNames,
            ...\array_map(
                static fn(Plugin $plugin): string => $plugin->name,
                $deps->requiredPlugins,
            ),
        ]);

        // Build VeloxConfig from all plugins
        $config = $configBuilder->buildConfiguration($allPluginNames);

        // Parse target platform
        $currentPlatform = TargetPlatform::current();
        $targetPlatform = new TargetPlatform(
            os: $filter->targetOs ?? $currentPlatform->os,
            arch: $filter->targetArch ?? $currentPlatform->arch,
        );

        // Convert to BuildRequest for Velox middleware
        $buildRequest = $converter->convert(
            config: $config,
            targetPlatform: $targetPlatform,
            forceRebuild: $filter->forceRebuild,
            requestId: Uuid::uuid4()->toString(),
        );

        // Return BuildRequest JSON with X-Velox-Build header
        // Velox middleware will intercept this response and handle:
        // 1. Cache lookup (SHA256-based)
        // 2. Build via Velox server (if cache miss)
        // 3. Stream binary to client
        // 4. Cache result
        //
        // PHP worker is released immediately (< 100ms)
        return $response
            ->json($buildRequest->toArray())
            ->withHeader('X-Velox-Build', 'true');
    }
}
