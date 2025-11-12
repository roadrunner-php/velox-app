<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Discovery\Service;

use App\Module\Github\DiscoveryClient;
use App\Module\Velox\Plugin\Discovery\DTO\DiscoveryResult;
use App\Module\Velox\Plugin\Discovery\DTO\DiscoveryStatistics;
use App\Module\Velox\Plugin\Discovery\Exception\GitHubApiException;
use App\Module\Velox\Plugin\Discovery\Exception\ManifestValidationException;
use App\Module\Velox\Plugin\DTO\Plugin;
use Psr\Log\LoggerInterface;
use Spiral\Http\Exception\ClientException\ServerErrorException;

/**
 * Main orchestrator for GitHub plugin discovery
 */
final readonly class GitHubDiscoveryService
{
    public function __construct(
        private DiscoveryClient $githubClient,
        private ManifestParserService $manifestParser,
        private PluginRegistryService $pluginRegistry,
        private LoggerInterface $logger,
        private string $organization = 'roadrunner-plugins',
        private string $manifestFile = '.velox.yaml',
    ) {}

    /**
     * Discover all plugins from organization
     */
    public function discover(bool $force = false): DiscoveryResult
    {
        $startTime = \microtime(true);
        $plugins = [];
        $failed = [];

        $this->logger->info('Starting plugin discovery', [
            'organization' => $this->organization,
        ]);

        try {
            $repositories = $this->githubClient->getOrganizationRepositories($this->organization);
        } catch (ServerErrorException $e) {
            $this->logger->error('Failed to fetch organization repositories', [
                'organization' => $this->organization,
                'error' => $e->getMessage(),
            ]);

            throw new GitHubApiException(
                "Failed to fetch repositories: {$e->getMessage()}",
                $this->organization,
            );
        }

        $this->logger->info('Found repositories', [
            'count' => \count($repositories),
        ]);

        foreach ($repositories as $repo) {
            $repoName = $repo['name'];

            try {
                $plugin = $this->discoverRepository($repoName);
                if ($plugin !== null) {
                    $plugins[] = $plugin;
                }
            } catch (ManifestValidationException $e) {
                $this->logger->warning('Manifest validation failed', [
                    'repository' => $repoName,
                    'error' => $e->getMessage(),
                    'errors' => $e->errors,
                ]);
                $failed[$repoName] = $e->getMessage();
            } catch (GitHubApiException $e) {
                $this->logger->warning('GitHub API error', [
                    'repository' => $repoName,
                    'error' => $e->getMessage(),
                ]);
                $failed[$repoName] = $e->getMessage();
            } catch (\Exception $e) {
                $this->logger->error('Unexpected error', [
                    'repository' => $repoName,
                    'error' => $e->getMessage(),
                ]);
                $failed[$repoName] = $e->getMessage();
            }
        }

        $duration = (\microtime(true) - $startTime) * 1000;

        $statistics = new DiscoveryStatistics(
            repositoriesScanned: \count($repositories),
            pluginsRegistered: \count($plugins),
            pluginsFailed: \count($failed),
            durationMs: $duration,
            lastScan: new \DateTimeImmutable(),
            failedRepositories: $failed,
        );

        $this->pluginRegistry->saveMetadata($statistics);

        $this->logger->info('Plugin discovery completed', [
            'plugins_registered' => \count($plugins),
            'plugins_failed' => \count($failed),
            'duration_ms' => $duration,
        ]);

        return new DiscoveryResult(
            plugins: $plugins,
            statistics: $statistics,
            success: true,
        );
    }

    /**
     * Discover single repository
     */
    public function discoverRepository(string $repositoryName): ?Plugin
    {
        // Get latest release
        try {
            $release = $this->githubClient->getLatestRelease($this->organization, $repositoryName);
        } catch (ServerErrorException $e) {
            // No releases found - skip this repository
            $this->logger->debug('No releases found', [
                'repository' => $repositoryName,
            ]);
            return null;
        }

        // Skip drafts and pre-releases
        if ($release['draft'] ?? false || $release['prerelease'] ?? false) {
            $this->logger->debug('Skipping draft/prerelease', [
                'repository' => $repositoryName,
                'tag' => $release['tag_name'],
            ]);
            return null;
        }

        $version = $release['tag_name'];

        // Get manifest file
        try {
            $manifestContent = $this->githubClient->getFileContent(
                $this->organization,
                $repositoryName,
                $this->manifestFile,
                $version,
            );
        } catch (ServerErrorException) {
            throw new GitHubApiException(
                "Manifest file not found: {$this->manifestFile}",
                $repositoryName,
                404,
            );
        }

        // Parse and validate manifest
        $manifest = $this->manifestParser->parse($manifestContent, $this->organization, $repositoryName, $version);

        // Register plugin
        return $this->pluginRegistry->register($manifest);
    }

    /**
     * Update single plugin from webhook event
     */
    public function updateFromWebhook(string $repositoryName, string $tagName): ?Plugin
    {
        $this->logger->info('Updating plugin from webhook', [
            'repository' => $repositoryName,
            'tag' => $tagName,
        ]);

        try {
            // Get manifest from release
            $manifestContent = $this->githubClient->getFileContent(
                $this->organization,
                $repositoryName,
                $this->manifestFile,
                $tagName,
            );

            $manifest = $this->manifestParser->parse($manifestContent, $this->organization, $repositoryName, $tagName);

            return $this->pluginRegistry->update($manifest);
        } catch (ManifestValidationException $e) {
            $this->logger->warning('Webhook update failed - validation', [
                'repository' => $repositoryName,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Webhook update failed', [
                'repository' => $repositoryName,
                'error' => $e->getMessage(),
            ]);
            throw new GitHubApiException(
                "Failed to update plugin: {$e->getMessage()}",
                $repositoryName,
            );
        }
    }
}
