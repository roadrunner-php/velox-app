<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryBuilder\Client;

use App\Module\Velox\BinaryBuilder\DTO\BuildRequest;
use App\Module\Velox\BinaryBuilder\DTO\BuildResult;
use App\Module\Velox\BinaryBuilder\Exception\VeloxBuildFailedException;
use App\Module\Velox\BinaryBuilder\Exception\VeloxServerConnectionException;
use App\Module\Velox\BinaryBuilder\Exception\VeloxTimeoutException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Spiral\Files\FilesInterface;

final readonly class VeloxClient implements VeloxClientInterface
{
    private const string BUILD_ENDPOINT = '/api.service.v1.BuildService/Build';

    public function __construct(
        private ClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
        private FilesInterface $files,
        private string $serverUrl,
    ) {}

    public function build(BuildRequest $request, string $outputPath): BuildResult
    {
        $startTime = \microtime(true);

        try {
            $httpRequest = $this->createJsonRequest('POST', self::BUILD_ENDPOINT, $request->toArray());
            $response = $this->httpClient->sendRequest($httpRequest);

            $this->ensureSuccessfulResponse($response, $request->requestId);

            return $this->handleSuccessResponse($response, $outputPath, $startTime, $request);
        } catch (NetworkExceptionInterface $e) {
            throw new VeloxServerConnectionException($this->serverUrl, $e);
        } catch (ClientExceptionInterface $e) {
            if (\str_contains($e->getMessage(), 'timeout') || \str_contains($e->getMessage(), 'timed out')) {
                throw new VeloxTimeoutException(300, $request->requestId, $e);
            }

            throw new VeloxBuildFailedException(
                "HTTP request failed: {$e->getMessage()}",
                $request->requestId,
                $e,
            );
        }
    }

    public function isAvailable(): bool
    {
        try {
            $request = $this->createRequest('GET', '/health');
            $response = $this->httpClient->sendRequest($request);
            return $response->getStatusCode() === 200;
        } catch (\Exception) {
            return false;
        }
    }

    public function getServerVersion(): ?string
    {
        try {
            $request = $this->createRequest('GET', '/version');
            $response = $this->httpClient->sendRequest($request);

            if ($response->getStatusCode() !== 200) {
                return null;
            }

            $data = \json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
            return $data['version'] ?? null;
        } catch (\Exception) {
            return null;
        }
    }

    private function createRequest(string $method, string $path): RequestInterface
    {
        $uri = $this->serverUrl . $path;
        return $this->requestFactory
            ->createRequest($method, $uri)
            ->withHeader('Accept', 'application/json');
    }

    private function createJsonRequest(string $method, string $path, array $data): RequestInterface
    {
        $json = \json_encode($data, \JSON_THROW_ON_ERROR);
        $stream = $this->streamFactory->createStream($json);

        return $this
            ->createRequest($method, $path)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($stream);
    }

    private function ensureSuccessfulResponse(ResponseInterface $response, string $requestId): void
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode === 408) {
            throw new VeloxTimeoutException(300, $requestId);
        }

        if ($statusCode >= 400) {
            $body = $response->getBody()->getContents();
            $errorMessage = $this->extractErrorFromBody($body);

            throw new VeloxBuildFailedException(
                $errorMessage ?? "HTTP {$statusCode}: Request failed",
                $requestId,
            );
        }
    }

    private function handleSuccessResponse(
        ResponseInterface $response,
        string $outputPath,
        float $startTime,
        BuildRequest $request,
    ): BuildResult {
        $body = $response->getBody()->getContents();
        $contentType = $response->getHeaderLine('Content-Type');

        if (\str_contains($contentType, 'application/json')) {
            // JSON response - contains build result with path and logs
            $data = \json_decode($body, true, 512, \JSON_THROW_ON_ERROR);

            if (isset($data['error'])) {
                throw new VeloxBuildFailedException($data['error'], $request->requestId);
            }

            // Velox server returns JSON with 'path' to temporary binary and 'logs'
            if (isset($data['path'])) {
                // Copy binary from the local filesystem path provided by server
                $this->copyBinary($data['path'], $outputPath);

                $endTime = \microtime(true);
                $binarySize = $this->files->size($outputPath);

                // Parse logs from response
                $logs = ['Remote build via Velox server'];
                if (isset($data['logs']) && \is_string($data['logs'])) {
                    $logs = \array_merge($logs, \explode("\n", \trim($data['logs'])));
                }

                return new BuildResult(
                    success: true,
                    binaryPath: $outputPath,
                    buildTimeSeconds: $endTime - $startTime,
                    binarySizeBytes: $binarySize,
                    logs: $logs,
                    errors: [],
                    configPath: null,
                    buildHash: $request->requestId,
                );
            }

            throw new VeloxBuildFailedException(
                'Unexpected response format: missing "path" field in JSON response',
                $request->requestId,
            );
        }

        // Binary response - save directly (fallback for direct binary responses)
        $this->files->write($outputPath, $body);
        \chmod($outputPath, 0755); // Make executable

        $endTime = \microtime(true);
        $binarySize = $this->files->size($outputPath);

        return new BuildResult(
            success: true,
            binaryPath: $outputPath,
            buildTimeSeconds: $endTime - $startTime,
            binarySizeBytes: $binarySize,
            logs: ["Remote build via Velox server (direct binary response)"],
            errors: [],
            configPath: null,
            buildHash: $request->requestId,
        );
    }

    private function copyBinary(string $serverPath, string $outputPath): void
    {
        try {
            // The server returns a path to the binary on the local filesystem
            // Simply copy it to the output path
            if (!$this->files->exists($serverPath)) {
                throw new \RuntimeException("Binary not found at path: {$serverPath}");
            }

            $this->files->copy($serverPath, $outputPath);
            \chmod($outputPath, 0755); // Make executable
        } catch (\Exception $e) {
            throw new \RuntimeException("Binary copy failed: {$e->getMessage()}", 0, $e);
        }
    }

    private function extractErrorFromBody(string $body): ?string
    {
        if (empty($body)) {
            return null;
        }

        try {
            $data = \json_decode($body, true, 512, \JSON_THROW_ON_ERROR);
            return $data['error'] ?? $data['message'] ?? null;
        } catch (\JsonException) {
            // Not JSON, return raw body if it's reasonably short
            return \strlen($body) < 500 ? $body : null;
        }
    }
}
