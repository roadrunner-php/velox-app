<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Discovery\Repository;

use App\Module\Velox\Plugin\Discovery\DTO\DiscoveryStatistics;
use App\Module\Velox\Plugin\DTO\Plugin;

/**
 * Repository contract for storing discovered plugins
 */
interface DiscoveredPluginRepositoryInterface
{
    /**
     * Find all discovered plugins
     *
     * @return array<Plugin>
     */
    public function findAll(): array;

    /**
     * Find plugin by name
     */
    public function findByName(string $name): ?Plugin;

    /**
     * Store or update plugin
     */
    public function save(Plugin $plugin): void;

    /**
     * Remove plugin
     */
    public function remove(string $name): void;

    /**
     * Clear all plugins
     */
    public function clear(): void;

    /**
     * Get discovery metadata
     */
    public function getMetadata(): ?DiscoveryStatistics;

    /**
     * Save discovery metadata
     */
    public function saveMetadata(DiscoveryStatistics $statistics): void;
}
