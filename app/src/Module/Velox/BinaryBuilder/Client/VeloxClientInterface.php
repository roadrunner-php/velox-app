<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryBuilder\Client;

use App\Module\Velox\BinaryBuilder\DTO\BuildRequest;
use App\Module\Velox\BinaryBuilder\DTO\BuildResult;

interface VeloxClientInterface
{
    /**
     * Build RoadRunner binary via Velox server
     *
     * @param BuildRequest $request Build configuration
     * @param string $outputPath Path where to save the binary
     * @return BuildResult Build result with metrics
     *
     * @throws \App\Module\Velox\BinaryBuilder\Exception\VeloxServerConnectionException
     * @throws \App\Module\Velox\BinaryBuilder\Exception\VeloxBuildFailedException
     * @throws \App\Module\Velox\BinaryBuilder\Exception\VeloxTimeoutException
     */
    public function build(BuildRequest $request, string $outputPath): BuildResult;

    /**
     * Check if Velox server is available
     */
    public function isAvailable(): bool;

    /**
     * Get Velox server version
     */
    public function getServerVersion(): ?string;
}
