<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Discovery\Endpoint\Console;

use App\Module\Velox\Plugin\Discovery\Service\GitHubDiscoveryService;
use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Attribute\Option;
use Spiral\Console\Command;

/**
 * CLI command to discover community plugins
 */
#[AsCommand(
    name: 'velox:discover-plugins',
    description: 'Discover community plugins from roadrunner-plugins organization',
)]
final class DiscoverPluginsCommand extends Command
{
    #[Option(name: 'force', shortcut: 'f', description: 'Force re-scan even if cache is valid')]
    private bool $force = false;

    #[Option(name: 'clear-cache', shortcut: 'c', description: 'Clear existing cache before scan')]
    private bool $clearCache = false;

    public function __invoke(GitHubDiscoveryService $discoveryService): int
    {
        $this->info('Discovering community plugins from roadrunner-plugins...');
        $this->newLine();

        if ($this->clearCache) {
            $this->comment('Clearing existing cache...');
        }

        try {
            $startTime = \microtime(true);

            $result = $discoveryService->discover(force: $this->force);

            $duration = \microtime(true) - $startTime;

            $this->newLine();
            $this->info('✓ Discovery completed!');
            $this->newLine();

            // Display statistics
            $stats = $result->statistics;

            $this->table(
                ['Metric', 'Value'],
                [
                    ['Repositories Scanned', $stats->repositoriesScanned],
                    ['Plugins Registered', $this->formatSuccess($stats->pluginsRegistered)],
                    ['Plugins Failed', $this->formatError($stats->pluginsFailed)],
                    ['Duration', \number_format($duration, 2) . 's'],
                ],
            );

            // Show registered plugins
            if (!empty($result->plugins)) {
                $this->newLine();
                $this->comment('Registered Plugins:');
                foreach ($result->plugins as $plugin) {
                    $this->writeln("  - {$plugin->name} ({$plugin->ref}) [{$plugin->category->value}]");
                }
            }

            // Show failed repositories
            if (!empty($stats->failedRepositories)) {
                $this->newLine();
                $this->error('Failed Repositories:');
                foreach ($stats->failedRepositories as $repo => $error) {
                    $this->writeln("  <fg=red>✗</> {$repo}: {$error}");
                }
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->newLine();
            $this->error('Discovery failed: ' . $e->getMessage());

            if ($this->isVerbose()) {
                $this->writeln($e->getTraceAsString());
            }

            return self::FAILURE;
        }
    }

    private function formatSuccess(int $value): string
    {
        return "<fg=green>{$value}</>";
    }

    private function formatError(int $value): string
    {
        if ($value === 0) {
            return (string) $value;
        }

        return "<fg=red>{$value}</>";
    }
}
