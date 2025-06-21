<?php

declare(strict_types=1);

namespace App\Module\Velox;

use App\Module\Velox\Configuration\Service\ConfigurationGeneratorService;
use App\Module\Velox\Configuration\Service\ConfigurationValidatorService;
use App\Module\Velox\Dependency\Service\DependencyResolverService;
use App\Module\Velox\Environment\Service\EnvironmentFileService;
use App\Module\Velox\Plugin\Service\ConfigPluginProvider;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Files\FilesInterface;

final class VeloxBootloader extends Bootloader
{
    #[\Override]
    public function defineDependencies(): array
    {
        return [
            PluginsBootloader::class,
        ];
    }

    #[\Override]
    public function defineSingletons(): array
    {
        return [
            ConfigPluginProvider::class => ConfigPluginProvider::class,
            DependencyResolverService::class => DependencyResolverService::class,
            ConfigurationValidatorService::class => ConfigurationValidatorService::class,
            ConfigurationGeneratorService::class => ConfigurationGeneratorService::class,
            ConfigurationBuilder::class => ConfigurationBuilder::class,

            EnvironmentFileService::class => static fn(
                FilesInterface $files,
                DirectoriesInterface $dirs,
            ) => new EnvironmentFileService(
                files: $files,
                envFilePath: $dirs->get('root') . '.env',
            ),
        ];
    }
}
