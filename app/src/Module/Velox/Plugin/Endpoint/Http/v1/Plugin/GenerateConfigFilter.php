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
                'notEmpty',
                ['array::expectedValues', $pluginNames],
            ],
            'format' => ['required', 'string', 'notEmpty'],
        ]);
    }
}
