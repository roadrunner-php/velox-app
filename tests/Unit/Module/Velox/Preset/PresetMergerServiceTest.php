<?php

declare(strict_types=1);

namespace Tests\Unit\Module\Velox\Preset;

use App\Module\Velox\Preset\DTO\PresetDefinition;
use App\Module\Velox\Preset\Exception\PresetException;
use App\Module\Velox\Preset\Service\ConfigPresetProvider;
use App\Module\Velox\Preset\Service\PresetMergerService;
use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Expect;
use Testo\Test;

#[Test]
#[Covers(PresetMergerService::class)]
final class PresetMergerServiceTest
{
    private PresetMergerService $merger;

    public function __construct()
    {
        $this->merger = new PresetMergerService(new ConfigPresetProvider([
            new PresetDefinition('web', 'Web', 'Web server', ['server', 'http', 'gzip']),
            new PresetDefinition('queue', 'Queue', 'Queue workers', ['server', 'jobs', 'amqp'], priority: 10),
            new PresetDefinition('kafka', 'Kafka', 'Kafka driver', ['jobs', 'kafka'], priority: 5),
            new PresetDefinition('proxy', 'Proxy', 'Reverse proxy', ['http', 'proxy']),
        ]));
    }

    public function mergesPresetsByPriority(): void
    {
        $result = $this->merger->mergePresets(['web', 'queue']);

        Assert::true($result->isValid);
        Assert::same($result->mergedPresets, ['web', 'queue']);
        Assert::same($result->finalPlugins, ['server', 'jobs', 'amqp', 'http', 'gzip']);
        Assert::same($result->warnings, []);
    }

    public function returnsEmptyResultWithoutPresets(): void
    {
        $result = $this->merger->mergePresets([]);

        Assert::same($result->finalPlugins, []);
        Assert::true($result->isValid);
    }

    public function warnsAboutOverlappingFunctionality(): void
    {
        $result = $this->merger->mergePresets(['queue', 'kafka', 'web', 'proxy']);

        Assert::same($result->warnings, [
            'Multiple job-drivers selected, ensure they are compatible: amqp, kafka',
            'Multiple http-middleware selected, ensure they are compatible: gzip, proxy',
        ]);
        Assert::true($result->isValid);
    }

    public function failsOnMissingPresets(): never
    {
        Expect::exception(PresetException::class)->withMessage('Presets not found: unknown, other');

        $this->merger->mergePresets(['web', 'unknown', 'other']);
    }

    public function checksWhetherPresetsCanBeMerged(): void
    {
        Assert::true($this->merger->canMergePresets(['web', 'queue']));
        Assert::false($this->merger->canMergePresets(['web', 'unknown']));
    }

    public function recommendsPresetsCoveredBySelection(): void
    {
        Assert::same($this->merger->getRecommendedPresets(['server', 'http', 'gzip', 'jobs']), ['web']);
        Assert::same($this->merger->getRecommendedPresets(['server', 'http']), []);
    }

    public function recommendationSkipsEmptyPreset(): void
    {
        $merger = new PresetMergerService(new ConfigPresetProvider([
            new PresetDefinition('empty', 'Empty', 'No plugins', []),
        ]));

        Assert::same($merger->getRecommendedPresets(['http']), []);
    }
}
