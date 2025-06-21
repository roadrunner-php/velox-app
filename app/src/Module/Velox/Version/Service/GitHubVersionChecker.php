<?php

declare(strict_types=1);

namespace App\Module\Velox\Version\Service;

use App\Module\Velox\Plugin\DTO\Plugin;
use App\Module\Velox\Plugin\DTO\PluginRepository;
use App\Module\Velox\Version\Exception\VersionCheckException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientInterface;

final readonly class GitHubVersionChecker
{
    private const string GITHUB_API_URL = 'https://api.github.com';

    public function __construct(
        private ClientInterface $httpClient,
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

        $url = \sprintf(
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

            return \array_map(
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

    private function makeApiRequest(string $url): string
    {
        $response = $this->httpClient->sendRequest(
            new Request('GET', $url, $this->buildHeaders()),
        );

        return (string) $response->getBody();
    }

    private function buildHeaders(): array
    {
        $headers = [
            'User-Agent' => 'RoadRunner-Velox-Builder/1.0',
            'Accept' => 'application/vnd.github.v3+json',
        ];

        if ($this->githubToken !== null) {
            $headers['Authorization'] = "Bearer {$this->githubToken}";
        }

        return $headers;
    }
}
