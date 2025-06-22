<?php

declare(strict_types=1);

namespace App\Application\HTTP\Interceptor;

use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Http\Exception\ClientException\ForbiddenException;
use Spiral\Http\Exception\ClientException\UnauthorizedException;

final readonly class ExceptionHandlerInterceptor implements CoreInterceptorInterface
{
    public function process(string $controller, string $action, array $parameters, CoreInterface $core): mixed
    {
        try {
            return $core->callAction($controller, $action, $parameters);
        } catch (\Throwable $e) {
            throw match (true) {
                (int) $e->getCode() === 403 => new ForbiddenException(
                    message: $e->getMessage(),
                ),
                (int) $e->getCode() === 401 => new UnauthorizedException(
                    message: $e->getMessage(),
                ),
                default => $e,
            };
        }
    }
}
