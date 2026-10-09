<?php

declare(strict_types=1);

namespace Tests\Unit\Module\Velox\Preset;

use App\Module\Velox\Preset\DTO\PresetDefinition;
use App\Module\Velox\Preset\Service\ConfigPresetProvider;
use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Data\DataSet;
use Testo\Test;

#[Test]
#[Covers(ConfigPresetProvider::class)]
final class ConfigPresetProviderTest
{
    private ConfigPresetProvider $provider;

    public function __construct()
    {
        $this->provider = new ConfigPresetProvider([
            new PresetDefinition('web', 'Web Server', 'HTTP stack', ['http'], tags: ['web', 'production']),
            new PresetDefinition('queue', 'Queues', 'Background jobs', ['jobs'], tags: ['workers']),
            new PresetDefinition('community', 'Community', 'Third-party plugins', ['kafka'], isOfficial: false),
        ]);
    }

    public function filtersPresets(): void
    {
        Assert::count($this->provider->getAllPresets(), 3);
        Assert::same($this->names($this->provider->getOfficialPresets()), ['web', 'queue']);
        Assert::same($this->names($this->provider->getCommunityPresets()), ['community']);
    }

    #[DataSet([['production'], ['web']], 'single tag')]
    #[DataSet([['workers', 'web'], ['web', 'queue']], 'any of tags')]
    #[DataSet([[], []], 'no tags')]
    public function filtersByTags(array $tags, array $expected): void
    {
        Assert::same($this->names($this->provider->getPresetsByTags($tags)), $expected);
    }

    #[DataSet(['QUEUE', ['queue']], 'by name')]
    #[DataSet(['server', ['web']], 'by display name')]
    #[DataSet(['third-party', ['community']], 'by description')]
    public function searchesPresets(string $query, array $expected): void
    {
        Assert::same($this->names($this->provider->searchPresets($query)), $expected);
    }

    public function findsPresetByName(): void
    {
        Assert::same($this->provider->getPresetByName('queue')?->displayName, 'Queues');
        Assert::null($this->provider->getPresetByName('unknown'));
    }

    /**
     * @param array<PresetDefinition> $presets
     * @return list<string>
     */
    private function names(array $presets): array
    {
        return \array_values(\array_map(static fn(PresetDefinition $preset): string => $preset->name, $presets));
    }
}
