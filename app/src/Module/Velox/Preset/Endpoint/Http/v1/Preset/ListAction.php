<?php

declare(strict_types=1);

namespace App\Module\Velox\Preset\Endpoint\Http\v1\Preset;

use App\Application\HTTP\Response\ResourceInterface;
use App\Module\Velox\ConfigurationBuilder;
use Spiral\Router\Annotation\Route;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/presets',
    tags: ['presets'],
    parameters: [
        new OA\Parameter(
            name: 'tags',
            description: 'Filter presets by tags, comma-separated',
            in: 'query',
            required: false,
            schema: new OA\Schema(
                type: 'string',
                maxLength: 200,
                example: 'web,api',
            ),
        ),
        new OA\Parameter(
            name: 'search',
            description: 'Search presets by name',
            in: 'query',
            required: false,
            schema: new OA\Schema(
                type: 'string',
                maxLength: 100,
                example: 'search term',
            ),
        ),
        new OA\Parameter(
            name: 'official',
            description: 'Filter by official presets',
            in: 'query',
            required: false,
            schema: new OA\Schema(
                type: 'string',
                enum: ['yes', '1', 'on'],
                example: 'true',
            ),
        ),
    ],
    responses: [
        new OA\Response(
            ref: PresetCollectionResource::class,
            response: 200,
        ),
    ],
)]
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
