<?php

declare(strict_types=1);

namespace App\Application\Bootloader;

use Spiral\Bootloader\DomainBootloader;
use Spiral\Cycle\Interceptor\CycleInterceptor;
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
            CycleInterceptor::class,
        ];
    }
}
