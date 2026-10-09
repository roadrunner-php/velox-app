<?php

declare(strict_types=1);

namespace Tests\Unit\Module\Velox\Configuration;

use App\Module\Velox\Configuration\DTO\DebugConfig;
use App\Module\Velox\Configuration\DTO\GitHubConfig;
use App\Module\Velox\Configuration\DTO\GitHubToken;
use App\Module\Velox\Configuration\DTO\GitLabConfig;
use App\Module\Velox\Configuration\DTO\GitLabToken;
use App\Module\Velox\Configuration\DTO\VeloxConfig;
use Tests\Unit\Fixture\PluginFactory;
use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Test;

#[Test]
#[Covers(VeloxConfig::class)]
final class VeloxConfigTest
{
    public function findsPluginsAcrossRepositories(): void
    {
        $config = new VeloxConfig(
            github: new GitHubConfig(plugins: [PluginFactory::plugin('http')]),
            gitlab: new GitLabConfig(plugins: [PluginFactory::plugin('custom')]),
        );

        Assert::count($config->getAllPlugins(), 2);
        Assert::true($config->hasPlugin('custom'));
        Assert::false($config->hasPlugin('grpc'));
        Assert::same($config->getPlugin('http')?->name, 'http');
        Assert::null($config->getPlugin('grpc'));
    }

    public function collectsDependenciesRecursively(): void
    {
        $config = new VeloxConfig(github: new GitHubConfig(plugins: [
            PluginFactory::plugin('http', ['server', 'logger']),
            PluginFactory::plugin('server', ['logger', 'rpc']),
            PluginFactory::plugin('logger'),
        ]));

        Assert::array($config->getPluginDependencies('http'))->sameElementsAs(['server', 'logger', 'rpc']);
        Assert::same($config->getPluginDependencies('unknown'), []);
    }

    public function serializesOnlyConfiguredSections(): void
    {
        Assert::array((new VeloxConfig())->jsonSerialize())->hasKeys('roadrunner', 'log')->hasCount(2);

        $data = (new VeloxConfig(
            debug: new DebugConfig(true),
            github: new GitHubConfig(token: new GitHubToken('token')),
            gitlab: new GitLabConfig(token: new GitLabToken('token')),
        ))->jsonSerialize();

        Assert::array($data)->hasKeys('roadrunner', 'log', 'debug', 'github', 'gitlab');
    }
}
