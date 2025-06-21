<?php

declare(strict_types=1);

namespace App\Module\Velox\Version\Service;

use App\Module\Velox\Plugin\DTO\Plugin;
use App\Module\Velox\Plugin\DTO\PluginRepository;
use App\Module\Velox\Version\Exception\VersionCheckException;

final readonly class GitHubVersionChecker
{
    private const string GITHUB_API_URL = 'https://api.github.com';

    public function __construct(
        private ?string $githubToken = null,
    ) {}

    /**
     * Get the latest stable version for a GitHub plugin
     */
    public function getLatestVersion(Plugin $plugin): ?string
    {
        if ($plugin->repositoryType !== PluginRepository::Github) {
            return null;
        }

        $url = sprintf(
            '%s/repos/%s/%s/releases/latest',
            self::GITHUB_API_URL,
            $plugin->owner,
            $plugin->repository,
        );

        try {
            $response = $this->makeApiRequest($url);
            $data = \json_decode($response, true);

            if (\json_last_error() !== JSON_ERROR_NONE) {
                throw new VersionCheckException("Invalid JSON response for {$plugin->name}");
            }

            return $data['tag_name'] ?? null;
        } catch (\Exception $e) {
            throw new VersionCheckException(
                "Failed to check version for plugin {$plugin->name}: " . $e->getMessage(),
                previous: $e,
            );
        }
    }

    /**
     * Get all available versions for a GitHub plugin
     *
     * @return array<string>
     */
    public function getAllVersions(Plugin $plugin): array
    {
        if ($plugin->repositoryType !== PluginRepository::Github) {
            return [];
        }

        $url = \sprintf(
            '%s/repos/%s/%s/releases',
            self::GITHUB_API_URL,
            $plugin->owner,
            $plugin->repository,
        );

        try {
            $response = $this->makeApiRequest($url);
            $data = \json_decode($response, true);

            if (\json_last_error() !== JSON_ERROR_NONE) {
                throw new VersionCheckException("Invalid JSON response for {$plugin->name}");
            }

            return array_map(
                static fn(array $release) => $release['tag_name'],
                \array_filter($data, static fn(array $release) => !$release['prerelease']),
            );
        } catch (\Exception $e) {
            throw new VersionCheckException(
                "Failed to get versions for plugin {$plugin->name}: " . $e->getMessage(),
                previous: $e,
            );
        }
    }

    /**
     * Check if a repository exists and is accessible
     */
    public function repositoryExists(Plugin $plugin): bool
    {
        if ($plugin->repositoryType !== PluginRepository::Github) {
            return false;
        }

        $url = \sprintf(
            '%s/repos/%s/%s',
            self::GITHUB_API_URL,
            $plugin->owner,
            $plugin->repository,
        );

        try {
            $this->makeApiRequest($url);
            return true;
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Get rate limit information
     *
     * @return array{limit: int, remaining: int, reset: int}
     */
    public function getRateLimitInfo(): array
    {
        $url = self::GITHUB_API_URL . '/rate_limit';

        try {
            $response = $this->makeApiRequest($url);
            $data = \json_decode($response, true);

            return [
                'limit' => $data['rate']['limit'] ?? 0,
                'remaining' => $data['rate']['remaining'] ?? 0,
                'reset' => $data['rate']['reset'] ?? 0,
            ];
        } catch (\Exception) {
            return ['limit' => 0, 'remaining' => 0, 'reset' => 0];
        }
    }

    private function makeApiRequest(string $url): string
    {
        $context = \stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => $this->buildHeaders(),
                'timeout' => 30,
            ],
        ]);

        $response = file_get_contents($url, false, $context);

        if ($response === false) {
            $error = \error_get_last();
            throw new \RuntimeException($error['message'] ?? 'Failed to make API request');
        }

        // Check HTTP status code
        if (isset($http_response_header)) {
            $statusLine = $http_response_header[0] ?? '';
            if (\preg_match('/HTTP\/\d\.\d\s+(\d+)/', $statusLine, $matches)) {
                $statusCode = (int) $matches[1];
                if ($statusCode >= 400) {
                    throw new \RuntimeException("HTTP {$statusCode} error");
                }
            }
        }

        return $response;
    }

    private function buildHeaders(): string
    {
        $headers = [
            'User-Agent: RoadRunner-Velox-Builder/1.0',
            'Accept: application/vnd.github.v3+json',
        ];

        if ($this->githubToken !== null) {
            $headers[] = "Authorization: Bearer {$this->githubToken}";
        }

        return \implode("\r\n", $headers);
    }
}
