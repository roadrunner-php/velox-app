<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Discovery\Endpoint\Http\v1\Discovery;

use Spiral\Filters\Attribute\Input\Post;
use Spiral\Filters\Model\Filter;
use Spiral\Filters\Model\FilterDefinitionInterface;
use Spiral\Filters\Model\HasFilterDefinition;
use Spiral\Validator\FilterDefinition;

/**
 * Validates GitHub webhook payload
 */
final class WebhookFilter extends Filter implements HasFilterDefinition
{
    #[Post]
    public string $action = '';

    #[Post]
    public array $release = [];

    #[Post]
    public array $repository = [];

    public function filterDefinition(): FilterDefinitionInterface
    {
        return new FilterDefinition([
            'action' => ['required', 'string', 'in:published'],
            'release' => ['required', 'array'],
            'release.tag_name' => ['required', 'string'],
            'release.draft' => ['boolean'],
            'release.prerelease' => ['boolean'],
            'repository' => ['required', 'array'],
            'repository.name' => ['required', 'string'],
            'repository.owner' => ['required', 'array'],
            'repository.owner.login' => ['required', 'string'],
        ]);
    }

    public function getRepositoryName(): string
    {
        return $this->repository['name'] ?? '';
    }

    public function getTagName(): string
    {
        return $this->release['tag_name'] ?? '';
    }

    public function isDraft(): bool
    {
        return $this->release['draft'] ?? false;
    }

    public function isPrerelease(): bool
    {
        return $this->release['prerelease'] ?? false;
    }
}
