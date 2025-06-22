<?php

declare(strict_types=1);

namespace App\Module\Velox\Preset\Endpoint\Http\v1\Preset;

use App\Module\Velox\ConfigurationBuilder;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Spiral\Http\Exception\ClientException;
use Spiral\Http\Request\InputManager;
use Spiral\Http\ResponseWrapper;
use Spiral\Router\Annotation\Route;

final readonly class GenerateConfigAction
{
    #[Route(
        route: 'v1/presets/generate-config',
        name: 'preset.generate-config',
        methods: ['POST', 'GET'],
        group: 'api'
    )]
    public function __invoke(
        ConfigurationBuilder $builder,
        InputManager $request,
        ResponseWrapper $response,
    ): ResponseInterface {
        $presetNames = $request->post('presets', $request->query('presets', []));
        $format = $request->post('format', $request->query('format', 'toml'));

        if (empty($presetNames) || !\is_array($presetNames)) {
            throw new ClientException(
                400,
                'Presets array is required',
            );
        }

        // Validate presets first
        $validationResult = $builder->validatePresets($presetNames);
        if (!$validationResult->isValid) {
            throw new ClientException(
                422,
                'Preset validation failed: ' . \implode(', ', $validationResult->errors),
            );
        }

        // Generate configuration
        $config = $builder->buildConfigurationFromPresets($presetNames);

        $result = match ($format) {
            'toml' => $builder->generateToml($config),
            'json' => \json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            'dockerfile', 'docker' => $builder->generateDockerfile($config),
            default => throw new ClientException(
                400,
                'Unsupported format: ' . $format,
            ),
        };

        return $response
            ->create(200)
            ->withHeader('Content-Type', 'text/plain')
            ->withBody(Stream::create($result));
    }
}
