<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Endpoint\Http\v1\Plugin\Dependency;

use App\Application\HTTP\Response\JsonResource;
use App\Module\Velox\Dependency\DTO\DependencyResolution;
use App\Module\Velox\Plugin\Endpoint\Http\v1\Plugin\PluginResource;

/**
 * @extends JsonResource<DependencyResolution>
 */
final class PluginDependenciesResource extends JsonResource
{
    public function __construct(
        private readonly string $plugin,
        mixed $data = [],
    ) {
        parent::__construct($data);
    }

    #[\Override]
    protected function mapData(): array|\JsonSerializable
    {
        $dependencyResult = $this->data;

        $dependencies = [];
        foreach ($dependencyResult->requiredPlugins as $depPlugin) {
            if ($depPlugin->name === $this->plugin) {
                continue; // Skip the plugin itself
            }

            $dependencies[] = new PluginResource($depPlugin);
        }

        $conflicts = [];
        foreach ($dependencyResult->conflicts as $conflict) {
            if ($conflict->pluginName === $this->plugin) {
                continue; // Skip conflicts for the plugin itself
            }

            $conflicts[] = [
                'plugin' => $conflict->pluginName,
                'type' => $conflict->conflictType,
                'message' => $conflict->message,
                'severity' => $conflict->severity,
                'conflicting_plugins' => $conflict->conflictingPlugins,
            ];
        }

        return [
            'resolved_dependencies' => $dependencies,
            'dependency_count' => [
                'resolved' => \count($dependencies),
            ],
            'conflicts' => $conflicts,
            'is_valid' => $dependencyResult->isValid,
        ];
    }
}
