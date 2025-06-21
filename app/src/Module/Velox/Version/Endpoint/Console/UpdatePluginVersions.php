<?php

declare(strict_types=1);

namespace App\Module\Velox\Version\Endpoint\Console;

use App\Module\Velox\Version\Service\PluginVersionManager;
use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Command;

#[AsCommand(
    name: 'plugin:update-versions',
    description: 'Update plugin versions from the configuration file',
)]
final class UpdatePluginVersions extends Command
{
    public function __invoke(PluginVersionManager $manager): int
    {
        $updates = $manager->checkForUpdates();

        $envUpdates = $manager->updatePluginVersions($updates);

        foreach ($updates as $update) {
            $this->output->writeln(
                \sprintf(
                    '<info>Plugin %s updated from %s to %s</info>',
                    $update->pluginName,
                    $update->currentVersion,
                    $update->latestVersion,
                ),
            );
        }

        $this->output->writeln('<info>Plugin versions updated successfully.</info>');

        $envs = [];
        foreach ($envUpdates as $key => $version) {
            $envs[] = \sprintf('%s=%s', $key, $version);
        }

        if (!empty($envs)) {
            $this->output->writeln('<info>Updated environment variables:</info>');
            $this->output->writeln(\implode("\n", $envs));
        } else {
            $this->output->writeln('<info>No environment variables were updated.</info>');
        }

        return self::SUCCESS;
    }
}
