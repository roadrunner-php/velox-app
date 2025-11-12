<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryBuilder\Endpoint\Http\v1\Binary;

use App\Module\Velox\BinaryBuilder\DTO\TargetPlatform;
use App\Module\Velox\BinaryBuilder\Exception\BuildException;
use App\Module\Velox\BinaryBuilder\Service\BinaryBuilderService;
use App\Module\Velox\ConfigurationBuilder;
use App\Module\Velox\Plugin\DTO\Plugin;
use Psr\Http\Message\ResponseInterface;
use Spiral\Files\FilesInterface;
use Spiral\Http\ResponseWrapper;
use Spiral\Router\Annotation\Route;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/v1/binary/generate',
    description: 'Generate a RoadRunner binary from a list of selected plugins. The system automatically resolves dependencies, builds the binary via Velox server, and returns detailed build information. Supports caching to speed up subsequent builds with identical configurations.',
    summary: 'Generate RoadRunner binary from selected plugins',
    requestBody: new OA\RequestBody(
        description: 'Binary generation configuration',
        required: true,
        content: new OA\JsonContent(ref: GenerateBinaryFilter::class),
    ),
    tags: ['binary', 'build'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Binary file download',
            headers: [
                new OA\Header(
                    header: 'Content-Disposition',
                    description: 'Attachment filename',
                    schema: new OA\Schema(type: 'string', example: 'attachment; filename="rr"'),
                ),
                new OA\Header(
                    header: 'Content-Length',
                    description: 'File size in bytes',
                    schema: new OA\Schema(type: 'integer', example: 52428800),
                ),
            ],
            content: [
                'application/octet-stream' => new OA\MediaType(
                    mediaType: 'application/octet-stream',
                    schema: new OA\Schema(
                        type: 'string',
                        format: 'binary',
                    ),
                ),
            ],
        ),
        new OA\Response(
            response: 422,
            description: 'Validation error - invalid plugins, OS, or architecture specified',
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
            description: 'Binary build failed',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'error',
                        type: 'string',
                        example: 'Build failed: compilation error',
                    ),
                    new OA\Property(
                        property: 'logs',
                        type: 'array',
                        items: new OA\Items(type: 'string'),
                        example: ['Build error on line 45', 'Missing dependency'],
                    ),
                ],
            ),
        ),
        new OA\Response(
            response: 503,
            description: 'Velox server unavailable',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'error',
                        type: 'string',
                        example: 'Velox server is not available',
                    ),
                ],
            ),
        ),
    ],
)]
final readonly class GenerateBinaryAction
{
    public function __construct(
        private FilesInterface $files,
    ) {}

    #[Route(
        route: 'v1/binary/generate',
        name: 'binary.generate',
        methods: ['POST'],
        group: 'api',
    )]
    public function __invoke(
        BinaryBuilderService $binaryBuilder,
        ConfigurationBuilder $configBuilder,
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

        // Parse target platform
        $currentPlatform = TargetPlatform::current();
        $targetPlatform = new TargetPlatform(
            os: $filter->targetOs ?? $currentPlatform->os,
            arch: $filter->targetArch ?? $currentPlatform->arch,
        );

        try {
            // Build binary
            $buildResult = $binaryBuilder->buildFromPluginSelection(
                selectedPlugins: $allPluginNames,
                outputDirectory: '/tmp/velox-api-builds',
                targetPlatform: $targetPlatform,
            );

            // Read file
            $size = $this->files->size($buildResult->binaryPath);
            $filename = \basename($buildResult->binaryPath);

            // Return binary response
            return $response
                ->create(200)
                ->withHeader('Content-Type', 'application/octet-stream')
                ->withHeader('Content-Disposition', "attachment; filename=\"{$filename}\"")
                ->withHeader('Content-Length', (string) $size)
                ->withHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->withHeader('X-Sendfile', $buildResult->binaryPath);
        } catch (BuildException $e) {
            return $response->json([
                'error' => $e->getMessage(),
                'logs' => $e->buildLogs,
            ], 500);
        } catch (\Exception $e) {
            return $response->json([
                'error' => 'Build failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
