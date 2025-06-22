<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Endpoint\Http\v1\Plugin;

use App\Application\HTTP\Response\ErrorResource;
use App\Application\HTTP\Response\ResourceInterface;
use App\Module\Velox\ConfigurationBuilder;
use Spiral\Router\Annotation\Route;

final readonly class ShowAction
{
    #[Route(route: 'v1/plugins/<name>', name: 'plugin.show', methods: ['GET'], group: 'api')]
    public function __invoke(ConfigurationBuilder $builder, string $name): ResourceInterface
    {
        $plugin = $builder->getAvailablePlugins();

        // Find plugin by name
        foreach ($plugin as $p) {
            if ($p->name === $name) {
                return new PluginResource($p);
            }
        }

        return new ErrorResource(
            new \Exception("Plugin '{$name}' not found", 404),
        );
    }
}
