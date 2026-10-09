<?php

declare(strict_types=1);

namespace Tests\Unit\Module\Velox\Plugin;

use App\Module\Velox\Plugin\DTO\Plugin;
use App\Module\Velox\Plugin\DTO\PluginCategory;
use App\Module\Velox\Plugin\DTO\PluginSource;
use App\Module\Velox\Plugin\Service\CompositePluginProvider;
use App\Module\Velox\Plugin\Service\ConfigPluginProvider;
use Tests\Unit\Fixture\PluginFactory;
use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Test;

#[Test]
#[Covers(CompositePluginProvider::class)]
final class CompositePluginProviderTest
{
    private CompositePluginProvider $provider;

    public function __construct()
    {
        $this->provider = new CompositePluginProvider([
            new ConfigPluginProvider([
                PluginFactory::plugin('http', ref: 'v5.0.0', category: PluginCategory::Http),
                PluginFactory::plugin('logger', category: PluginCategory::Logging),
            ]),
            new ConfigPluginProvider([
                PluginFactory::plugin('http', ref: 'v5.1.0', category: PluginCategory::Http),
                PluginFactory::plugin('kafka', category: PluginCategory::Jobs, source: PluginSource::Community),
            ]),
        ]);
    }

    public function mergesPluginsWithoutDuplicates(): void
    {
        Assert::same($this->names($this->provider->getAllPlugins()), ['http', 'logger', 'kafka']);
        Assert::same($this->names($this->provider->getPluginsByCategory(PluginCategory::Http)), ['http']);
        Assert::same($this->names($this->provider->getOfficialPlugins()), ['http', 'logger']);
        Assert::same($this->names($this->provider->getCommunityPlugins()), ['kafka']);
        Assert::same($this->names($this->provider->searchPlugins('k')), ['kafka']);
    }

    public function laterProviderWinsInLists(): void
    {
        $http = $this->provider->getAllPlugins()[0];

        Assert::same($http->ref, 'v5.1.0');
    }

    public function firstProviderWinsInLookup(): void
    {
        Assert::same($this->provider->getPluginByName('http')?->ref, 'v5.0.0');
        Assert::same($this->provider->getPluginByName('kafka')?->name, 'kafka');
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
