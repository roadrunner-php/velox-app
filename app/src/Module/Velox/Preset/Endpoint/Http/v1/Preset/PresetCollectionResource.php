<?php

declare(strict_types=1);

namespace App\Module\Velox\Preset\Endpoint\Http\v1\Preset;

use App\Application\HTTP\Response\ResourceCollection;
use App\Module\Velox\Preset\DTO\PresetDefinition;

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
