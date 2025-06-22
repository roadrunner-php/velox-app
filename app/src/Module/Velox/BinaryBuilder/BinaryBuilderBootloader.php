<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryBuilder;

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
            VeloxBinaryRunner::class => static fn(
                EnvironmentInterface $env,
                DirectoriesInterface $dirs,
            ) => new VeloxBinaryRunner(
                veloxBinaryPath: $dirs->get('root') . 'vx',
                timeoutSeconds: (int) $env->get('VELOX_BUILD_TIMEOUT', 300),
            ),
            BinaryBuilderService::class => static fn(
                VeloxBinaryRunner $binaryRunner,
                FilesInterface $files,
                DirectoriesInterface $dirs,
                ConfigurationGeneratorService $configGenerator,
                ConfigurationValidatorService $configValidator,
            ) => new BinaryBuilderService(
                configGenerator: $configGenerator,
                configValidator: $configValidator,
                binaryRunner: $binaryRunner,
                files: $files,
                tempDir: $dirs->get('runtime') . 'velox-builds',
            ),
        ];
    }
}
