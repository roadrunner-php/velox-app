<?php

declare(strict_types=1);

namespace App\Module\Velox\Service;

final readonly class VersionComparisonService
{
    /**
     * Compare two semantic versions
     *
     * @return int -1 if version1 < version2, 0 if equal, 1 if version1 > version2
     */
    public function compareVersions(string $version1, string $version2): int
    {
        $v1 = $this->normalizeVersion($version1);
        $v2 = $this->normalizeVersion($version2);

        return \version_compare($v1, $v2);
    }

    /**
     * Check if a version is newer than another
     */
    public function isNewerVersion(string $currentVersion, string $latestVersion): bool
    {
        return $this->compareVersions($latestVersion, $currentVersion) > 0;
    }

    /**
     * Check if version is a stable release (not pre-release)
     */
    public function isStableVersion(string $version): bool
    {
        $normalized = $this->normalizeVersion($version);

        // Check for pre-release indicators
        $preReleasePatterns = [
            '/-(alpha|beta|rc|dev|snapshot)/i',
            '/\+/',  // Build metadata
        ];

        foreach ($preReleasePatterns as $pattern) {
            if (\preg_match($pattern, $normalized)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the latest stable version from an array of versions
     *
     * @param array<string> $versions
     */
    public function getLatestStableVersion(array $versions): ?string
    {
        $stableVersions = \array_filter($versions, [$this, 'isStableVersion']);

        if (empty($stableVersions)) {
            return null;
        }

        \usort($stableVersions, [$this, 'compareVersions']);

        return \end($stableVersions);
    }

    /**
     * Check if a version follows semantic versioning format
     */
    public function isValidSemanticVersion(string $version): bool
    {
        $pattern = '/^v?(\d+)\.(\d+)\.(\d+)(?:-([0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*))?(?:\+([0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*))?$/';

        return \preg_match($pattern, $version) === 1;
    }

    /**
     * Extract major version from a version string
     */
    public function getMajorVersion(string $version): int
    {
        $normalized = $this->normalizeVersion($version);
        $parts = \explode('.', $normalized);

        return (int) ($parts[0] ?? 0);
    }

    /**
     * Check if two versions are compatible (same major version)
     */
    public function areVersionsCompatible(string $version1, string $version2): bool
    {
        return $this->getMajorVersion($version1) === $this->getMajorVersion($version2);
    }

    /**
     * Get version update recommendation
     *
     * @return array{updateType: string, recommended: bool, reason: string}
     */
    public function getUpdateRecommendation(string $currentVersion, string $latestVersion): array
    {
        if (!$this->isNewerVersion($currentVersion, $latestVersion)) {
            return [
                'updateType' => 'none',
                'recommended' => false,
                'reason' => 'Current version is up to date',
            ];
        }

        $currentMajor = $this->getMajorVersion($currentVersion);
        $latestMajor = $this->getMajorVersion($latestVersion);

        if ($latestMajor > $currentMajor) {
            return [
                'updateType' => 'major',
                'recommended' => false,
                'reason' => 'Major version update may contain breaking changes',
            ];
        }

        if ($this->areVersionsCompatible($currentVersion, $latestVersion)) {
            return [
                'updateType' => 'minor_patch',
                'recommended' => true,
                'reason' => 'Compatible update with bug fixes and new features',
            ];
        }

        return [
            'updateType' => 'unknown',
            'recommended' => false,
            'reason' => 'Version compatibility could not be determined',
        ];
    }

    /**
     * Normalize version string for comparison
     */
    private function normalizeVersion(string $version): string
    {
        // Remove 'v' prefix if present
        $normalized = \ltrim($version, 'v');

        // Handle special cases like 'master', 'main', 'latest'
        if (\in_array(\strtolower($normalized), ['master', 'main', 'latest', 'HEAD'])) {
            return '999.999.999'; // Treat as very high version for comparison
        }

        // Ensure we have at least 3 parts (major.minor.patch)
        $parts = \explode('.', $normalized);
        while (\count($parts) < 3) {
            $parts[] = '0';
        }

        return \implode('.', \array_slice($parts, 0, 3));
    }
}
