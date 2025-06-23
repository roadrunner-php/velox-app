<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Endpoint\Http\v1\Plugin;

use App\Application\HTTP\Response\ErrorResource;
use App\Application\HTTP\Response\ResourceInterface;
use App\Module\Velox\ConfigurationBuilder;
use Spiral\Router\Annotation\Route;

final readonly class ShowAction
{
    #[Route(route: 'v1/plugin/<name>', name: 'plugin.show', methods: ['GET'], group: 'api')]
    public function __invoke(ConfigurationBuilder $builder, ShowPluginFilter $filter): ResourceInterface
    {
        $plugins = $builder->getAvailablePlugins();

        // Find plugin by name
        foreach ($plugins as $plugin) {
            if ($plugin->name === $filter->name) {
                return new PluginResource($plugin);
            }
        }

        return new ErrorResource(
            new \Exception("Plugin '{$filter->name}' not found", 404),
        );
    }
}
