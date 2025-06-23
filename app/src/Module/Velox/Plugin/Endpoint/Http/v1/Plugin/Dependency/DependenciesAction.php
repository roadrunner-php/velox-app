<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Endpoint\Http\v1\Plugin\Dependency;

use App\Application\HTTP\Response\ResourceInterface;
use App\Module\Velox\ConfigurationBuilder;
use Spiral\Http\Exception\ClientException\NotFoundException;
use Spiral\Router\Annotation\Route;

final readonly class DependenciesAction
{
    #[Route(route: 'v1/plugin/<name>/dependencies', name: 'plugin.dependencies', methods: ['GET'], group: 'api')]
    public function __invoke(ConfigurationBuilder $builder, GetDependenciesFilter $filter): ResourceInterface
    {
        // Find the plugin
        $plugin = null;
        foreach ($builder->getAvailablePlugins() as $p) {
            if ($p->name === $filter->name) {
                $plugin = $p;
                break;
            }
        }

        if ($plugin === null) {
            throw new NotFoundException(
                message: "Plugin with name '{$filter->name}' not found.",
            );
        }

        return new PluginDependenciesResource(
            $filter->name,
            $builder->resolveDependencies([$filter->name]),
        );
    }
}
