<?php

declare(strict_types=1);

namespace App\Module\Github\Contributors\Endpoint\Http\v1;

use App\Application\HTTP\Response\ResourceCollection;
use App\Module\Github\Contributors\Dto\Contributor;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: ContributorCollectionResource::class,
    description: 'Collection of GitHub contributors with metadata',
    properties: [
        new OA\Property(
            property: 'data',
            description: 'Array of contributor objects',
            type: 'array',
            items: new OA\Items(ref: ContributorResource::class),
        ),
        new OA\Property(
            property: 'meta',
            description: 'Metadata about the contributors collection',
            properties: [
                new OA\Property(
                    property: 'total',
                    description: 'Total number of contributors',
                    type: 'integer',
                    example: 150,
                ),
                new OA\Property(
                    property: 'total_contributions',
                    description: 'Sum of all contributions across all contributors',
                    type: 'integer',
                    example: 2540,
                ),
                new OA\Property(
                    property: 'top_contributor',
                    description: 'Contributor with the most contributions',
                    properties: [
                        new OA\Property(property: 'login', type: 'string', example: 'maintainer'),
                        new OA\Property(property: 'contributions_count', type: 'integer', example: 89),
                    ],
                    type: 'object',
                    nullable: true,
                ),
            ],
            type: 'object',
        ),
    ],
    example: [
        'data' => [
            [
                'login' => 'octocat',
                'avatar_url' => 'https://avatars.githubusercontent.com/u/1?v=4',
                'profile_url' => 'https://github.com/octocat',
                'contributions_count' => 42,
            ],
        ],
        'meta' => [
            'total' => 150,
            'total_contributions' => 2540,
            'top_contributor' => [
                'login' => 'maintainer',
                'contributions_count' => 89,
            ],
        ],
    ],
)]
final class ContributorCollectionResource extends ResourceCollection
{
    /**
     * @param iterable<Contributor> $data
     */
    public function __construct(
        iterable $data,
        string|\Closure $resource = ContributorResource::class,
        ...$args,
    ) {
        parent::__construct($data, $resource, $args);
    }

    #[\Override]
    protected function wrapData(array $data): array
    {
        $contributors = $this->data;
        $contributorArray = \is_array($contributors) ? $contributors : \iterator_to_array($contributors);

        $stats = $this->calculateStats($contributorArray);

        return [
            'data' => $data,
            'meta' => [
                'total' => \count($data),
                'total_contributions' => $stats['total_contributions'],
                'top_contributor' => $stats['top_contributor'],
            ],
        ];
    }

    /**
     * @param array<Contributor> $contributors
     * @return array{total_contributions: int, top_contributor: array{login: string, contributions_count: int}|null}
     */
    private function calculateStats(array $contributors): array
    {
        $totalContributions = 0;
        $topContributor = null;
        $maxContributions = 0;

        foreach ($contributors as $contributor) {
            $contributions = $contributor->contributionsCount ?? 0;
            $totalContributions += $contributions;

            if ($contributions > $maxContributions) {
                $maxContributions = $contributions;
                $topContributor = [
                    'login' => $contributor->login,
                    'contributions_count' => $contributions,
                ];
            }
        }

        return [
            'total_contributions' => $totalContributions,
            'top_contributor' => $topContributor,
        ];
    }
}
