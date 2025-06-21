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
     * @param VersionUpdateInfo[] $updates
     */
    public function updatePluginVersions(array $updates): array
    {
        $envUpdates = [];

        foreach ($updates as $updateInfo) {
            $envKey = $this->getEnvironmentKey($updateInfo->pluginName);
            $envUpdates[$envKey] = $updateInfo->latestVersion;
        }

        return $envUpdates;
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
