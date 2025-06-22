<?php

declare(strict_types=1);

namespace App\Module\Velox\Endpoint\Console;

use App\Module\Velox\ConfigurationBuilder;
use App\Module\Velox\Configuration\Exception\ValidationException;
use App\Module\Velox\Preset\Exception\PresetException;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Attribute\Option;
use Spiral\Console\Command;
use Spiral\Files\FilesInterface;

#[AsCommand(
    name: 'velox:generate-dockerfile',
    description: 'Generate Dockerfile and velox.toml from presets',
)]
final class GenerateDockerfile extends Command
{
    #[Option(shortcut: 'p', description: 'List of presets to merge')]
    private array $presets = [];

    #[Option(description: 'Base Docker image for Dockerfile')]
    private string $baseImage = 'php:8.3-cli';

    public function __invoke(
        ConfigurationBuilder $builder,
        FilesInterface $files,
        DirectoriesInterface $dirs,
    ): int {
        if (empty($this->presets)) {
            $this->error('No presets specified. Use --presets option or --list to see available presets.');
            $this->comment('Example: php app.php velox:generate-dockerfile --presets=web-server,monitoring');
            return self::FAILURE;
        }

        $outputDir = $dirs->get('runtime') . 'dockerfiles';

        try {
            return $this->generateFiles($builder, $files, $outputDir);
        } catch (PresetException $e) {
            $this->error("Preset error: {$e->getMessage()}");
            if (!empty($e->presetNames)) {
                $this->error('Invalid presets: ' . \implode(', ', $e->presetNames));
            }
            return self::FAILURE;
        } catch (ValidationException $e) {
            $this->error("Validation error: {$e->getMessage()}");
            foreach ($e->validationErrors as $validationError) {
                $this->error("  • {$validationError}");
            }
            return self::FAILURE;
        } catch (\Exception $e) {
            $this->error("Unexpected error: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    private function generateFiles(
        ConfigurationBuilder $builder,
        FilesInterface $files,
        string $outputDir,
    ): int {
        $this->info('🚀 Generating files from presets: ' . \implode(', ', $this->presets));

        // Validate and merge presets
        $mergeResult = $builder->mergePresets($this->presets);

        if (!$mergeResult->isValid) {
            $this->error('❌ Failed to merge presets:');
            foreach ($mergeResult->conflicts as $conflict) {
                $this->error("  • {$conflict}");
            }
            return self::FAILURE;
        }

        if (!empty($mergeResult->warnings)) {
            $this->newLine();
            $this->comment('⚠️  Warnings:');
            foreach ($mergeResult->warnings as $warning) {
                $this->warning("  • {$warning}");
            }
        }

        // Build configuration
        $this->info('📦 Selected plugins: ' . \implode(', ', $mergeResult->finalPlugins));

        $config = $builder->buildConfigurationFromPresets($this->presets);

        // Validate configuration
        $validationResult = $builder->validateConfiguration($config);
        if (!$validationResult->isValid) {
            $this->error('❌ Configuration validation failed:');
            foreach ($validationResult->errors as $error) {
                $this->error("  • {$error}");
            }
            return self::FAILURE;
        }

        if (!empty($validationResult->warnings)) {
            $this->newLine();
            $this->comment('⚠️  Configuration warnings:');
            foreach ($validationResult->warnings as $warning) {
                $this->warning("  • {$warning}");
            }
        }

        // Ensure output directory exists
        if (!$files->isDirectory($outputDir)) {
            $files->ensureDirectory($outputDir);
        }

        $generatedFiles = [];

        // Generate Dockerfile
        $dockerfile = $builder->generateDockerfile($config, $this->baseImage);
        $dockerfilePath = $outputDir . '/Dockerfile';
        $files->write($dockerfilePath, $dockerfile);
        $generatedFiles[] = $dockerfilePath;
        $this->info("✅ Generated Dockerfile: {$dockerfilePath}");

        // Show summary
        $this->newLine();
        $this->info('🎉 Generation completed successfully!');
        $this->info("📁 Output directory: {$outputDir}");
        $this->info('📄 Generated files:');
        foreach ($generatedFiles as $file) {
            $this->line("  • " . \basename($file));
        }

        // Show next steps
        $this->newLine();
        $this->comment('🔨 Next steps:');
        $this->comment('  • Build Docker image: docker build -t my-roadrunner .');
        $this->comment('  • Run container: docker run -p 8080:8080 my-roadrunner');

        return self::SUCCESS;
    }
}
