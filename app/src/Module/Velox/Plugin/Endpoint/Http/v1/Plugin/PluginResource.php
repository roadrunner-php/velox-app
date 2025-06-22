<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Endpoint\Http\v1\Plugin;

use App\Application\HTTP\Response\JsonResource;
use App\Module\Velox\Plugin\DTO\Plugin;
use App\Module\Velox\Plugin\DTO\PluginRepository;
use App\Module\Velox\Plugin\DTO\PluginSource;

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
