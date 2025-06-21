<?php

declare(strict_types=1);

namespace App\Module\Velox\Version\Service;

use App\Module\Velox\Environment\Service\EnvironmentFileService;
use App\Module\Velox\Plugin\DTO\Plugin;
use App\Module\Velox\Plugin\DTO\PluginRepository;
use App\Module\Velox\Plugin\Service\PluginProviderInterface;
use App\Module\Velox\Version\DTO\VersionUpdateInfo;
use App\Module\Velox\Version\Exception\VersionCheckException;
use Spiral\Exceptions\ExceptionReporterInterface;

final readonly class PluginVersionManager
{
    public function __construct(
        private PluginProviderInterface $pluginProvider,
        private GitHubVersionChecker $versionChecker,
        private VersionComparisonService $comparisonService,
        private EnvironmentFileService $environmentService,
        private ExceptionReporterInterface $reporter,
    ) {}

    /**
     * Check for updates for all plugins
     *
     * @return array<VersionUpdateInfo>
     */
    public function checkForUpdates(): array
    {
        $plugins = $this->pluginProvider->getAllPlugins();
        $updates = [];

        foreach ($plugins as $plugin) {
            if ($plugin->repositoryType !== PluginRepository::Github) {
                continue;
            }

            try {
                $updateInfo = $this->checkPluginUpdate($plugin);
                if ($updateInfo !== null) {
                    $updates[] = $updateInfo;
                }
            } catch (VersionCheckException $e) {
                $this->reporter->report($e);
            }
        }

        return $updates;
    }

    /**
     * Update plugin versions in environment file
     *
     * @param array<string> $pluginNames Empty array means update all recommended plugins
     */
    public function updatePluginVersions(array $pluginNames = []): array
    {
        $updates = $this->checkForUpdates();
        $envUpdates = [];
        $results = [];

        foreach ($updates as $updateInfo) {
            // Skip if specific plugins requested and this plugin is not in the list
            if (!empty($pluginNames) && !\in_array($updateInfo->pluginName, $pluginNames)) {
                continue;
            }

            // Only update recommended updates by default
            if (!$updateInfo->isRecommended && empty($pluginNames)) {
                continue;
            }

            $envKey = $this->getEnvironmentKey($updateInfo->pluginName);
            $envUpdates[$envKey] = $updateInfo->latestVersion;

            $results[] = [
                'plugin' => $updateInfo->pluginName,
                'from' => $updateInfo->currentVersion,
                'to' => $updateInfo->latestVersion,
                'type' => $updateInfo->updateType,
            ];
        }

        if (!empty($envUpdates)) {
            $this->environmentService->updateEnvironmentVariables($envUpdates);
        }

        return $results;
    }

    /**
     * Check for update for a specific plugin
     */
    public function checkPluginUpdate(Plugin $plugin): ?VersionUpdateInfo
    {
        if ($plugin->repositoryType !== PluginRepository::Github) {
            return null;
        }

        $latestVersion = $this->versionChecker->getLatestVersion($plugin);
        if ($latestVersion === null) {
            return null;
        }

        $currentVersion = $plugin->ref;

        // Skip if versions are the same
        if ($currentVersion === $latestVersion) {
            return null;
        }

        // Skip if current version is newer (shouldn't happen but just in case)
        if (!$this->comparisonService->isNewerVersion($currentVersion, $latestVersion)) {
            return null;
        }

        $recommendation = $this->comparisonService->getUpdateRecommendation($currentVersion, $latestVersion);

        return new VersionUpdateInfo(
            pluginName: $plugin->name,
            currentVersion: $currentVersion,
            latestVersion: $latestVersion,
            updateType: $recommendation['updateType'],
            isRecommended: $recommendation['recommended'],
            reason: $recommendation['reason'],
        );
    }

    /**
     * Get GitHub API rate limit information
     */
    public function getRateLimitInfo(): array
    {
        return $this->versionChecker->getRateLimitInfo();
    }

    /**
     * Validate all plugin repositories exist
     *
     * @return array<string, bool> Plugin name => exists
     */
    public function validatePluginRepositories(): array
    {
        $plugins = $this->pluginProvider->getAllPlugins();
        $results = [];

        foreach ($plugins as $plugin) {
            if ($plugin->repositoryType !== PluginRepository::Github) {
                continue;
            }

            $results[$plugin->name] = $this->versionChecker->repositoryExists($plugin);
        }

        return $results;
    }

    /**
     * Get outdated plugins summary
     *
     * @return array{total: int, outdated: int, recommended_updates: int, major_updates: int}
     */
    public function getUpdatesSummary(): array
    {
        $updates = $this->checkForUpdates();

        $summary = [
            'total' => \count($this->pluginProvider->getAllPlugins()),
            'outdated' => \count($updates),
            'recommended_updates' => 0,
            'major_updates' => 0,
        ];

        foreach ($updates as $update) {
            if ($update->isRecommended) {
                $summary['recommended_updates']++;
            }
            if ($update->updateType === 'major') {
                $summary['major_updates']++;
            }
        }

        return $summary;
    }

    /**
     * Generate environment key for plugin
     */
    private function getEnvironmentKey(string $pluginName): string
    {
        // Convert plugin name to environment variable format
        // e.g., 'appLogger' -> 'RR_PLUGIN_APP_LOGGER'

        $envName = \strtoupper(\preg_replace('/([a-z])([A-Z])/', '$1_$2', $pluginName));
        return "RR_PLUGIN_{$envName}";
    }
}
