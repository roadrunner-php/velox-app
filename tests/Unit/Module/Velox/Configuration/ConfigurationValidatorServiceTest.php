<?php

declare(strict_types=1);

namespace Tests\Unit\Module\Velox\Configuration;

use App\Module\Velox\Configuration\DTO\GitHubConfig;
use App\Module\Velox\Configuration\DTO\GitHubToken;
use App\Module\Velox\Configuration\DTO\GitLabConfig;
use App\Module\Velox\Configuration\DTO\GitLabEndpoint;
use App\Module\Velox\Configuration\DTO\GitLabToken;
use App\Module\Velox\Configuration\DTO\RoadRunnerConfig;
use App\Module\Velox\Configuration\DTO\VeloxConfig;
use App\Module\Velox\Configuration\Service\ConfigurationValidatorService;
use App\Module\Velox\Dependency\Service\DependencyResolverService;
use App\Module\Velox\Plugin\DTO\Plugin;
use App\Module\Velox\Plugin\Service\ConfigPluginProvider;
use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Test;
use Tests\Unit\Fixture\PluginFactory;

#[Test]
#[Covers(ConfigurationValidatorService::class)]
final class ConfigurationValidatorServiceTest
{
    public function acceptsCompleteConfiguration(): void
    {
        $plugins = [
            PluginFactory::plugin('server'),
            PluginFactory::plugin('logger'),
            PluginFactory::plugin('http', ['server', 'logger']),
        ];

        $result = $this->validator($plugins)->validateConfiguration(new VeloxConfig(
            roadrunner: new RoadRunnerConfig('v2025.1.2'),
            github: new GitHubConfig(new GitHubToken('token'), $plugins),
        ));

        Assert::true($result->isValid);
        Assert::same($result->errors, []);
        Assert::same($result->warnings, []);
    }

    public function requiresServerPlugin(): void
    {
        $plugins = [PluginFactory::plugin('logger')];

        $result = $this->validator($plugins)->validateConfiguration(new VeloxConfig(
            roadrunner: new RoadRunnerConfig('v2025.1.2'),
            github: new GitHubConfig(new GitHubToken('token'), $plugins),
        ));

        Assert::false($result->isValid);
        Assert::same($result->errors, ['Server plugin is required but not configured.']);
    }

    public function reportsDependencyConflictsAsErrors(): void
    {
        $plugins = [PluginFactory::plugin('server', ['missing'])];

        $result = $this->validator($plugins)->validateConfiguration(new VeloxConfig(
            roadrunner: new RoadRunnerConfig('v2025.1.2'),
            github: new GitHubConfig(new GitHubToken('token'), $plugins),
        ));

        Assert::false($result->isValid);
        Assert::same($result->errors, ["Plugin 'missing' not found"]);
    }

    public function warnsAboutRiskyConfiguration(): void
    {
        $plugins = [
            PluginFactory::plugin('server'),
            PluginFactory::plugin('http', ref: 'v4.1.0'),
        ];

        $result = $this->validator($plugins)->validateConfiguration(new VeloxConfig(
            github: new GitHubConfig(plugins: $plugins),
            gitlab: new GitLabConfig(plugins: [PluginFactory::plugin('custom')]),
        ));

        Assert::true($result->isValid);
        Assert::array($result->warnings)->sameElementsAs([
            'GitHub token is not set but GitHub plugins are configured. This may cause rate limiting issues.',
            'GitLab token is not set but GitLab plugins are configured.',
            'GitLab endpoint is not set, using default: https://gitlab.com',
            'Using master branch for RoadRunner is not recommended for production use.',
            'Plugin http: Version v4.x is deprecated, please use v5.x for compatibility',
            'HTTP plugin is configured but logger is missing. Logging is recommended for HTTP servers.',
        ]);
    }

    public function acceptsConfiguredGitlab(): void
    {
        $plugins = [PluginFactory::plugin('server')];

        $result = $this->validator($plugins)->validateConfiguration(new VeloxConfig(
            roadrunner: new RoadRunnerConfig('v2025.1.2'),
            gitlab: new GitLabConfig(new GitLabToken('token'), new GitLabEndpoint(), $plugins),
        ));

        Assert::true($result->isValid);
        Assert::same($result->warnings, []);
    }

    /**
     * @param array<Plugin> $plugins
     */
    private function validator(array $plugins): ConfigurationValidatorService
    {
        return new ConfigurationValidatorService(
            new DependencyResolverService(new ConfigPluginProvider($plugins)),
        );
    }
}
