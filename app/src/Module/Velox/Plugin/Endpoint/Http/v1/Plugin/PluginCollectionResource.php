<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Endpoint\Http\v1\Plugin;

use App\Application\HTTP\Response\ResourceCollection;
use App\Module\Velox\Plugin\DTO\Plugin;

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
