<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryBuilder;

use App\Module\Velox\BinaryBuilder\Client\VeloxClient;
use App\Module\Velox\BinaryBuilder\Client\VeloxClientInterface;
use App\Module\Velox\BinaryBuilder\Converter\ConfigToRequestConverter;
use App\Module\Velox\BinaryBuilder\Service\BinaryBuilderService;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Files\FilesInterface;
use Symfony\Component\HttpClient\Psr18Client;

final class BinaryBuilderBootloader extends Bootloader
{
    #[\Override]
    public function defineDependencies(): array
    {
        return [
            BinaryCacheBootloader::class,
        ];
    }

    #[\Override]
    public function defineSingletons(): array
    {
        return [
            // Config converter for remote builds
            ConfigToRequestConverter::class => ConfigToRequestConverter::class,

            // Velox client for remote builds (only if server URL is configured)
            VeloxClientInterface::class => static function (
                EnvironmentInterface $env,
                FilesInterface $files,
            ): VeloxClient {
                $httpClient = new Psr18Client();

                return new VeloxClient(
                    httpClient: $httpClient->withOptions([
                        'base_uri' => $env->get('VELOX_SERVER_URL', 'http:/vx-server:9000'),
                        'headers' => [
                            'Content-Type' => 'application/json',
                            'Accept' => 'application/json',
                        ],
                        'timeout' => (int) $env->get('VELOX_SERVER_TIMEOUT', 600),
                    ]),
                    requestFactory: $httpClient,
                    streamFactory: $httpClient,
                    files: $files,
                    serverUrl: $env->get('VELOX_SERVER_URL', 'http://vx-server:9000'),
                );
            },

            // Binary builder service (supports both remote and local)
            BinaryBuilderService::class => BinaryBuilderService::class,
        ];
    }
}
