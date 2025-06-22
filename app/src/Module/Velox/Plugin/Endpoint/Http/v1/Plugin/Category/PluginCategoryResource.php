<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Endpoint\Http\v1\Plugin\Category;

use App\Application\HTTP\Response\JsonResource;

/**
 * @extends JsonResource<\App\Module\Velox\Plugin\DTO\PluginCategory>
 */
final class PluginCategoryResource extends JsonResource
{
    #[\Override]
    protected function mapData(): array|\JsonSerializable
    {
        return [
            'value' => $this->data->value,
            'label' => $this->data->name,
        ];
    }
}
