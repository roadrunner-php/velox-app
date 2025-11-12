<?php

declare(strict_types=1);

namespace App\Module\Github\Contributors\Endpoint\Http\v1;

use App\Application\HTTP\Response\ResourceInterface;
use App\Module\Github\Contributors\ContributorsRepositoryInterface;
use Spiral\Router\Annotation\Route;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/contributors',
    description: 'Retrieve a list of GitHub contributors for the project repository. This endpoint fetches contributor information from the GitHub API including their usernames, avatar images, and contribution counts.',
    summary: 'List GitHub contributors',
    tags: ['contributors'],
    parameters: [
        new OA\Parameter(
            name: 'per_page',
            description: 'Number of contributors to return per page (max 100)',
            in: 'query',
            required: false,
            schema: new OA\Schema(
                type: 'integer',
                default: 30,
                maximum: 100,
                minimum: 1,
                example: 30,
            ),
        ),
        new OA\Parameter(
            name: 'page',
            description: 'Page number for pagination (1-based)',
            in: 'query',
            required: false,
            schema: new OA\Schema(
                type: 'integer',
                default: 1,
                minimum: 1,
                example: 1,
            ),
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'List of GitHub contributors retrieved successfully',
            content: new OA\JsonContent(ref: ContributorCollectionResource::class),
        ),
        new OA\Response(
            response: 422,
            description: 'Validation error for invalid query parameters',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'error',
                        type: 'string',
                        example: 'Parameter validation failed',
                    ),
                    new OA\Property(
                        property: 'details',
                        properties: [
                            new OA\Property(
                                property: 'per_page',
                                type: 'array',
                                items: new OA\Items(type: 'string'),
                                example: ['Must be between 1 and 100'],
                            ),
                        ],
                        type: 'object',
                    ),
                ],
            ),
        ),
        new OA\Response(
            response: 500,
            description: 'GitHub API error or server error',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'error',
                        type: 'string',
                        example: 'Failed to fetch contributors from GitHub API',
                    ),
                ],
            ),
        ),
        new OA\Response(
            response: 503,
            description: 'GitHub API rate limit exceeded',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'error',
                        type: 'string',
                        example: 'GitHub API rate limit exceeded. Please try again later.',
                    ),
                    new OA\Property(
                        property: 'retry_after',
                        description: 'Seconds until rate limit resets',
                        type: 'integer',
                        example: 3600,
                    ),
                ],
            ),
        ),
    ],
)]
final readonly class ListAction
{
    #[Route(route: 'v1/contributors', name: 'contributors.list', methods: ['GET'], group: 'api')]
    public function __invoke(
        ContributorsRepositoryInterface $repository,
        ListContributorsFilter $filter,
    ): ResourceInterface {
        $contributors = $repository->findAll(perPage: $filter->perPage, page: $filter->page);

        return new ContributorCollectionResource($contributors);
    }
}
