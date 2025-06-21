<?php

declare(strict_types=1);

namespace App\Module\Velox;

use App\Module\Velox\Service\ConfigPluginProvider;
use App\Module\Velox\Service\ConfigurationGeneratorService;
use App\Module\Velox\Service\ConfigurationValidatorService;
use App\Module\Velox\Service\DependencyResolverService;
use App\Module\Velox\Service\JsonToTomlConverter;
use Spiral\Boot\Bootloader\Bootloader;

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
            JsonToTomlConverter::class => JsonToTomlConverter::class,
            ConfigPluginProvider::class => ConfigPluginProvider::class,
            DependencyResolverService::class => DependencyResolverService::class,
            ConfigurationValidatorService::class => ConfigurationValidatorService::class,
            ConfigurationGeneratorService::class => ConfigurationGeneratorService::class,
            ConfigurationBuilder::class => ConfigurationBuilder::class,
        ];
    }
}
