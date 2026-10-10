<?php

declare(strict_types=1);

namespace Tests\Unit\Module\Velox\Preset;

use App\Module\Velox\Plugin\Service\ConfigPluginProvider;
use App\Module\Velox\Preset\DTO\PresetDefinition;
use App\Module\Velox\Preset\Service\ConfigPresetProvider;
use App\Module\Velox\Preset\Service\PresetValidatorService;
use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Test;
use Tests\Unit\Fixture\PluginFactory;

#[Test]
#[Covers(PresetValidatorService::class)]
final class PresetValidatorServiceTest
{
    private PresetValidatorService $validator;

    public function __construct()
    {
        $this->validator = new PresetValidatorService(
            new ConfigPresetProvider([
                new PresetDefinition('web', 'Web', 'Web server', ['server', 'http']),
                new PresetDefinition('broken', 'Broken', 'Unknown plugins', ['server', 'missing']),
            ]),
            new ConfigPluginProvider([
                PluginFactory::plugin('server'),
                PluginFactory::plugin('http'),
            ]),
        );
    }

    public function acceptsAvailablePresets(): void
    {
        $result = $this->validator->validatePresets(['web']);

        Assert::true($result->isValid);
        Assert::same($result->errors, []);
    }

    public function reportsUnknownPresets(): void
    {
        $result = $this->validator->validatePresets(['web', 'unknown']);

        Assert::false($result->isValid);
        Assert::same($result->errors, ["Preset 'unknown' not found"]);
    }

    public function reportsUnavailablePlugins(): void
    {
        $result = $this->validator->validatePresets(['web', 'broken']);

        Assert::false($result->isValid);
        Assert::same($result->errors, ["Plugin 'missing' required by presets but not available"]);
    }
}
