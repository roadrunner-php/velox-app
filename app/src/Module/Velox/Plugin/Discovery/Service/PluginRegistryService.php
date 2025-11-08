<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Discovery\Service;

use App\Module\Velox\Plugin\Discovery\DTO\PluginManifest;
use App\Module\Velox\Plugin\Discovery\Repository\DiscoveredPluginRepositoryInterface;
use App\Module\Velox\Plugin\DTO\Plugin;
use App\Module\Velox\Plugin\DTO\PluginRepository;
use App\Module\Velox\Plugin\DTO\PluginSource;
use Psr\Log\LoggerInterface;

/**
 * Registers and stores discovered plugins
 */
final readonly class PluginRegistryService
{
    public function __construct(
        private DiscoveredPluginRepositoryInterface $repository,
        private LoggerInterface $logger,
    ) {}

    /**
     * Save discovery metadata
     */
    public function saveMetadata(\App\Module\Velox\Plugin\Discovery\DTO\DiscoveryStatistics $statistics): void
    {
        $this->repository->saveMetadata($statistics);
    }

    /**
     * Register plugin from manifest
     */
    public function register(PluginManifest $manifest): Plugin
    {
        $plugin = new Plugin(
            name: $manifest->name,
            ref: $manifest->version,
            owner: $manifest->owner,
            repository: $manifest->repository,
            repositoryType: PluginRepository::from($manifest->repositoryType ?? 'github'),
            source: PluginSource::Community,
            folder: $manifest->folder,
            replace: $manifest->replace,
            dependencies: $manifest->dependencies,
            description: $manifest->description,
            category: $manifest->category,
            docsUrl: $manifest->docsUrl,
        );

        $this->repository->save($plugin);

        $this->logger->info('Plugin registered', [
            'plugin' => $manifest->name,
            'version' => $manifest->version,
            'category' => $manifest->category->value,
        ]);

        return $plugin;
    }

    /**
     * Update existing plugin
     */
    public function update(PluginManifest $manifest): Plugin
    {
        $existing = $this->repository->findByName($manifest->name);

        $this->logger->info('Plugin updated', [
            'plugin' => $manifest->name,
            'old_version' => $existing?->ref ?? 'none',
            'new_version' => $manifest->version,
        ]);

        return $this->register($manifest);
    }

    /**
     * Remove plugin by name
     */
    public function remove(string $name): void
    {
        $this->repository->remove($name);

        $this->logger->info('Plugin removed', [
            'plugin' => $name,
        ]);
    }

    /**
     * Clear all plugins
     */
    public function clear(): void
    {
        $this->repository->clear();

        $this->logger->info('All discovered plugins cleared');
    }
}
