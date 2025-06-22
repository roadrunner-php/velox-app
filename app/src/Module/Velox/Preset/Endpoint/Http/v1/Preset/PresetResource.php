<?php

declare(strict_types=1);

namespace App\Module\Velox\Preset\Endpoint\Http\v1\Preset;

use App\Application\HTTP\Response\JsonResource;
use App\Module\Velox\Preset\DTO\PresetDefinition;

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
