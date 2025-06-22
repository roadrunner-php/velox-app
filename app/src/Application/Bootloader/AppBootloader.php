<?php

declare(strict_types=1);

namespace App\Application\Bootloader;

use App\Application\HTTP\Interceptor\ExceptionHandlerInterceptor;
use App\Application\HTTP\Interceptor\JsonResourceInterceptor;
use App\Application\HTTP\Interceptor\StringToIntParametersInterceptor;
use App\Application\HTTP\Interceptor\UuidParametersConverterInterceptor;
use Spiral\Bootloader\DomainBootloader;
use Spiral\Interceptors\HandlerInterface;

final class AppBootloader extends DomainBootloader
{
    #[\Override]
    public function defineSingletons(): array
    {
        return [
            HandlerInterface::class => $this->domainCore(...),
        ];
    }

    #[\Override]
    protected static function defineInterceptors(): array
    {
        return [
            ExceptionHandlerInterceptor::class,
            JsonResourceInterceptor::class,
            StringToIntParametersInterceptor::class,
            UuidParametersConverterInterceptor::class,
        ];
    }
}
