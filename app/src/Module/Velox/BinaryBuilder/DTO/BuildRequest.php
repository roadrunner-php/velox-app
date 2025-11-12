<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryBuilder\DTO;

final readonly class BuildRequest
{
    /**
     * @param string $requestId Unique request identifier
     * @param bool $forceRebuild Bypass cache and force rebuild
     * @param TargetPlatform $targetPlatform Target build platform
     * @param string $rrVersion RoadRunner version (e.g., v2025.1.2)
     * @param array<array{module_name: string, tag: string, replace?: string}> $plugins Plugin specifications
     */
    public function __construct(
        public string $requestId,
        public bool $forceRebuild,
        public TargetPlatform $targetPlatform,
        public string $rrVersion,
        public array $plugins,
    ) {}

    public function toArray(): array
    {
        return [
            'request_id' => $this->requestId,
            'force_rebuild' => $this->forceRebuild,
            'target_platform' => $this->targetPlatform->toArray(),
            'rr_version' => $this->rrVersion,
            'plugins' => $this->plugins,
        ];
    }

    public function toJson(): string
    {
        return \json_encode($this->toArray(), \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT);
    }
}
