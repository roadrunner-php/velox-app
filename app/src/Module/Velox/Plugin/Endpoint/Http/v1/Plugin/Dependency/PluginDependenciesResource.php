<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Endpoint\Http\v1\Plugin\Dependency;

use App\Application\HTTP\Response\JsonResource;
use App\Module\Velox\Dependency\DTO\DependencyResolution;
use App\Module\Velox\Plugin\Endpoint\Http\v1\Plugin\PluginResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: PluginDependenciesResource::class,
    description: 'Plugin dependency resolution result with conflicts analysis',
    properties: [
        new OA\Property(
            property: 'resolved_dependencies',
            description: 'List of plugins that are required as dependencies (excluding the requested plugin itself)',
            type: 'array',
            items: new OA\Items(ref: PluginResource::class),
        ),
        new OA\Property(
            property: 'dependency_count',
            description: 'Statistics about resolved dependencies',
            properties: [
                new OA\Property(
                    property: 'resolved',
                    description: 'Number of successfully resolved dependencies',
                    type: 'integer',
                    example: 3,
                ),
            ],
            type: 'object',
        ),
        new OA\Property(
            property: 'conflicts',
            description: 'List of potential conflicts that may arise when using this plugin',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(
                        property: 'plugin',
                        description: 'Name of the conflicting plugin',
                        type: 'string',
                        example: 'incompatible-plugin',
                    ),
                    new OA\Property(
                        property: 'type',
                        description: 'Type of conflict',
                        type: 'string',
                        enum: [
                            'circular_dependency',
                            'missing_dependency',
                            'version_conflict',
                            'resource_conflict',
                            'incompatible_plugin',
                            'duplicate_plugin',
                        ],
                        example: 'version_conflict',
                    ),
                    new OA\Property(
                        property: 'message',
                        description: 'Human-readable description of the conflict',
                        type: 'string',
                        example: 'Version conflict between plugin A and plugin B',
                    ),
                    new OA\Property(
                        property: 'severity',
                        description: 'Severity level of the conflict',
                        type: 'string',
                        enum: ['error', 'warning', 'info'],
                        example: 'error',
                    ),
                    new OA\Property(
                        property: 'conflicting_plugins',
                        description: 'List of plugin names involved in the conflict',
                        type: 'array',
                        items: new OA\Items(type: 'string'),
                        example: ['plugin-a', 'plugin-b'],
                    ),
                ],
                type: 'object',
            ),
        ),
        new OA\Property(
            property: 'is_valid',
            description: 'Whether the dependency resolution was successful without critical conflicts',
            type: 'boolean',
            example: true,
        ),
    ],
    example: [
        'resolved_dependencies' => [
            [
                'name' => 'server',
                'version' => 'v5.0.2',
                'owner' => 'roadrunner-server',
                'repository' => 'server',
                'source' => 'official',
            ],
        ],
        'dependency_count' => [
            'resolved' => 1,
        ],
        'conflicts' => [],
        'is_valid' => true,
    ]
)]
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
