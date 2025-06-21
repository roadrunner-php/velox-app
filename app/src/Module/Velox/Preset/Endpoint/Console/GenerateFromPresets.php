<?php

declare(strict_types=1);

namespace App\Module\Velox\Preset\Endpoint\Console;

use App\Module\Velox\ConfigurationBuilder;
use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Attribute\Option;
use Spiral\Console\Command;

#[AsCommand(
    name: 'velox:generate-from-presets',
    description: 'Generate Velox configuration from presets',
)]
final class GenerateFromPresets extends Command
{
    #[Option(shortcut: 'p', description: 'List of presets to merge')]
    private array $presets = [];

    public function __invoke(ConfigurationBuilder $builder): int
    {
        if (empty($this->presets)) {
            $this->error('No presets specified. Use --presets option or --list to see available presets.');
            return self::FAILURE;
        }

        return $this->generateConfiguration($builder);
    }

    private function generateConfiguration(ConfigurationBuilder $builder): int
    {
        try {
            $mergeResult = $builder->mergePresets($this->presets);

            if (!$mergeResult->isValid) {
                $this->error('Failed to merge presets:');
                foreach ($mergeResult->conflicts as $conflict) {
                    $this->error("  • {$conflict}");
                }
                return self::FAILURE;
            }

            if (!empty($mergeResult->warnings)) {
                foreach ($mergeResult->warnings as $warning) {
                    $this->warning("Warning: {$warning}");
                }
            }

            $config = $builder->buildConfigurationFromPresets($this->presets);
            $toml = $builder->generateToml($config);

            $this->info("Generated configuration from presets: " . \implode(', ', $this->presets));
            $this->info("Final plugins: " . \implode(', ', $mergeResult->finalPlugins));
            $this->newLine();

            echo $toml;

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error generating configuration: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
