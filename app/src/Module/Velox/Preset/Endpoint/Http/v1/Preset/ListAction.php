<?php

declare(strict_types=1);

namespace App\Module\Velox\Preset\Endpoint\Http\v1\Preset;

use App\Application\HTTP\Response\ResourceInterface;
use App\Module\Velox\ConfigurationBuilder;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Router\Annotation\Route;

final readonly class ListAction
{
    #[Route(route: 'v1/presets', name: 'preset.list', methods: ['GET'], group: 'api')]
    public function __invoke(ConfigurationBuilder $builder, ServerRequestInterface $request): ResourceInterface
    {
        $params = $request->getQueryParams();

        // Get filter parameters
        $tags = $params['tags'] ?? null;
        $search = $params['search'] ?? null;
        $official = $params['official'] ?? null;

        // Start with all presets
        $presets = $builder->getAvailablePresets();

        // Apply tags filter
        if ($tags !== null && $tags !== '') {
            $tagArray = \explode(',', $tags);
            $presets = $builder->getPresetsByTags($tagArray);
        }

        // Apply search filter
        if ($search !== null && $search !== '') {
            $presets = $builder->searchPresets($search);
        }

        // Apply official filter
        if ($official !== null && $official !== '') {
            $isOfficial = \filter_var($official, FILTER_VALIDATE_BOOLEAN);
            $presets = \array_filter(
                $presets,
                static fn($preset) => $preset->isOfficial === $isOfficial,
            );
        }

        return new PresetCollectionResource($presets);
    }
}
