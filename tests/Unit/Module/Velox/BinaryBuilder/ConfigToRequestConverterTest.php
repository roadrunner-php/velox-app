<?php

declare(strict_types=1);

namespace Tests\Unit\Module\Velox\BinaryBuilder;

use App\Module\Velox\BinaryBuilder\Converter\ConfigToRequestConverter;
use App\Module\Velox\BinaryBuilder\DTO\Architecture;
use App\Module\Velox\BinaryBuilder\DTO\OS;
use App\Module\Velox\BinaryBuilder\DTO\TargetPlatform;
use App\Module\Velox\Configuration\DTO\GitHubConfig;
use App\Module\Velox\Configuration\DTO\GitLabConfig;
use App\Module\Velox\Configuration\DTO\RoadRunnerConfig;
use App\Module\Velox\Configuration\DTO\VeloxConfig;
use App\Module\Velox\Plugin\DTO\PluginRepository;
use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Test;
use Tests\Unit\Fixture\PluginFactory;

#[Test]
#[Covers(ConfigToRequestConverter::class)]
final class ConfigToRequestConverterTest
{
    public function convertsConfigToBuildRequest(): void
    {
        $config = new VeloxConfig(
            roadrunner: new RoadRunnerConfig('v2025.1.2'),
            github: new GitHubConfig(plugins: [
                PluginFactory::plugin('http', ref: 'v5.2.7'),
                PluginFactory::plugin('legacy', ref: 'v1.4.0'),
                PluginFactory::plugin('branch', ref: 'master', folder: '/plugin', replace: '../local'),
            ]),
        );
        $platform = new TargetPlatform(OS::Darwin, Architecture::ARM64);

        $request = (new ConfigToRequestConverter())->convert($config, $platform, true, 'request-1');

        Assert::same($request->toArray(), [
            'request_id' => 'request-1',
            'force_rebuild' => true,
            'target_platform' => ['os' => 'darwin', 'arch' => 'arm64'],
            'rr_version' => 'v2025.1.2',
            'plugins' => [
                ['module_name' => 'github.com/roadrunner-server/http/v5', 'tag' => 'v5.2.7'],
                ['module_name' => 'github.com/roadrunner-server/legacy', 'tag' => 'v1.4.0'],
                [
                    'module_name' => 'github.com/roadrunner-server/branch/plugin',
                    'tag' => 'master',
                    'replace' => '../local',
                ],
            ],
        ]);
        Assert::same(\json_decode($request->toJson(), true), $request->toArray());
    }

    public function generatesRequestIdAndCurrentPlatformByDefault(): void
    {
        $request = (new ConfigToRequestConverter())->convert(new VeloxConfig());

        Assert::string($request->requestId)->matchesRegex('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}$/');
        Assert::false($request->forceRebuild);
        Assert::equals($request->targetPlatform, TargetPlatform::current());
        Assert::same($request->plugins, []);
    }

    public function convertsGitlabPlugin(): void
    {
        $config = new VeloxConfig(gitlab: new GitLabConfig(plugins: [
            PluginFactory::plugin('custom', ref: 'v2.0.0', repositoryType: PluginRepository::Gitlab, owner: 'acme'),
        ]));

        $request = (new ConfigToRequestConverter())->convert($config, requestId: 'request-1');

        Assert::same($request->plugins[0]['module_name'], 'gitlab.com/acme/custom/v2');
    }
}
