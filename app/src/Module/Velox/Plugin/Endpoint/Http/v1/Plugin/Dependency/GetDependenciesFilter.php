<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Endpoint\Http\v1\Plugin\Dependency;

use App\Module\Velox\Plugin\DTO\Plugin;
use App\Module\Velox\Plugin\Service\PluginProviderInterface;
use Spiral\Filters\Attribute\Input\Route;
use Spiral\Filters\Model\Filter;
use Spiral\Filters\Model\FilterDefinitionInterface;
use Spiral\Filters\Model\HasFilterDefinition;
use Spiral\Validator\FilterDefinition;

final class GetDependenciesFilter extends Filter implements HasFilterDefinition
{
    #[Route(key: 'name')]
    public string $name;

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
            'name' => [
                'required',
                'string',
                'notEmpty',
                ['string::shorter', 100],
                ['in_array', $pluginNames, 'error' => 'Plugin not found'],
            ],
        ]);
    }
}
