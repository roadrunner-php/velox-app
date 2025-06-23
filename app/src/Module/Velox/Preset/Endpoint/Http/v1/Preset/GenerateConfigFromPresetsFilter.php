<?php

declare(strict_types=1);

namespace App\Module\Velox\Preset\Endpoint\Http\v1\Preset;

use App\Module\Velox\Plugin\Endpoint\Http\v1\Plugin\ConfigFormat;
use App\Module\Velox\Preset\DTO\PresetDefinition;
use App\Module\Velox\Preset\Service\PresetProviderInterface;
use Spiral\Filters\Attribute\Input\Data;
use Spiral\Filters\Attribute\Input\Post;
use Spiral\Filters\Model\Filter;
use Spiral\Filters\Model\FilterDefinitionInterface;
use Spiral\Filters\Model\HasFilterDefinition;
use Spiral\Validator\FilterDefinition;

final class GenerateConfigFromPresetsFilter extends Filter implements HasFilterDefinition
{
    #[Data]
    public array $presets = [];

    #[Post]
    public ConfigFormat $format = ConfigFormat::TOML;

    public function __construct(
        private readonly PresetProviderInterface $provider,
    ) {}

    public function filterDefinition(): FilterDefinitionInterface
    {
        $presetNames = \array_map(
            static fn(PresetDefinition $preset): string => $preset->name,
            $this->provider->getAllPresets(),
        );

        return new FilterDefinition([
            'presets' => [
                'required',
                'array',
                'notEmpty',
                ['array::expectedValues', $presetNames],
            ],
            'format' => ['string'],
        ]);
    }
}
