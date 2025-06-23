<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Endpoint\Http\v1\Plugin;

use App\Module\Velox\ConfigurationBuilder;
use App\Module\Velox\Plugin\DTO\Plugin;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Spiral\Http\ResponseWrapper;
use Spiral\Router\Annotation\Route;

final readonly class GenerateConfigAction
{
    #[Route(
        route: 'v1/plugins/generate-config',
        name: 'plugin.generate-config',
        methods: ['POST', 'GET'],
        group: 'api',
    )]
    public function __invoke(
        ConfigurationBuilder $builder,
        GenerateConfigFilter $filter,
        ResponseWrapper $response,
    ): ResponseInterface {
        $pluginNames = $filter->plugins;
        $format = $filter->format;

        $deps = $builder->resolveDependencies($pluginNames);

        $pluginNames = \array_unique([
            ...$pluginNames,
            ...\array_values(
                \array_map(
                    static fn(Plugin $plugin): string => $plugin->name,
                    $deps->requiredPlugins,
                ),
            ),
        ]);

        // Generate configuration
        $config = $builder->buildConfiguration($pluginNames, '${RT_TOKEN}');

        $result = match ($format) {
            ConfigFormat::TOML => $builder->generateToml($config),
            ConfigFormat::JSON => \json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            ConfigFormat::Docker, ConfigFormat::Dockerfile => $builder->generateDockerfile($config),
        };

        return $response
            ->create(200)
            ->withHeader('Content-Type', 'text/plain')
            ->withBody(Stream::create($result));
    }
}
