<?php

declare(strict_types=1);

namespace App\Application\HTTP\Response;

use Psr\Http\Message\ResponseInterface;

trait JsonTrait
{
    protected function writeJson(ResponseInterface $response, mixed $payload, int $code = 200): ResponseInterface
    {
        if ($payload instanceof \JsonSerializable) {
            $payload = $payload->jsonSerialize();
        }

        $response->getBody()->write(\json_encode($payload));

        return $response->withStatus($code)->withHeader('Content-Type', 'application/json');
    }
}
