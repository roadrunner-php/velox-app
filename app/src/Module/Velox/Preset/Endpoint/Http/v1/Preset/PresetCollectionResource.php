<?php

declare(strict_types=1);

namespace App\Module\Velox\Preset\Endpoint\Http\v1\Preset;

use App\Application\HTTP\Response\ResourceCollection;
use App\Module\Velox\Preset\DTO\PresetDefinition;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: PresetCollectionResource::class,
    properties: [
        new OA\Property(
            property: 'data',
            description: 'List of presets',
            type: 'array',
            items: new OA\Items(ref: PresetResource::class),
        ),
        new OA\Property(
            property: 'meta',
            properties: [
                new OA\Property(property: 'total', description: 'Total number of presets', type: 'integer'),
                new OA\Property(
                    property: 'filters',
                    properties: [
                        new OA\Property(property: 'available_tags', type: 'array', items: new OA\Items(type: 'string')),
                    ],
                    type: 'object',
                ),
            ],
            type: 'object',
        ),
    ],
    type: 'object',
)]
final class PresetCollectionResource extends ResourceCollection
{
    /**
     * @param iterable<PresetDefinition> $data
     */
    public function __construct(
        iterable $data,
        string|\Closure $resource = PresetResource::class,
        ...$args,
    ) {
        parent::__construct($data, $resource, $args);
    }

    #[\Override]
    protected function wrapData(array $data): array
    {
        $presets = $this->data;
        $presetArray = \is_array($presets) ? $presets : \iterator_to_array($presets);

        return [
            'data' => $data,
            'meta' => [
                'total' => \count($data),
                'filters' => [
                    'available_tags' => $this->getAvailableTags($presetArray),
                ],
            ],
        ];
    }

    /**
     * @param array<PresetDefinition> $presets
     * @return array<string>
     */
    private function getAvailableTags(array $presets): array
    {
        $tags = [];
        foreach ($presets as $preset) {
            $tags = [...$tags, ...$preset->tags];
        }
        return \array_values(\array_unique($tags));
    }
}
