<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Endpoint\Http\v1\Plugin;

use App\Module\Velox\Plugin\DTO\PluginCategory;
use App\Module\Velox\Plugin\DTO\PluginSource;
use Spiral\Filters\Attribute\Input\Query;
use Spiral\Filters\Model\Filter;
use Spiral\Filters\Model\FilterDefinitionInterface;
use Spiral\Filters\Model\HasFilterDefinition;
use Spiral\Validator\FilterDefinition;

final class ListPluginsFilter extends Filter implements HasFilterDefinition
{
    #[Query]
    public ?PluginCategory $category = null;

    #[Query]
    public ?PluginSource $source = null;

    #[Query]
    public ?string $search = null;

    public function filterDefinition(): FilterDefinitionInterface
    {
        $validCategories = \array_map(
            static fn(PluginCategory $category): string => $category->value,
            PluginCategory::cases(),
        );

        $validSources = \array_map(
            static fn(PluginSource $source): string => $source->value,
            PluginSource::cases(),
        );

        return new FilterDefinition([
            'category' => [
                [
                    'in_array',
                    $validCategories,
                    'error' => 'Invalid category. Valid categories: ' . \implode(', ', $validCategories),
                ],
            ],
            'source' => [
                [
                    'in_array',
                    $validSources,
                    'error' => 'Invalid source. Valid sources: ' . \implode(', ', $validSources),
                ],
            ],
            'search' => [
                'string',
                ['string::shorter', 100],
            ],
        ]);
    }
}
