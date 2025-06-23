<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Endpoint\Http\v1\Plugin;

use App\Module\Velox\Plugin\DTO\Plugin;
use App\Module\Velox\Plugin\Service\PluginProviderInterface;
use Spiral\Filters\Attribute\Input\Post;
use Spiral\Filters\Model\Filter;
use Spiral\Filters\Model\FilterDefinitionInterface;
use Spiral\Filters\Model\HasFilterDefinition;
use Spiral\Validator\FilterDefinition;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: GenerateConfigFilter::class,
    description: 'Request body for generating RoadRunner configuration from plugins',
    required: ['plugins'],
    properties: [
        new OA\Property(
            property: 'plugins',
            description: 'Array of plugin names to include in the configuration. Dependencies will be automatically resolved and included.',
            type: 'array',
            items: new OA\Items(
                type: 'string',
                pattern: '^[a-zA-Z0-9_-]+$',
            ),
            minItems: 1,
            example: ['server', 'logger', 'http', 'gzip', 'static'],
        ),
        new OA\Property(
            property: 'format',
            description: 'Output format for the generated configuration file',
            type: 'string',
            default: 'toml',
            enum: ['toml', 'json', 'docker', 'dockerfile'],
            example: 'toml',
        ),
    ],
    example: [
        'plugins' => ['server', 'logger', 'http', 'gzip', 'static'],
        'format' => 'toml',
    ],
)]
final class GenerateConfigFilter extends Filter implements HasFilterDefinition
{
    #[Post]
    public array $plugins = [];

    #[Post]
    public ConfigFormat $format = ConfigFormat::TOML;

    public function __construct(
        private readonly PluginProviderInterface $provider,
    ) {}

    public function filterDefinition(): FilterDefinitionInterface
    {
        $pluginNames = \array_map(
            static fn(Plugin $plugin): string => $plugin->name,
            $this->provider->getAllPlugins(),
        );

        return new FilterDefinition([
            'plugins' => [
                'required',
                'array',
                'notEmpty',
                ['array::expectedValues', $pluginNames],
            ],
            'format' => ['string'],
        ]);
    }
}
