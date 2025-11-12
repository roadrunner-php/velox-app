<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryBuilder\Client;

use App\Module\Velox\BinaryBuilder\DTO\BuildRequest;
use App\Module\Velox\BinaryBuilder\DTO\BuildResult;
use App\Module\Velox\BinaryBuilder\Exception\VeloxBuildFailedException;
use App\Module\Velox\BinaryBuilder\Exception\VeloxServerConnectionException;
use App\Module\Velox\BinaryBuilder\Exception\VeloxTimeoutException;
use Guzzle\Http\Client;
use Psr\Http\Message\ResponseInterface;
use Spiral\Files\FilesInterface;

final readonly class VeloxClient implements VeloxClientInterface
{
    private const string BUILD_ENDPOINT = '/api.service.v1.BuildService/Build';

    private Client $httpClient;

    public function __construct(
        private string $serverUrl,
        private int $timeoutSeconds,
        private FilesInterface $files,
    ) {
        $this->httpClient = new Client($serverUrl, [
            'timeout' => $timeoutSeconds,
            'connect_timeout' => 10,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function build(BuildRequest $request, string $outputPath): BuildResult
    {
        $startTime = \microtime(true);

        try {
            $response = $this->httpClient->post(self::BUILD_ENDPOINT, [
                'json' => $request->toArray(),
            ]);

            return $this->handleSuccessResponse($response, $outputPath, $startTime, $request);
        } catch (ConnectException $e) {
            throw new VeloxServerConnectionException($this->serverUrl, $e);
        } catch (RequestException $e) {
            if ($e->getCode() === 408 || \str_contains($e->getMessage(), 'timeout')) {
                throw new VeloxTimeoutException($this->timeoutSeconds, $request->requestId, $e);
            }

            throw new VeloxBuildFailedException(
                $this->extractErrorMessage($e),
                $request->requestId,
                $e,
            );
        } catch (GuzzleException $e) {
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
            $response = $this->httpClient->get('/health', ['timeout' => 5]);
            return $response->getStatusCode() === 200;
        } catch (\Exception) {
            return false;
        }
    }

    public function getServerVersion(): ?string
    {
        try {
            $response = $this->httpClient->get('/version', ['timeout' => 5]);
            $data = \json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
            return $data['version'] ?? null;
        } catch (\Exception) {
            return null;
        }
    }

    private function handleSuccessResponse(
        ResponseInterface $response,
        string $outputPath,
        float $startTime,
        BuildRequest $request,
    ): BuildResult {
        $body = $response->getBody()->getContents();

        // Check if response is JSON (error/status) or binary (success)
        $contentType = $response->getHeaderLine('Content-Type');

        if (\str_contains($contentType, 'application/json')) {
            // JSON response - could be error or build info
            $data = \json_decode($body, true, 512, \JSON_THROW_ON_ERROR);

            if (isset($data['error'])) {
                throw new VeloxBuildFailedException($data['error'], $request->requestId);
            }

            // If JSON contains binary URL, download it
            if (isset($data['binary_url'])) {
                $this->downloadBinary($data['binary_url'], $outputPath);
            } else {
                throw new VeloxBuildFailedException(
                    'Unexpected response format: no binary or download URL',
                    $request->requestId,
                );
            }
        } else {
            // Binary response - save directly
            $this->files->write($outputPath, $body);
        }

        $endTime = \microtime(true);
        $binarySize = $this->files->size($outputPath);

        return new BuildResult(
            success: true,
            binaryPath: $outputPath,
            buildTimeSeconds: $endTime - $startTime,
            binarySizeBytes: $binarySize,
            logs: ["Remote build via Velox server: {$this->serverUrl}"],
            errors: [],
            configPath: null,
            buildHash: $request->requestId,
        );
    }

    private function downloadBinary(string $url, string $outputPath): void
    {
        try {
            $response = $this->httpClient->get($url, ['sink' => $outputPath]);

            if ($response->getStatusCode() !== 200) {
                throw new \RuntimeException("Failed to download binary: HTTP {$response->getStatusCode()}");
            }
        } catch (GuzzleException $e) {
            throw new \RuntimeException("Binary download failed: {$e->getMessage()}", 0, $e);
        }
    }

    private function extractErrorMessage(\Throwable $exception): string
    {
        if ($exception instanceof RequestException && $exception->hasResponse()) {
            $response = $exception->getResponse();
            $body = $response->getBody()->getContents();

            try {
                $data = \json_decode($body, true, 512, \JSON_THROW_ON_ERROR);
                if (isset($data['error'])) {
                    return $data['error'];
                }
                if (isset($data['message'])) {
                    return $data['message'];
                }
            } catch (\JsonException) {
                // Response is not JSON, return raw body
                return $body ?: $exception->getMessage();
            }
        }

        return $exception->getMessage();
    }
}
