<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Endpoint\Http\v1\Plugin;

use App\Application\HTTP\Response\ResourceCollection;
use App\Module\Velox\Plugin\DTO\Plugin;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: PluginCollectionResource::class,
    description: 'Collection of RoadRunner plugins with metadata and statistics',
    properties: [
        new OA\Property(
            property: 'data',
            description: 'Array of plugin objects',
            type: 'array',
            items: new OA\Items(ref: PluginResource::class),
        ),
        new OA\Property(
            property: 'meta',
            description: 'Metadata about the plugin collection',
            properties: [
                new OA\Property(
                    property: 'total',
                    description: 'Total number of plugins in the result set',
                    type: 'integer',
                    example: 25,
                ),
                new OA\Property(
                    property: 'statistics',
                    description: 'Statistical breakdown of plugins',
                    properties: [
                        new OA\Property(
                            property: 'by_category',
                            description: 'Plugin count grouped by category',
                            type: 'object',
                            example: ['http' => 5, 'jobs' => 3, 'kv' => 4],
                            additionalProperties: new OA\AdditionalProperties(type: 'integer'),
                        ),
                        new OA\Property(
                            property: 'by_source',
                            description: 'Plugin count grouped by source type',
                            type: 'object',
                            example: ['official' => 20, 'community' => 5],
                            additionalProperties: new OA\AdditionalProperties(type: 'integer'),
                        ),
                        new OA\Property(
                            property: 'with_dependencies',
                            description: 'Number of plugins that have dependencies',
                            type: 'integer',
                            example: 12,
                        ),
                        new OA\Property(
                            property: 'total_dependencies',
                            description: 'Total count of all dependencies across all plugins',
                            type: 'integer',
                            example: 28,
                        ),
                    ],
                    type: 'object',
                ),
                new OA\Property(
                    property: 'filters',
                    description: 'Available filter options based on current dataset',
                    properties: [
                        new OA\Property(
                            property: 'available_categories',
                            description: 'List of categories present in the current result set',
                            type: 'array',
                            items: new OA\Items(type: 'string'),
                            example: ['http', 'jobs', 'kv', 'metrics'],
                        ),
                        new OA\Property(
                            property: 'available_sources',
                            description: 'List of sources present in the current result set',
                            type: 'array',
                            items: new OA\Items(type: 'string'),
                            example: ['official', 'community'],
                        ),
                    ],
                    type: 'object',
                ),
            ],
            type: 'object',
        ),
    ],
)]
final class PluginCollectionResource extends ResourceCollection
{
    /**
     * @param iterable<Plugin> $data
     */
    public function __construct(
        iterable $data,
        string|\Closure $resource = PluginResource::class,
        ...$args,
    ) {
        parent::__construct($data, $resource, $args);
    }

    #[\Override]
    protected function wrapData(array $data): array
    {
        $plugins = $this->data;
        $pluginArray = \is_array($plugins) ? $plugins : \iterator_to_array($plugins);

        // Calculate statistics
        $stats = $this->calculateStats($pluginArray);

        return [
            'data' => $data,
            'meta' => [
                'total' => \count($data),
                'statistics' => $stats,
                'filters' => [
                    'available_categories' => $this->getAvailableCategories($pluginArray),
                    'available_sources' => $this->getAvailableSources($pluginArray),
                ],
            ],
        ];
    }

    /**
     * @param array<Plugin> $plugins
     * @return array<string, mixed>
     */
    private function calculateStats(array $plugins): array
    {
        $stats = [
            'by_category' => [],
            'by_source' => [],
            'with_dependencies' => 0,
            'total_dependencies' => 0,
        ];

        foreach ($plugins as $plugin) {
            // Count by category
            $category = $plugin->category?->value ?? 'Uncategorized';
            $stats['by_category'][$category] = ($stats['by_category'][$category] ?? 0) + 1;

            // Count by source
            $source = $plugin->source->value;
            $stats['by_source'][$source] = ($stats['by_source'][$source] ?? 0) + 1;

            // Count dependencies
            if (!empty($plugin->dependencies)) {
                $stats['with_dependencies']++;
                $stats['total_dependencies'] += \count($plugin->dependencies);
            }
        }

        return $stats;
    }

    /**
     * @param array<Plugin> $plugins
     * @return array<string>
     */
    private function getAvailableCategories(array $plugins): array
    {
        $categories = [];
        foreach ($plugins as $plugin) {
            if ($plugin->category !== null) {
                $categories[] = $plugin->category->value;
            }
        }
        return \array_values(\array_unique($categories));
    }

    /**
     * @param array<Plugin> $plugins
     * @return array<string>
     */
    private function getAvailableSources(array $plugins): array
    {
        $sources = [];
        foreach ($plugins as $plugin) {
            $sources[] = $plugin->source->value;
        }
        return \array_values(\array_unique($sources));
    }
}
