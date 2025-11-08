<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryGeneration\Endpoint\Http\v1;

use App\Module\Velox\BinaryGeneration\DTO\ScriptRequest;
use App\Module\Velox\BinaryGeneration\Service\PlatformDetectionService;
use App\Module\Velox\BinaryGeneration\Service\ScriptGeneratorService;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Http\ResponseWrapper;
use Spiral\Router\Annotation\Route;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: 'build.sh',
    description: 'Generate a shell script that automatically downloads, configures, and builds a custom RoadRunner binary based on selected presets and plugins. The script handles platform detection, dependency installation, and build process.',
    summary: 'Generate installation script for custom RoadRunner binary',
    tags: ['binary-generation'],
    parameters: [
        new OA\Parameter(
            name: 'presets',
            description: 'Comma-separated preset names to include',
            in: 'query',
            required: false,
            schema: new OA\Schema(
                type: 'string',
                example: 'web-server,monitoring',
            ),
        ),
        new OA\Parameter(
            name: 'plugins',
            description: 'Comma-separated plugin names to include (used if no presets specified)',
            in: 'query',
            required: false,
            schema: new OA\Schema(
                type: 'string',
                example: 'server,http,logger,metrics',
            ),
        ),
        new OA\Parameter(
            name: 'platform',
            description: 'Target platform (auto-detects from User-Agent if not specified)',
            in: 'query',
            required: false,
            schema: new OA\Schema(
                type: 'string',
                default: 'auto',
                enum: ['auto', 'linux/amd64', 'linux/arm64', 'darwin/amd64', 'darwin/arm64', 'windows/amd64'],
                example: 'linux/amd64',
            ),
        ),
        new OA\Parameter(
            name: 'github_token',
            description: 'GitHub token for authenticated API access (to avoid rate limits)',
            in: 'query',
            required: false,
            schema: new OA\Schema(
                type: 'string',
                example: 'ghp_xxxxxxxxxxxx',
            ),
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Installation script generated successfully',
            content: [
                'text/plain' => new OA\MediaType(
                    mediaType: 'text/plain',
                    schema: new OA\Schema(
                        type: 'string',
                        example: "#!/bin/bash\n# RoadRunner Binary Builder Script\n# Generated on 2024-01-15 10:30:00\n\nset -e  # Exit on any error\n\n# Download and build RoadRunner...\n",
                    ),
                ),
            ],
        ),
        new OA\Response(
            response: 422,
            description: 'Validation error - invalid parameters specified',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'error',
                        type: 'string',
                        example: 'Invalid platform specified',
                    ),
                    new OA\Property(
                        property: 'details',
                        type: 'object',
                        additionalProperties: new OA\AdditionalProperties(
                            type: 'array',
                            items: new OA\Items(type: 'string'),
                        ),
                    ),
                ],
            ),
        ),
        new OA\Response(
            response: 500,
            description: 'Script generation failed',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'error',
                        type: 'string',
                        example: 'Failed to generate installation script',
                    ),
                ],
            ),
        ),
    ],
)]
final readonly class GenerateScriptAction
{
    #[Route(
        route: 'build.sh',
        methods: ['GET'],
    )]
    public function __invoke(
        ServerRequestInterface $request,
        GenerateScriptFilter $filter,
        ScriptGeneratorService $scriptGenerator,
        PlatformDetectionService $platformDetection,
        ResponseWrapper $response,
    ): ResponseInterface {
        // Create script request from filter
        $scriptRequest = new ScriptRequest(
            presets: $filter->getPresetsArray() ?? [],
            plugins: $filter->getPluginsArray() ?? [],
            platform: $filter->platform,
            githubToken: $filter->github_token,
        );

        // Detect platform
        $platformInfo = $platformDetection->detectPlatform($request, $filter->platform);

        // Generate script
        $script = $scriptGenerator->generateScript($scriptRequest, $platformInfo);

        return $response
            ->create(200)
            ->withHeader('X-Platform-Detected', $platformInfo->platform->value)
            ->withBody(Stream::create($script));
    }
}
