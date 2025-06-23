<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Endpoint\Http\v1\Plugin\Category;

use App\Application\HTTP\Response\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: PluginCategoryResource::class,
    description: 'Plugin category information',
    properties: [
        new OA\Property(
            property: 'value',
            description: 'Machine-readable category identifier used in API requests',
            type: 'string',
            enum: [
                'core',
                'logging',
                'http',
                'jobs',
                'kv',
                'metrics',
                'grpc',
                'monitoring',
                'network',
                'broadcasting',
                'workflow',
                'observability',
            ],
            example: 'http',
        ),
        new OA\Property(
            property: 'label',
            description: 'Human-readable category name for display purposes',
            type: 'string',
            example: 'HTTP',
        ),
    ],
    example: [
        'value' => 'http',
        'label' => 'HTTP',
    ]
)]
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
