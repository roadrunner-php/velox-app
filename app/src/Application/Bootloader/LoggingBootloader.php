<?php

declare(strict_types=1);

namespace App\Application\Bootloader;

use Monolog\Level;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Http\Middleware\ErrorHandlerMiddleware;
use Spiral\Monolog\Bootloader\MonologBootloader;
use Spiral\Monolog\Config\MonologConfig;
use Spiral\RoadRunnerBridge\Bootloader as RoadRunnerBridge;

final class LoggingBootloader extends Bootloader
{
    #[\Override]
    public function defineDependencies(): array
    {
        return [
            // Logging and exceptions handling
            MonologBootloader::class,
            RoadRunnerBridge\LoggerBootloader::class,
        ];
    }

    public function init(MonologBootloader $monolog): void
    {
        // HTTP level errors
        $monolog->addHandler(
            channel: ErrorHandlerMiddleware::class,
            handler: $monolog->logRotate(
                directory('runtime') . 'logs/http.log',
            ),
        );

        // app level errors
        $monolog->addHandler(
            channel: MonologConfig::DEFAULT_CHANNEL,
            handler: $monolog->logRotate(
                filename: directory('runtime') . 'logs/error.log',
                level: Level::Error,
                maxFiles: 25,
                bubble: false,
            ),
        );

        // debug and info messages via global LoggerInterface
        $monolog->addHandler(
            channel: MonologConfig::DEFAULT_CHANNEL,
            handler: $monolog->logRotate(
                filename: directory('runtime') . 'logs/debug.log',
            ),
        );
    }
}
