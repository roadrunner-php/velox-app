<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Discovery\Service;

use App\Module\Velox\Plugin\Discovery\Repository\DiscoveredPluginRepositoryInterface;
use App\Module\Velox\Plugin\DTO\Plugin;
use App\Module\Velox\Plugin\DTO\PluginCategory;
use App\Module\Velox\Plugin\Service\PluginProviderInterface;

/**
 * Plugin provider for discovered community plugins
 */
final readonly class GitHubDiscoveryPluginProvider implements PluginProviderInterface
{
    public function __construct(
        private DiscoveredPluginRepositoryInterface $repository,
        private GitHubDiscoveryService $discoveryService,
        private bool $lazyLoad = true,
    ) {}

    public function getAllPlugins(): array
    {
        $this->ensureDiscovered();
        return $this->repository->findAll();
    }

    public function getOfficialPlugins(): array
    {
        // Discovery provider only returns community plugins
        return [];
    }

    public function getCommunityPlugins(): array
    {
        $this->ensureDiscovered();
        return $this->repository->findAll();
    }

    public function getPluginByName(string $name): ?Plugin
    {
        $this->ensureDiscovered();
        return $this->repository->findByName($name);
    }

    public function getPluginsByCategory(PluginCategory $category): array
    {
        $this->ensureDiscovered();

        return \array_filter(
            $this->repository->findAll(),
            static fn(Plugin $plugin): bool => $plugin->category === $category,
        );
    }

    public function searchPlugins(string $query): array
    {
        $this->ensureDiscovered();

        $query = \strtolower($query);

        return \array_filter(
            $this->repository->findAll(),
            static fn(Plugin $plugin): bool =>
                \str_contains(\strtolower($plugin->name), $query) ||
                \str_contains(\strtolower($plugin->description), $query),
        );
    }

    /**
     * Trigger discovery if lazy loading is enabled and no plugins are cached
     */
    private function ensureDiscovered(): void
    {
        if (!$this->lazyLoad) {
            return;
        }

        $metadata = $this->repository->getMetadata();

        // If no metadata exists, discovery hasn't run yet
        if ($metadata === null) {
            $this->discoveryService->discover();
        }
    }
}
