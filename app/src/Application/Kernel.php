<?php

declare(strict_types=1);

namespace App\Application;

use App\Module\Github\GithubBootloader;
use App\Module\Velox\VeloxBootloader;
use Spiral\Boot\Bootloader\CoreBootloader;
use Spiral\Bootloader as Framework;
use Spiral\Cycle\Bootloader as CycleBridge;
use Spiral\Distribution\Bootloader\DistributionBootloader;
use Spiral\DotEnv\Bootloader\DotenvBootloader;
use Spiral\RoadRunnerBridge\Bootloader as RoadRunnerBridge;
use Spiral\Sentry\Bootloader\SentryReporterBootloader;
use Spiral\Stempler\Bootloader\StemplerBootloader;
use Spiral\Storage\Bootloader\StorageBootloader;
use Spiral\TemporalBridge\Bootloader as TemporalBridge;
use Spiral\Tokenizer\Bootloader\TokenizerListenerBootloader;
use Spiral\Views\Bootloader\ViewsBootloader;

/**
 * @psalm-suppress ClassMustBeFinal
 */
class Kernel extends \Spiral\Framework\Kernel
{
    #[\Override]
    public function defineSystemBootloaders(): array
    {
        return [
            CoreBootloader::class,
            DotenvBootloader::class,
            TokenizerListenerBootloader::class,
        ];
    }

    #[\Override]
    public function defineBootloaders(): array
    {
        return [
            Bootloader\ExceptionHandlerBootloader::class,

            // Application specific logs
            Bootloader\LoggingBootloader::class,

            // Core Services
            Framework\SnapshotsBootloader::class,

            // Security and validation
            Framework\Security\EncrypterBootloader::class,

            // HTTP extensions

            // Databases
            CycleBridge\DatabaseBootloader::class,
            CycleBridge\MigrationsBootloader::class,

            // ORM
            CycleBridge\SchemaBootloader::class,
            CycleBridge\CycleOrmBootloader::class,
            CycleBridge\AnnotatedBootloader::class,
            CycleBridge\DisconnectsBootloader::class,

            // Sentry and Data collectors
            SentryReporterBootloader::class,
            Framework\DebugBootloader::class,
            Framework\Debug\LogCollectorBootloader::class,
            Framework\Debug\HttpCollectorBootloader::class,

            // Views
            ViewsBootloader::class,
            StemplerBootloader::class,

            // Storage
            StorageBootloader::class,
            DistributionBootloader::class,

            // Temporal
            TemporalBridge\TemporalBridgeBootloader::class,

            // Console commands
            Framework\CommandBootloader::class,
            RoadRunnerBridge\CommandBootloader::class,
            CycleBridge\CommandBootloader::class,

            \Spiral\Cache\Bootloader\CacheBootloader::class,
            RoadRunnerBridge\CacheBootloader::class,

            // Configure route groups, middleware for route groups
            Bootloader\RoutesBootloader::class,
            // Application domain
            Bootloader\AppBootloader::class,

            GithubBootloader::class,
            VeloxBootloader::class,
        ];
    }
}
