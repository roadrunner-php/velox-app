<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Endpoint\Http\v1\Plugin;

use App\Application\HTTP\Response\JsonResource;
use App\Module\Velox\Plugin\DTO\Plugin;
use App\Module\Velox\Plugin\DTO\PluginRepository;
use App\Module\Velox\Plugin\DTO\PluginSource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: PluginResource::class,
    description: 'Individual RoadRunner plugin information',
    properties: [
        new OA\Property(
            property: 'name',
            description: 'Unique name/identifier of the plugin',
            type: 'string',
            example: 'http',
        ),
        new OA\Property(
            property: 'version',
            description: 'Git reference (tag, branch, or commit) for the plugin version',
            type: 'string',
            example: 'v5.0.2',
        ),
        new OA\Property(
            property: 'owner',
            description: 'Repository owner (user or organization)',
            type: 'string',
            example: 'roadrunner-server',
        ),
        new OA\Property(
            property: 'repository',
            description: 'Repository name',
            type: 'string',
            example: 'http',
        ),
        new OA\Property(
            property: 'repository_url',
            description: 'Full URL to the plugin repository',
            type: 'string',
            format: 'uri',
            example: 'https://github.com/roadrunner-server/http',
        ),
        new OA\Property(
            property: 'repository_type',
            description: 'Type of repository hosting platform',
            type: 'string',
            enum: ['github', 'gitlab'],
            example: 'github',
        ),
        new OA\Property(
            property: 'source',
            description: 'Source classification of the plugin',
            type: 'string',
            enum: ['official', 'community'],
            example: 'official',
        ),
        new OA\Property(
            property: 'description',
            description: 'Human-readable description of the plugin functionality',
            type: 'string',
            example: 'HTTP server plugin for handling web requests',
        ),
        new OA\Property(
            property: 'category',
            description: 'Functional category of the plugin',
            type: 'string',
            enum: [
                'core',
                'logging',
                'http',
                'jobs',
                'kv',
                'metrics',
                'grpc',
                'monitoring',
                'network',
                'broadcasting',
                'workflow',
                'observability',
            ],
            example: 'http',
            nullable: true,
        ),
        new OA\Property(
            property: 'dependencies',
            description: 'List of other plugins that this plugin depends on',
            type: 'array',
            items: new OA\Items(type: 'string'),
            example: ['server', 'logger'],
        ),
        new OA\Property(
            property: 'folder',
            description: 'Subfolder within the repository where the plugin code is located',
            type: 'string',
            example: 'plugins/roadrunner',
            nullable: true,
        ),
        new OA\Property(
            property: 'replace',
            description: 'Go module replace directive for custom module paths',
            type: 'string',
            example: 'github.com/custom/http',
            nullable: true,
        ),
        new OA\Property(
            property: 'is_official',
            description: 'Whether this is an official RoadRunner plugin',
            type: 'boolean',
            example: true,
        ),
        new OA\Property(
            property: 'full_name',
            description: 'Full repository name in owner/repository format',
            type: 'string',
            example: 'roadrunner-server/http',
        ),
    ],
)]
/**
 * @extends JsonResource<Plugin>
 */
final class PluginResource extends JsonResource
{
    #[\Override]
    protected function mapData(): array|\JsonSerializable
    {
        return [
            'name' => $this->data->name,
            'version' => $this->data->ref,
            'owner' => $this->data->owner,
            'repository' => $this->data->repository,
            'repository_url' => $this->getRepositoryUrl(),
            'repository_type' => $this->data->repositoryType->value,
            'source' => $this->data->source->value,
            'description' => $this->data->description,
            'category' => $this->data->category?->value,
            'dependencies' => $this->data->dependencies,
            'folder' => $this->data->folder,
            'replace' => $this->data->replace,
            'is_official' => $this->data->source === PluginSource::Official,
            'full_name' => "{$this->data->owner}/{$this->data->repository}",
        ];
    }

    private function getRepositoryUrl(): string
    {
        return match ($this->data->repositoryType) {
            PluginRepository::Github => "https://github.com/{$this->data->owner}/{$this->data->repository}",
            PluginRepository::Gitlab => "https://gitlab.com/{$this->data->owner}/{$this->data->repository}",
        };
    }
}
