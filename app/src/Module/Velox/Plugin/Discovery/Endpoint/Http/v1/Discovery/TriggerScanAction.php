<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Discovery\Endpoint\Http\v1\Discovery;

use App\Application\HTTP\Response\ErrorResource;
use App\Application\HTTP\Response\ResourceInterface;
use App\Module\Velox\Plugin\Discovery\Service\GitHubDiscoveryService;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Spiral\Router\Annotation\Route;

/**
 * Triggers manual plugin discovery scan
 */
#[OA\Post(
    path: '/api/v1/plugins/discovery/scan',
    summary: 'Trigger manual plugin scan',
    description: 'Manually triggers a full scan of the roadrunner-plugins organization',
    security: [['bearerAuth' => []]],
    tags: ['discovery'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Scan completed successfully',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'status', type: 'string', example: 'success'),
                    new OA\Property(
                        property: 'statistics',
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'repositories_scanned', type: 'integer'),
                            new OA\Property(property: 'plugins_registered', type: 'integer'),
                            new OA\Property(property: 'plugins_failed', type: 'integer'),
                            new OA\Property(property: 'duration_ms', type: 'number'),
                        ],
                    ),
                ],
            ),
        ),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 500, description: 'Scan failed'),
    ],
)]
final readonly class TriggerScanAction
{
    #[Route(
        route: 'v1/plugins/discovery/scan',
        name: 'discovery.scan',
        methods: ['POST'],
        group: 'api',
    )]
    public function __invoke(
        GitHubDiscoveryService $discoveryService,
        LoggerInterface $logger,
    ): ResourceInterface {
        try {
            $logger->info('Manual discovery scan triggered');

            $result = $discoveryService->discover(force: true);

            return new \App\Application\HTTP\Response\JsonResource([
                'status' => 'success',
                'statistics' => $result->statistics->toArray(),
            ]);
        } catch (\Exception $e) {
            $logger->error('Manual scan failed', [
                'error' => $e->getMessage(),
            ]);

            return new ErrorResource('Scan failed: ' . $e->getMessage(), 500);
        }
    }
}
