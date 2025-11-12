<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryBuilder\Service;

use App\Module\Velox\BinaryBuilder\Client\VeloxClientInterface;
use App\Module\Velox\BinaryBuilder\Converter\ConfigToRequestConverter;
use App\Module\Velox\BinaryBuilder\DTO\BuildResult;
use App\Module\Velox\BinaryBuilder\DTO\TargetPlatform;
use App\Module\Velox\BinaryBuilder\Exception\BuildException;
use App\Module\Velox\Configuration\DTO\VeloxConfig;
use App\Module\Velox\Configuration\Service\ConfigurationGeneratorService;
use App\Module\Velox\Configuration\Service\ConfigurationValidatorService;
use Spiral\Files\FilesInterface;

final readonly class BinaryBuilderService
{
    public function __construct(
        private ConfigurationGeneratorService $configGenerator,
        private ConfigurationValidatorService $configValidator,
        private VeloxClientInterface $veloxClient,
        private ConfigToRequestConverter $configConverter,
        private BinaryCacheService $cacheService,
        private FilesInterface $files,
    ) {}

    /**
     * Build RoadRunner binary from VeloxConfig (supports remote and local builds)
     */
    public function buildBinary(
        VeloxConfig $config,
        string $outputDirectory,
        ?TargetPlatform $targetPlatform = null,
        bool $forceRebuild = false,
    ): BuildResult {
        // Validate configuration first
        $validationResult = $this->configValidator->validateConfiguration($config);
        if (!$validationResult->isValid) {
            throw new BuildException(
                'Configuration validation failed: ' . \implode(', ', $validationResult->errors),
                $validationResult->errors,
            );
        }

        // Use current platform if not specified
        $platform = $targetPlatform ?? TargetPlatform::current();

        // Generate cache key
        $cacheKey = $this->cacheService->generateKey($config, $platform);

        // Check cache unless force rebuild
        if (!$forceRebuild && $this->cacheService->has($cacheKey)) {
            // Ensure output directory exists
            if (!$this->files->isDirectory($outputDirectory)) {
                $this->files->ensureDirectory($outputDirectory);
            }

            $binaryPath = $outputDirectory . '/rr';
            $cachedResult = $this->cacheService->get($cacheKey, $binaryPath);

            if ($cachedResult !== null) {
                return $cachedResult;
            }
            // Cache retrieval failed, proceed with build
        }

        // Convert config to BuildRequest
        $buildRequest = $this->configConverter->convert($config, $platform, $forceRebuild);

        // Ensure output directory exists
        if (!$this->files->isDirectory($outputDirectory)) {
            $this->files->ensureDirectory($outputDirectory);
        }

        $binaryPath = $outputDirectory . '/rr';

        // Build via Velox server
        $buildResult = $this->veloxClient->build($buildRequest, $binaryPath);

        // Store in cache if build succeeded
        if ($buildResult->isSuccess()) {
            $this->cacheService->put($cacheKey, $binaryPath, $config, $platform);
        }

        return $buildResult;
    }

    /**
     * Build binary from plugin selection (supports remote and local)
     *
     * @param array<string> $selectedPlugins
     */
    public function buildFromPluginSelection(
        array $selectedPlugins,
        string $outputDirectory,
        ?TargetPlatform $targetPlatform = null,
        bool $forceRebuild = false,
    ): BuildResult {
        $config = $this->configGenerator->buildConfigFromSelection($selectedPlugins);

        return $this->buildBinary($config, $outputDirectory, $targetPlatform, $forceRebuild);
    }
}
