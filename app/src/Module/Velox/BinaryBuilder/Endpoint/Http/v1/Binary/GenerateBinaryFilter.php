<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryBuilder\Endpoint\Http\v1\Binary;

use App\Module\Velox\BinaryBuilder\DTO\Architecture;
use App\Module\Velox\BinaryBuilder\DTO\OS;
use App\Module\Velox\Plugin\DTO\Plugin;
use App\Module\Velox\Plugin\Service\PluginProviderInterface;
use Spiral\Filters\Attribute\Input\Post;
use Spiral\Filters\Model\Filter;
use Spiral\Filters\Model\FilterDefinitionInterface;
use Spiral\Filters\Model\HasFilterDefinition;
use Spiral\Validator\FilterDefinition;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: GenerateBinaryFilter::class,
    description: 'Request body for generating RoadRunner binary from plugins',
    required: ['plugins'],
    properties: [
        new OA\Property(
            property: 'plugins',
            description: 'Array of plugin names to include in the binary. Dependencies will be automatically resolved and included.',
            type: 'array',
            items: new OA\Items(
                type: 'string',
                pattern: '^[a-zA-Z0-9_-]+$',
            ),
            minItems: 1,
            example: ['server', 'logger', 'http', 'gzip', 'static'],
        ),
        new OA\Property(
            property: 'target_os',
            description: 'Target operating system for the binary. If not specified, uses current platform.',
            type: 'string',
            enum: ['linux', 'darwin', 'windows', 'freebsd'],
            example: 'linux',
        ),
        new OA\Property(
            property: 'target_arch',
            description: 'Target architecture for the binary. If not specified, uses current platform.',
            type: 'string',
            enum: ['amd64', 'arm64', '386', 'arm'],
            example: 'amd64',
        ),
    ],
    example: [
        'plugins' => ['server', 'logger', 'http', 'gzip', 'static'],
        'target_os' => 'linux',
        'target_arch' => 'amd64',
    ],
)]
final class GenerateBinaryFilter extends Filter implements HasFilterDefinition
{
    #[Post]
    public array $plugins = [];

    #[Post(key: 'target_os')]
    public OS $targetOs = OS::Linux;

    #[Post(key: 'target_arch')]
    public Architecture $targetArch = Architecture::AMD64;

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
        ]);
    }
}
