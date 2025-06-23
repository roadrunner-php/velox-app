<?php

declare(strict_types=1);

namespace App\Module\Velox\Preset\Endpoint\Http\v1\Preset;

use App\Application\HTTP\Response\JsonResource;
use App\Module\Velox\Preset\DTO\PresetDefinition;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: PresetResource::class,
    properties: [
        new OA\Property(
            property: 'name',
            description: 'The unique name of the preset',
            type: 'string',
        ),
        new OA\Property(
            property: 'display_name',
            description: 'The display name of the preset',
            type: 'string',
        ),
        new OA\Property(
            property: 'description',
            description: 'A brief description of the preset',
            type: 'string',
        ),
        new OA\Property(
            property: 'plugins',
            description: 'List of plugin names included in the preset',
            type: 'array',
            items: new OA\Items(type: 'string'),
        ),
        new OA\Property(
            property: 'plugin_count',
            description: 'Number of plugins included in the preset',
            type: 'integer',
        ),
        new OA\Property(
            property: 'tags',
            description: 'Tags associated with the preset',
            type: 'array',
            items: new OA\Items(type: 'string'),
        ),
        new OA\Property(
            property: 'is_official',
            description: 'Indicates if the preset is official',
            type: 'boolean',
        ),
        new OA\Property(
            property: 'priority',
            description: 'Priority of the preset, lower values indicate higher priority',
            type: 'integer',
        ),
    ],
)]
/**
 * @extends JsonResource<PresetDefinition>
 */
final class PresetResource extends JsonResource
{
    #[\Override]
    protected function mapData(): array|\JsonSerializable
    {
        return [
            'name' => $this->data->name,
            'display_name' => $this->data->displayName,
            'description' => $this->data->description,
            'plugins' => $this->data->pluginNames,
            'plugin_count' => \count($this->data->pluginNames),
            'tags' => $this->data->tags,
            'is_official' => $this->data->isOfficial,
            'priority' => $this->data->priority,
        ];
    }
}
