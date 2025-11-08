<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Discovery\Endpoint\Http\v1\Discovery;

use App\Application\HTTP\Response\ResourceCollection;
use App\Module\Velox\Plugin\DTO\Plugin;
use App\Module\Velox\Plugin\Endpoint\Http\v1\Plugin\PluginResource;

/**
 * Collection of discovered plugins with metadata
 */
final class DiscoveredPluginCollectionResource extends ResourceCollection
{
    /**
     * @param iterable<Plugin> $data
     */
    public function __construct(
        iterable $data,
        private readonly ?\App\Module\Velox\Plugin\Discovery\DTO\DiscoveryStatistics $statistics = null,
    ) {
        parent::__construct($data, PluginResource::class);
    }

    public function toArray(): array
    {
        $result = parent::toArray();

        if ($this->statistics !== null) {
            $result['meta'] = $this->statistics->toArray();
        }

        return $result;
    }
}
