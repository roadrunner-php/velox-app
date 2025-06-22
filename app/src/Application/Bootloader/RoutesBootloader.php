<?php

declare(strict_types=1);

namespace App\Application\Bootloader;

use Spiral\Bootloader\Http\HttpBootloader;
use Spiral\Bootloader\Http\RoutesBootloader as BaseRoutesBootloader;
use Spiral\Debug\StateCollector\HttpCollector;
use Spiral\Filter\ValidationHandlerMiddleware;
use Spiral\Http\Middleware\ErrorHandlerMiddleware;
use Spiral\Http\Middleware\JsonPayloadMiddleware;
use Spiral\Router\Bootloader\AnnotatedRoutesBootloader;
use Spiral\Router\GroupRegistry;
use Spiral\Bootloader as Framework;
use Spiral\RoadRunnerBridge\Bootloader as RoadRunnerBridge;
use Spiral\Nyholm\Bootloader\NyholmBootloader;

final class RoutesBootloader extends BaseRoutesBootloader
{
    #[\Override]
    public function defineDependencies(): array
    {
        return [
            HttpBootloader::class,
            RoadRunnerBridge\HttpBootloader::class,
            NyholmBootloader::class,
            Framework\Http\RouterBootloader::class,
            Framework\Http\JsonPayloadsBootloader::class,
            Framework\Http\CookiesBootloader::class,
            Framework\Http\SessionBootloader::class,
            Framework\Http\CsrfBootloader::class,
            Framework\Http\PaginationBootloader::class,
            AnnotatedRoutesBootloader::class,
        ];
    }

    #[\Override]
    protected function globalMiddleware(): array
    {
        return [
            ErrorHandlerMiddleware::class,
            JsonPayloadMiddleware::class,
            HttpCollector::class,
        ];
    }

    #[\Override]
    protected function middlewareGroups(): array
    {
        return [
            'api' => [
                ValidationHandlerMiddleware::class,
            ],
        ];
    }

    #[\Override]
    protected function configureRouteGroups(GroupRegistry $groups): void
    {
        $groups
            ->getGroup('api')
            ->setPrefix('api')
            ->setNamePrefix('api.');
    }
}
