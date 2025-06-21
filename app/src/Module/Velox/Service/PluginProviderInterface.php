<?php

declare(strict_types=1);

namespace App\Module\Velox\Service;

use App\Module\Velox\DTO\Plugin;
use App\Module\Velox\DTO\PluginCategory;

interface PluginProviderInterface
{
    /**
     * @return array<Plugin>
     */
    public function getAllPlugins(): array;

    /**
     * @return array<Plugin>
     */
    public function getPluginsByCategory(PluginCategory $category): array;

    /**
     * @return array<Plugin>
     */
    public function getOfficialPlugins(): array;

    /**
     * @return array<Plugin>
     */
    public function getCommunityPlugins(): array;

    /**
     * @return array<Plugin>
     */
    public function searchPlugins(string $query): array;

    public function getPluginByName(string $name): ?Plugin;
}
