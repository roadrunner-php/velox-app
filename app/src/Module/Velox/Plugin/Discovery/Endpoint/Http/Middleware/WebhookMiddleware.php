<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Discovery\Endpoint\Http\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Validates GitHub webhook secret
 */
final readonly class WebhookMiddleware implements MiddlewareInterface
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private ?string $webhookSecret = null,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // If no secret configured, skip validation
        if ($this->webhookSecret === null || $this->webhookSecret === '') {
            return $handler->handle($request);
        }

        $providedSecret = $request->getHeaderLine('X-Velox-Secret');

        if ($providedSecret !== $this->webhookSecret) {
            $response = $this->responseFactory->createResponse(401);
            $response->getBody()->write(\json_encode([
                'error' => 'Unauthorized',
                'message' => 'Invalid webhook secret',
            ]));

            return $response->withHeader('Content-Type', 'application/json');
        }

        return $handler->handle($request);
    }
}
