<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryBuilder\Endpoint\Console;

use App\Module\Velox\BinaryBuilder\DTO\TargetPlatform;
use App\Module\Velox\BinaryBuilder\Exception\BuildException;
use App\Module\Velox\BinaryBuilder\Service\BinaryBuilderService;
use App\Module\Velox\ConfigurationBuilder;
use App\Module\Velox\Preset\Service\PresetMergerService;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Attribute\Option;
use Spiral\Console\Command;

#[AsCommand(
    name: 'velox:build-binary',
    description: 'Build RoadRunner binary from configuration',
)]
final class BuildBinary extends Command
{
    #[Option(shortcut: 'p', description: 'List of presets to merge')]
    private array $presets = [];

    #[Option(description: 'Base Docker image for Dockerfile')]
    private string $baseImage = 'php:8.3-cli';

    #[Option(description: 'Target OS (linux, darwin, windows)')]
    private ?string $targetOs = 'linux';

    #[Option(description: 'Target architecture (amd64, arm64, 386, arm)')]
    private ?string $targetArch = 'amd64';

    #[Option(description: 'Force rebuild (bypass Velox server cache)')]
    private bool $forceRebuild = false;

    private string $outputDir;

    public function __invoke(
        BinaryBuilderService $binaryBuilder,
        ConfigurationBuilder $configBuilder,
        PresetMergerService $presetMerger,
        DirectoriesInterface $dirs,
    ): int {
        $this->outputDir = $dirs->get('runtime') . 'rr-builds';

        try {
            $mergeResult = $presetMerger->mergePresets($this->presets);

            if (!$mergeResult->isValid) {
                $this->error('Preset merge failed:');
                foreach ($mergeResult->conflicts as $conflict) {
                    $this->error("  • {$conflict}");
                }

                return self::FAILURE;
            }

            return $this->buildFromPresets($binaryBuilder, $configBuilder, $mergeResult->finalPlugins);
        } catch (BuildException $e) {
            $this->error("Build failed: {$e->getMessage()}");

            if (!empty($e->buildLogs)) {
                $this->newLine();
                $this->error('Build logs:');
                foreach ($e->buildLogs as $log) {
                    $this->line("  {$log}");
                }
            }

            return self::FAILURE;
        } catch (\Exception $e) {
            $this->error("Unexpected error: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    private function buildFromPresets(
        BinaryBuilderService $binaryBuilder,
        ConfigurationBuilder $configBuilder,
        array $selectedPlugins,
    ): int {
        $this->info('Building RoadRunner binary from selected plugins...');
        $this->info('Selected plugins: ' . \implode(', ', $selectedPlugins));

        // Parse target platform if specified
        $targetPlatform = null;
        if ($this->targetOs !== null || $this->targetArch !== null) {
            try {
                $currentPlatform = TargetPlatform::current();
                $targetPlatform = TargetPlatform::fromStrings(
                    os: $this->targetOs ?? $currentPlatform->os->value,
                    arch: $this->targetArch ?? $currentPlatform->arch->value,
                );
                $this->info("Target platform: {$targetPlatform->toString()}");
            } catch (\ValueError $e) {
                $this->error("Invalid platform specification: {$e->getMessage()}");
                $this->error('Valid OS: linux, darwin, windows, freebsd');
                $this->error('Valid architectures: amd64, arm64, 386, arm');
                return self::FAILURE;
            }
        }

        // Resolve dependencies
        $dependencyResult = $configBuilder->resolveDependencies($selectedPlugins);

        if (!$dependencyResult->isValid) {
            $this->error('Dependency resolution failed:');
            foreach ($dependencyResult->conflicts as $conflict) {
                $this->error("  • {$conflict->message}");
            }
            return self::FAILURE;
        }

        // Add required dependencies
        $allPlugins = \array_merge(
            $selectedPlugins,
            \array_map(static fn($plugin) => $plugin->name, $dependencyResult->requiredPlugins),
        );
        $allPlugins = \array_unique($allPlugins);

        $this->info('Final plugin list (including dependencies): ' . \implode(', ', $allPlugins));

        return $this->buildBinaryOnly($binaryBuilder, $allPlugins, $targetPlatform);
    }

    private function buildBinaryOnly(
        BinaryBuilderService $binaryBuilder,
        array $plugins,
        ?TargetPlatform $targetPlatform = null,
    ): int {
        $buildResult = $binaryBuilder->buildFromPluginSelection(
            $plugins,
            $this->outputDir,
            $targetPlatform,
            $this->forceRebuild,
        );

        if ($buildResult->isSuccess()) {
            $cacheStatus = $buildResult->fromCache ? '📦 (from cache)' : '🔨 (freshly built)';
            $this->info("✅ Binary built successfully! {$cacheStatus}");
            $this->info("📁 Binary path: {$buildResult->binaryPath}");

            if (!$buildResult->fromCache) {
                $this->info("⏱️  Build time: {$buildResult->getBuildTime()}");
            }

            $this->info("📦 Binary size: {$buildResult->getBinarySize()}");

            if ($buildResult->cacheKey !== null) {
                $this->comment("🔑 Cache key: {$buildResult->cacheKey}");
            }

            if (!empty($buildResult->logs)) {
                $this->newLine();
                $this->comment('Build logs:');
                foreach (\array_slice($buildResult->logs, -10) as $log) {
                    if (\trim($log)) {
                        $this->line("  {$log}");
                    }
                }
            }

            return self::SUCCESS;
        }

        $this->error('❌ Binary build failed');
        return self::FAILURE;
    }
}
