<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Discovery\DTO;

use App\Module\Velox\Plugin\DTO\Plugin;

/**
 * Result of plugin discovery scan
 */
final readonly class DiscoveryResult
{
    /**
     * @param array<Plugin> $plugins
     */
    public function __construct(
        public array $plugins,
        public DiscoveryStatistics $statistics,
        public bool $success,
    ) {}
}
