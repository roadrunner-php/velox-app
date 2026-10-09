<?php

declare(strict_types=1);

namespace Tests\Unit\Module\Velox\Plugin;

use App\Module\Velox\Plugin\DTO\Plugin;
use App\Module\Velox\Plugin\DTO\PluginCategory;
use App\Module\Velox\Plugin\DTO\PluginSource;
use App\Module\Velox\Plugin\Service\ConfigPluginProvider;
use Tests\Unit\Fixture\PluginFactory;
use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Data\DataSet;
use Testo\Test;

#[Test]
#[Covers(ConfigPluginProvider::class)]
final class ConfigPluginProviderTest
{
    private ConfigPluginProvider $provider;

    public function __construct()
    {
        $this->provider = new ConfigPluginProvider([
            PluginFactory::plugin('http', category: PluginCategory::Http, description: 'HTTP server'),
            PluginFactory::plugin('kv', category: PluginCategory::Kv, description: 'Key-value storage'),
            PluginFactory::plugin('kafka', category: PluginCategory::Jobs, source: PluginSource::Community),
            PluginFactory::plugin('custom'),
        ]);
    }

    public function filtersPlugins(): void
    {
        Assert::count($this->provider->getAllPlugins(), 4);
        Assert::same($this->names($this->provider->getPluginsByCategory(PluginCategory::Kv)), ['kv']);
        Assert::same($this->names($this->provider->getOfficialPlugins()), ['http', 'kv', 'custom']);
        Assert::same($this->names($this->provider->getCommunityPlugins()), ['kafka']);
    }

    #[DataSet(['HTTP', ['http']], 'by name, case-insensitive')]
    #[DataSet(['storage', ['kv']], 'by description')]
    #[DataSet(['jobs', ['kafka']], 'by category')]
    #[DataSet(['nothing', []], 'no match')]
    public function searchesPlugins(string $query, array $expected): void
    {
        Assert::same($this->names($this->provider->searchPlugins($query)), $expected);
    }

    public function findsPluginByName(): void
    {
        Assert::same($this->provider->getPluginByName('kafka')?->source, PluginSource::Community);
        Assert::null($this->provider->getPluginByName('grpc'));
    }

    /**
     * @param array<Plugin> $plugins
     * @return list<string>
     */
    private function names(array $plugins): array
    {
        return \array_values(\array_map(static fn(Plugin $plugin): string => $plugin->name, $plugins));
    }
}
