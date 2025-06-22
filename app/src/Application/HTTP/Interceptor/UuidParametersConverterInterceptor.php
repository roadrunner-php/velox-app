<?php

declare(strict_types=1);

namespace App\Application\HTTP\Interceptor;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;

final readonly class UuidParametersConverterInterceptor implements CoreInterceptorInterface
{
    public function process(string $controller, string $action, array $parameters, CoreInterface $core): mixed
    {
        $refMethod = new \ReflectionMethod($controller, $action);

        foreach ($refMethod->getParameters() as $parameter) {
            if ($parameter->getType() === null) {
                continue;
            }

            /** @var \ReflectionNamedType|\ReflectionUnionType|\ReflectionIntersectionType $type */
            $type = $parameter->getType();

            if ($type->getName() === UuidInterface::class) {
                if ($parameters[$parameter->getName()] === null) {
                    continue;
                }

                $parameters[$parameter->getName()] = Uuid::fromString($parameters[$parameter->getName()]);
            }
        }

        return $core->callAction($controller, $action, $parameters);
    }
}
