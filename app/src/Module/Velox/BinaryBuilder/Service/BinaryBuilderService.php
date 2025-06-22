<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryBuilder\Service;

use App\Module\Velox\BinaryBuilder\DTO\BuildResult;
use App\Module\Velox\BinaryBuilder\Exception\BuildException;
use App\Module\Velox\Configuration\DTO\VeloxConfig;
use App\Module\Velox\Configuration\Service\ConfigurationGeneratorService;
use App\Module\Velox\Configuration\Service\ConfigurationValidatorService;
use Spiral\Files\FilesInterface;
use Symfony\Component\Process\Process;

final readonly class BinaryBuilderService
{
    public function __construct(
        private ConfigurationGeneratorService $configGenerator,
        private ConfigurationValidatorService $configValidator,
        private VeloxBinaryRunner $binaryRunner,
        private FilesInterface $files,
        private string $tempDir = '/tmp/velox-builds',
    ) {}

    /**
     * Build RoadRunner binary from VeloxConfig
     */
    public function buildBinary(VeloxConfig $config, string $outputDirectory): BuildResult
    {
        $startTime = \microtime(true);
        $buildHash = $this->generateBuildHash($config);

        // Validate configuration first
        $validationResult = $this->configValidator->validateConfiguration($config);
        if (!$validationResult->isValid) {
            throw new BuildException(
                'Configuration validation failed: ' . \implode(', ', $validationResult->errors),
                $validationResult->errors,
            );
        }

        // Check if vx binary is available
        if (!$this->binaryRunner->isVeloxAvailable()) {
            throw new BuildException('Velox binary (vx) is not available. Please install it first.');
        }

        // Prepare build directory
        $buildDir = $this->prepareBuildDirectory($buildHash);
        $configPath = $buildDir . '/velox.toml';
        $binaryPath = $outputDirectory . '/rr';

        try {
            // Generate TOML configuration
            $tomlContent = $this->configGenerator->generateToml($config);
            $this->files->write($configPath, $tomlContent);

            // Ensure output directory exists
            if (!$this->files->isDirectory($outputDirectory)) {
                $this->files->ensureDirectory($outputDirectory);
            }

            // Build binary
            $buildResult = $this->binaryRunner->build($configPath, $outputDirectory);

            if (!$buildResult['success']) {
                throw new BuildException(
                    'Binary build failed with exit code: ' . $buildResult['exitCode'],
                    \explode("\n", $buildResult['output'] . "\n" . $buildResult['errorOutput']),
                );
            }

            // Check if binary was created successfully
            if (!$this->files->exists($binaryPath)) {
                throw new BuildException(
                    'Binary was not created at expected path: ' . $binaryPath,
                    \explode("\n", $buildResult['output']),
                );
            }

            $endTime = \microtime(true);
            $binarySize = $this->files->size($binaryPath);

            return new BuildResult(
                success: true,
                binaryPath: $binaryPath,
                buildTimeSeconds: $endTime - $startTime,
                binarySizeBytes: $binarySize,
                logs: \explode("\n", $buildResult['output']),
                errors: [],
                configPath: $configPath,
                buildHash: $buildHash,
            );
        } catch (BuildException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new BuildException(
                'Unexpected error during binary build: ' . $e->getMessage(),
                [],
                $e,
            );
        } finally {
            // Cleanup temporary files
            $this->cleanupBuildDirectory($buildDir);
        }
    }

    /**
     * Build binary from plugin selection
     *
     * @param array<string> $selectedPlugins
     */
    public function buildFromPluginSelection(array $selectedPlugins, string $outputDirectory): BuildResult
    {
        $config = $this->configGenerator->buildConfigFromSelection($selectedPlugins);

        return $this->buildBinary($config, $outputDirectory);
    }

    /**
     * Build binary and return both binary and Dockerfile
     */
    public function buildWithDockerfile(
        VeloxConfig $config,
        string $outputDirectory,
        string $baseImage = 'php:8.3-cli',
    ): array {
        $buildResult = $this->buildBinary($config, $outputDirectory);

        $dockerfile = $this->configGenerator->generateDockerfile(config: $config, baseImage: $baseImage);
        $dockerfilePath = $outputDirectory . '/Dockerfile';
        $this->files->write($dockerfilePath, $dockerfile);

        // Also save the velox.toml for reference
        $tomlContent = $this->configGenerator->generateToml($config);
        $tomlPath = $outputDirectory . '/velox.toml';
        $this->files->write($tomlPath, $tomlContent);

        return [
            'buildResult' => $buildResult,
            'dockerfilePath' => $dockerfilePath,
            'tomlPath' => $tomlPath,
        ];
    }

    /**
     * Get estimated build time based on plugin count
     */
    public function estimateBuildTime(VeloxConfig $config): int
    {
        $pluginCount = \count($config->getAllPlugins());

        // Base time + time per plugin (rough estimates)
        $baseTime = 30; // seconds
        $timePerPlugin = 5; // seconds

        return $baseTime + ($pluginCount * $timePerPlugin);
    }

    /**
     * Check build requirements
     */
    public function checkBuildRequirements(): array
    {
        return [
            'vx_available' => $this->binaryRunner->isVeloxAvailable(),
            'vx_version' => $this->binaryRunner->getVeloxVersion(),
            'temp_dir_writable' => $this->files->isDirectory($this->tempDir)
                || $this->files->ensureDirectory($this->tempDir),
            'go_available' => $this->isGoAvailable(),
        ];
    }

    private function generateBuildHash(VeloxConfig $config): string
    {
        return \hash('sha256', \json_encode($config) . \time());
    }

    private function prepareBuildDirectory(string $buildHash): string
    {
        $buildDir = $this->tempDir . '/' . $buildHash;

        if (!$this->files->ensureDirectory($buildDir)) {
            throw new BuildException("Cannot create build directory: {$buildDir}");
        }

        return $buildDir;
    }

    private function cleanupBuildDirectory(string $buildDir): void
    {
        try {
            if ($this->files->isDirectory($buildDir)) {
                $this->files->deleteDirectory($buildDir);
            }
        } catch (\Exception) {
            // Ignore cleanup errors
        }
    }

    private function isGoAvailable(): bool
    {
        $process = new Process(['go', 'version']);
        $process->setTimeout(10);

        try {
            $process->run();
            return $process->isSuccessful();
        } catch (\Exception) {
            return false;
        }
    }
}
