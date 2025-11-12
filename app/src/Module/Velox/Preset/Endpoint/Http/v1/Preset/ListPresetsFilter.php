<?php

declare(strict_types=1);

namespace App\Module\Velox\Preset\Endpoint\Http\v1\Preset;

use Spiral\Filters\Attribute\Input\Query;
use Spiral\Filters\Model\Filter;
use Spiral\Filters\Model\FilterDefinitionInterface;
use Spiral\Filters\Model\HasFilterDefinition;
use Spiral\Validator\FilterDefinition;

final class ListPresetsFilter extends Filter implements HasFilterDefinition
{
    #[Query]
    public ?string $tags = null;

    #[Query]
    public ?string $search = null;

    #[Query]
    public ?string $official = null;

    public function filterDefinition(): FilterDefinitionInterface
    {
        return new FilterDefinition([
            'tags' => [
                'string',
                ['string::shorter', 200],
            ],
            'search' => [
                'string',
                ['string::shorter', 100],
            ],
            'official' => [
                'mixed::accepted',
            ],
        ]);
    }

    /**
     * @return array<string>|null
     */
    public function getTagsArray(): ?array
    {
        if ($this->tags === null || $this->tags === '') {
            return null;
        }

        return \array_filter(
            \array_map(trim(...), \explode(',', $this->tags)),
            static fn(string $tag): bool => $tag !== '',
        );
    }

    public function getOfficialBoolean(): ?bool
    {
        if ($this->official === null || $this->official === '') {
            return null;
        }

        return \filter_var($this->official, FILTER_VALIDATE_BOOLEAN);
    }
}
