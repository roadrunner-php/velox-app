<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryBuilder;

use App\Module\Velox\BinaryBuilder\Client\VeloxClient;
use App\Module\Velox\BinaryBuilder\Client\VeloxClientInterface;
use App\Module\Velox\BinaryBuilder\Converter\ConfigToRequestConverter;
use App\Module\Velox\BinaryBuilder\Service\BinaryBuilderService;
use App\Module\Velox\BinaryBuilder\Service\VeloxBinaryRunner;
use App\Module\Velox\Configuration\Service\ConfigurationGeneratorService;
use App\Module\Velox\Configuration\Service\ConfigurationValidatorService;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Files\FilesInterface;

final class BinaryBuilderBootloader extends Bootloader
{
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
            ): ?VeloxClient {
                return new VeloxClient(
                    serverUrl: $env->get('VELOX_SERVER_URL', 'vx-server:9000'),
                    timeoutSeconds: (int) $env->get('VELOX_SERVER_TIMEOUT', 600),
                    files: $files,
                );
            },

            // Binary builder service (supports both remote and local)
            BinaryBuilderService::class => static fn(
                VeloxBinaryRunner $binaryRunner,
                FilesInterface $files,
                DirectoriesInterface $dirs,
                ConfigurationGeneratorService $configGenerator,
                ConfigurationValidatorService $configValidator,
                VeloxClientInterface $veloxClient,
                ConfigToRequestConverter $configConverter,
            ) => new BinaryBuilderService(
                configGenerator: $configGenerator,
                configValidator: $configValidator,
                veloxClient: $veloxClient,
                configConverter: $configConverter,
                files: $files,
                tempDir: $dirs->get('runtime') . 'velox-builds',
            ),
        ];
    }
}
