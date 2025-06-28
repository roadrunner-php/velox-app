<?php

declare(strict_types=1);

namespace App\Module\Github\Contributors\Endpoint\Http\v1;

use App\Application\HTTP\Response\JsonResource;
use App\Module\Github\Contributors\Dto\Contributor;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: ContributorResource::class,
    description: 'Individual GitHub contributor information',
    properties: [
        new OA\Property(
            property: 'login',
            description: 'GitHub username of the contributor',
            type: 'string',
            example: 'octocat',
        ),
        new OA\Property(
            property: 'avatar_url',
            description: 'URL to the contributor\'s GitHub avatar image',
            type: 'string',
            format: 'uri',
            example: 'https://avatars.githubusercontent.com/u/1?v=4',
        ),
        new OA\Property(
            property: 'profile_url',
            description: 'URL to the contributor\'s GitHub profile',
            type: 'string',
            format: 'uri',
            example: 'https://github.com/octocat',
        ),
        new OA\Property(
            property: 'contributions_count',
            description: 'Number of contributions made by this contributor',
            type: 'integer',
            example: 42,
            nullable: true,
        ),
    ],
    example: [
        'login' => 'octocat',
        'avatar_url' => 'https://avatars.githubusercontent.com/u/1?v=4',
        'profile_url' => 'https://github.com/octocat',
        'contributions_count' => 42,
    ],
)]
/**
 * @extends JsonResource<Contributor>
 */
final class ContributorResource extends JsonResource
{
    #[\Override]
    protected function mapData(): array|\JsonSerializable
    {
        return [
            'login' => $this->data->login,
            'avatar_url' => $this->data->avatarUrl,
            'profile_url' => "https://github.com/{$this->data->login}",
            'contributions_count' => $this->data->contributionsCount,
        ];
    }
}
