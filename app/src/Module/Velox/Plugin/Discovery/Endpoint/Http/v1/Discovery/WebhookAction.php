<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Discovery\Endpoint\Http\v1\Discovery;

use App\Application\HTTP\Response\ErrorResource;
use App\Application\HTTP\Response\ResourceInterface;
use App\Module\Velox\Plugin\Discovery\Exception\ManifestValidationException;
use App\Module\Velox\Plugin\Discovery\Service\GitHubDiscoveryService;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Spiral\Router\Annotation\Route;

/**
 * Receives GitHub webhook notifications for plugin updates
 */
#[OA\Post(
    path: '/api/v1/plugins/discovery/webhook',
    summary: 'GitHub webhook for plugin releases',
    description: 'Receives GitHub release webhook events to update plugins in real-time',
    tags: ['discovery'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['action', 'release', 'repository'],
            properties: [
                new OA\Property(property: 'action', type: 'string', example: 'published'),
                new OA\Property(
                    property: 'release',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'tag_name', type: 'string', example: 'v1.2.0'),
                        new OA\Property(property: 'draft', type: 'boolean'),
                        new OA\Property(property: 'prerelease', type: 'boolean'),
                    ],
                ),
                new OA\Property(
                    property: 'repository',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'name', type: 'string', example: 'redis-cache'),
                        new OA\Property(
                            property: 'owner',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'login', type: 'string', example: 'roadrunner-plugins'),
                            ],
                        ),
                    ],
                ),
            ],
        ),
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Plugin updated successfully',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'status', type: 'string', example: 'success'),
                    new OA\Property(
                        property: 'plugin',
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'name', type: 'string'),
                            new OA\Property(property: 'version', type: 'string'),
                            new OA\Property(property: 'updated', type: 'boolean'),
                        ],
                    ),
                ],
            ),
        ),
        new OA\Response(response: 401, description: 'Invalid webhook secret'),
        new OA\Response(response: 422, description: 'Validation failed'),
    ],
)]
final readonly class WebhookAction
{
    #[Route(
        route: 'v1/plugins/discovery/webhook',
        name: 'discovery.webhook',
        methods: ['POST'],
        group: 'api',
        middleware: [\App\Module\Velox\Plugin\Discovery\Endpoint\Http\Middleware\WebhookMiddleware::class],
    )]
    public function __invoke(
        GitHubDiscoveryService $discoveryService,
        WebhookFilter $filter,
        LoggerInterface $logger,
    ): ResourceInterface {
        // Skip drafts and prereleases
        if ($filter->isDraft() || $filter->isPrerelease()) {
            $logger->info('Skipping draft/prerelease webhook', [
                'repository' => $filter->getRepositoryName(),
                'tag' => $filter->getTagName(),
            ]);

            return new \App\Application\HTTP\Response\JsonResource([
                'status' => 'skipped',
                'reason' => 'Draft or prerelease',
            ]);
        }

        try {
            $plugin = $discoveryService->updateFromWebhook(
                $filter->getRepositoryName(),
                $filter->getTagName(),
            );

            if ($plugin === null) {
                return new \App\Application\HTTP\Response\JsonResource([
                    'status' => 'skipped',
                    'reason' => 'No valid plugin found',
                ]);
            }

            return new \App\Application\HTTP\Response\JsonResource([
                'status' => 'success',
                'plugin' => [
                    'name' => $plugin->name,
                    'version' => $plugin->ref,
                    'updated' => true,
                ],
            ]);
        } catch (ManifestValidationException $e) {
            $logger->warning('Webhook validation failed', [
                'repository' => $filter->getRepositoryName(),
                'error' => $e->getMessage(),
            ]);

            return new ErrorResource($e->getMessage(), 422, [
                'repository' => $e->repository,
                'errors' => $e->errors,
            ]);
        } catch (\Exception $e) {
            $logger->error('Webhook processing failed', [
                'repository' => $filter->getRepositoryName(),
                'error' => $e->getMessage(),
            ]);

            return new ErrorResource('Failed to process webhook: ' . $e->getMessage(), 500);
        }
    }
}
