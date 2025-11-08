<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Discovery\Repository;

use App\Module\Velox\Plugin\Discovery\DTO\DiscoveryStatistics;
use App\Module\Velox\Plugin\DTO\Plugin;
use Psr\SimpleCache\CacheInterface;

/**
 * Simple in-memory storage for discovered plugins
 */
final class CachePluginRepository implements DiscoveredPluginRepositoryInterface
{
    private const string PLUGINS_KEY = 'plugins';
    private const string METATDATA_KEY = 'metadata';

    private ?DiscoveryStatistics $metadata = null;

    public function __construct(
        private readonly CacheInterface $cache,
    ) {}

    public function findAll(): array
    {
        return \array_values($this->fetch());
    }

    public function findByName(string $name): ?Plugin
    {
        return $this->fetch()[$name] ?? null;
    }

    public function save(Plugin $plugin): void
    {
        $plugins = $this->fetch();

        $plugins[$plugin->name] = $plugin;
        $this->cache->set(self::PLUGINS_KEY, $plugins);
    }

    public function remove(string $name): void
    {
        $plugins = $this->fetch();
        unset($plugins[$name]);
        $this->cache->set(self::PLUGINS_KEY, $plugins);
    }

    public function clear(): void
    {
        $this->cache->delete(self::PLUGINS_KEY);
        $this->cache->delete(self::METATDATA_KEY);
    }

    public function getMetadata(): ?DiscoveryStatistics
    {
        return $this->cache->get(self::METATDATA_KEY, null);
    }

    public function saveMetadata(DiscoveryStatistics $statistics): void
    {
        $this->cache->set(self::METATDATA_KEY, $statistics);
    }

    private function fetch(): array
    {
        return $this->cache->get(self::PLUGINS_KEY, []);
    }
}
