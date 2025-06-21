<?php

declare(strict_types=1);

namespace App\Module\Velox\Configuration\Endpoint\Console;

use App\Module\Velox\ConfigurationBuilder;
use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Attribute\Option;
use Spiral\Console\Command;

#[AsCommand(
    name: 'velox:generate-config',
)]
final class GenerateConfig extends Command
{
    #[Option(shortcut: 'p', description: 'List of plugins to update')]
    private array $plugins = [];

    public function __invoke(ConfigurationBuilder $builder): int
    {
        $config = $builder->buildConfiguration($this->plugins);

        echo $builder->generateToml($config);

        return self::SUCCESS;
    }
}
