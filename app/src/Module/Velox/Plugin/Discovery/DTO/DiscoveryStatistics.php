<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Discovery\DTO;

/**
 * Statistics about discovery scan
 */
final readonly class DiscoveryStatistics
{
    public function __construct(
        public int $repositoriesScanned,
        public int $pluginsRegistered,
        public int $pluginsFailed,
        public float $durationMs,
        public \DateTimeImmutable $lastScan,
        public array $failedRepositories = [],
    ) {}

    public function toArray(): array
    {
        return [
            'repositories_scanned' => $this->repositoriesScanned,
            'plugins_registered' => $this->pluginsRegistered,
            'plugins_failed' => $this->pluginsFailed,
            'duration_ms' => $this->durationMs,
            'last_scan' => $this->lastScan->format('c'),
            'failed_repositories' => $this->failedRepositories,
        ];
    }
}
