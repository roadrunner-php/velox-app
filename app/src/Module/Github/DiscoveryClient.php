<?php

declare(strict_types=1);

namespace App\Module\Github;

use App\Module\Velox\Plugin\Discovery\Exception\RateLimitException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Spiral\Http\Exception\ClientException\ServerErrorException;

/**
 * GitHub API client for plugin discovery
 */
final readonly class DiscoveryClient
{
    private const string API_BASE = 'https://api.github.com';

    public function __construct(
        private ClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory,
        private ?string $token = null,
    ) {}

    /**
     * Get all repositories from organization
     *
     * @return array<array{name: string, full_name: string}>
     */
    public function getOrganizationRepositories(string $organization): array
    {
        $repositories = [];
        $page = 1;
        $perPage = 100;

        do {
            $response = $this->get("/orgs/{$organization}/repos", [
                'per_page' => $perPage,
                'page' => $page,
                'type' => 'public',
            ]);

            if (empty($response)) {
                break;
            }

            $repositories = \array_merge($repositories, $response);
            $page++;
        } while (\count($response) === $perPage);

        return $repositories;
    }

    /**
     * Get latest release for repository
     *
     * @return array{tag_name: string, draft: bool, prerelease: bool}
     * @throws ServerErrorException
     */
    public function getLatestRelease(string $owner, string $repository): array
    {
        return $this->get("/repos/{$owner}/{$repository}/releases/latest");
    }

    /**
     * Get file content from repository
     *
     * @throws ServerErrorException
     */
    public function getFileContent(
        string $owner,
        string $repository,
        string $path,
        string $ref = 'main',
    ): string {
        $response = $this->get("/repos/{$owner}/{$repository}/contents/{$path}", [
            'ref' => $ref,
        ]);

        if (!isset($response['content'])) {
            throw new ServerErrorException('File content not found');
        }

        // GitHub returns base64 encoded content
        $content = \base64_decode($response['content'], true);

        if ($content === false) {
            throw new ServerErrorException('Failed to decode file content');
        }

        return $content;
    }

    /**
     * Get rate limit information
     *
     * @return array{limit: int, remaining: int, reset: int}
     */
    public function getRateLimit(): array
    {
        $response = $this->get('/rate_limit');

        return [
            'limit' => $response['rate']['limit'] ?? 0,
            'remaining' => $response['rate']['remaining'] ?? 0,
            'reset' => $response['rate']['reset'] ?? 0,
        ];
    }

    /**
     * Make GET request to GitHub API
     *
     * @throws ServerErrorException
     */
    private function get(string $path, array $query = []): array
    {
        $url = self::API_BASE . $path;

        if (!empty($query)) {
            $url .= '?' . \http_build_query($query);
        }
        $request = $this->requestFactory->createRequest('GET', $url);

        // Add authentication if token provided
        if ($this->token !== null) {
            $request = $request->withHeader('Authorization', "Bearer {$this->token}");
        }

        // Add required headers
        $request = $request
            ->withHeader('Accept', 'application/vnd.github+json')
            ->withHeader('X-GitHub-Api-Version', '2022-11-28')
            ->withHeader('User-Agent', 'Velox-Discovery/1.0');

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (\Exception $e) {
            throw new ServerErrorException(
                "GitHub API request failed: {$e->getMessage()}",
                $e,
            );
        }

        $statusCode = $response->getStatusCode();

        // Handle rate limiting
        if ($statusCode === 403) {
            $resetTime = (int) $response->getHeaderLine('X-RateLimit-Reset');
            throw new RateLimitException(
                $resetTime,
            );
        }

        // Handle errors
        if ($statusCode >= 400) {
            $body = (string) $response->getBody();
            throw new ServerErrorException(
                "GitHub API error ({$statusCode}): {$body}",
            );
        }

        $body = (string) $response->getBody();
        $data = \json_decode($body, true);

        if (!\is_array($data)) {
            throw new ServerErrorException('Invalid JSON response from GitHub API');
        }

        return $data;
    }
}
