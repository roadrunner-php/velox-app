<?php

declare(strict_types=1);

namespace App\Module\Velox\Preset\Endpoint\Http\v1\Preset;

use App\Module\Velox\ConfigurationBuilder;
use App\Module\Velox\Plugin\Endpoint\Http\v1\Plugin\ConfigFormat;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Spiral\Http\Exception\ClientException;
use Spiral\Http\ResponseWrapper;
use Spiral\Router\Annotation\Route;
use Spiral\Validation\Exception\ValidationException;

final readonly class GenerateConfigAction
{
    #[Route(
        route: 'v1/presets/generate-config',
        name: 'preset.generate-config',
        methods: ['POST'],
        group: 'api',
    )]
    public function __invoke(
        ConfigurationBuilder $builder,
        GenerateConfigFromPresetsFilter $filter,
        ResponseWrapper $response,
    ): ResponseInterface {
        // Validate presets first
        $validationResult = $builder->validatePresets($filter->presets);
        if (!$validationResult->isValid) {
            throw new ClientException(
                422,
                'Preset validation failed: ' . \implode(', ', $validationResult->errors),
            );
        }

        // Generate configuration
        $config = $builder->buildConfigurationFromPresets($filter->presets, '${RT_TOKEN}');

        $result = match ($filter->format) {
            ConfigFormat::TOML => $builder->generateToml($config),
            ConfigFormat::JSON => \json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            ConfigFormat::Dockerfile, ConfigFormat::Docker => $builder->generateDockerfile($config),
        };

        return $response
            ->create(200)
            ->withHeader('Content-Type', 'text/plain')
            ->withBody(Stream::create($result));
    }
}
