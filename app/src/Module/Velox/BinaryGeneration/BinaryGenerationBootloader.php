<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryGeneration;

use App\Module\Velox\BinaryGeneration\Service\PlatformDetectionService;
use App\Module\Velox\BinaryGeneration\Service\ScriptGeneratorService;
use App\Module\Velox\ConfigurationBuilder;
use Spiral\Boot\Bootloader\Bootloader;

final class BinaryGenerationBootloader extends Bootloader
{
    public function defineSingletons(): array
    {
        return [
            PlatformDetectionService::class => PlatformDetectionService::class,
            ScriptGeneratorService::class => static fn(
                ConfigurationBuilder $configBuilder,
            ) => new ScriptGeneratorService(
                configBuilder: $configBuilder,
            ),
        ];
    }
}
