<?php

declare(strict_types=1);

namespace App\Application\HTTP\Response;

use Nyholm\Psr7\Stream;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Spiral\Http\ErrorHandler\RendererInterface;
use Spiral\Http\Header\AcceptHeader;
use Spiral\Views\ViewsInterface;

final readonly class ErrorResourceRenderer implements RendererInterface
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private ContainerInterface $container,
        private array $jsonTypes = ['application/json'],
    ) {}

    public function renderException(Request $request, int $code, \Throwable $exception): Response
    {
        $response = new ErrorResource($exception);

        if ($this->isJsonExpected($request)) {
            return $response->toResponse(
                $this->responseFactory->createResponse($code),
            );
        }

        $views = $this->container->get(ViewsInterface::class);

        return $this->responseFactory
            ->createResponse($code)
            ->withBody(
                Stream::create($views->render('error', [
                    'code' => $code,
                ])),
            );
    }

    public function isJsonExpected(Request $request, bool $softMatch = false): bool
    {
        $acceptHeader = AcceptHeader::fromString($request->getHeaderLine('Accept'));
        foreach ($this->jsonTypes as $jsonType) {
            if ($acceptHeader->has($jsonType)) {
                return true;
            }
        }

        if ($softMatch) {
            foreach ($acceptHeader->getAll() as $item) {
                $itemValue = \strtolower($item->getValue());
                if (\str_ends_with($itemValue, '/json') || \str_ends_with($itemValue, '+json')) {
                    return true;
                }
            }
        }

        return false;
    }
}
