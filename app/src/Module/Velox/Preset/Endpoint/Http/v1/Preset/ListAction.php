<?php

declare(strict_types=1);

namespace App\Module\Velox\Preset\Endpoint\Http\v1\Preset;

use App\Application\HTTP\Response\ResourceInterface;
use App\Module\Velox\ConfigurationBuilder;
use Spiral\Router\Annotation\Route;

final readonly class ListAction
{
    #[Route(route: 'v1/presets', name: 'preset.list', methods: ['GET'], group: 'api')]
    public function __invoke(ConfigurationBuilder $builder, ListPresetsFilter $filter): ResourceInterface
    {
        // Start with all presets
        $presets = $builder->getAvailablePresets();

        // Apply tags filter
        $tagsArray = $filter->getTagsArray();
        if ($tagsArray !== null) {
            $presets = $builder->getPresetsByTags($tagsArray);
        }

        // Apply search filter
        if ($filter->search !== null && $filter->search !== '') {
            $presets = $builder->searchPresets($filter->search);
        }

        // Apply official filter
        $officialBoolean = $filter->getOfficialBoolean();
        if ($officialBoolean !== null) {
            $presets = \array_filter(
                $presets,
                static fn($preset) => $preset->isOfficial === $officialBoolean,
            );
        }

        return new PresetCollectionResource($presets);
    }
}
