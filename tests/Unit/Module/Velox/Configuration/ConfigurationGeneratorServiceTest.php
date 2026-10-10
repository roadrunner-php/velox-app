<?php

declare(strict_types=1);

namespace Tests\Unit\Module\Velox\Configuration;

use App\Module\Velox\Configuration\DTO\GitHubConfig;
use App\Module\Velox\Configuration\DTO\VeloxConfig;
use App\Module\Velox\Configuration\Service\ConfigurationGeneratorService;
use App\Module\Velox\Plugin\DTO\Plugin;
use App\Module\Velox\Plugin\DTO\PluginRepository;
use App\Module\Velox\Plugin\DTO\PluginSource;
use App\Module\Velox\Plugin\Service\ConfigPluginProvider;
use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Test;
use Tests\Unit\Fixture\PluginFactory;

#[Test]
#[Covers(ConfigurationGeneratorService::class)]
final class ConfigurationGeneratorServiceTest
{
    public function buildsConfigFromSelectedPlugins(): void
    {
        $generator = new ConfigurationGeneratorService(
            pluginProvider: $this->provider(),
            roadRunnerVersion: 'v2025.1.2',
            githubToken: 'default-token',
            gitlabToken: 'gitlab-token',
            gitlabEndpoint: 'https://gitlab.example.com',
        );

        $config = $generator->buildConfigFromSelection(['http', 'gitlab-plugin', 'unknown']);

        Assert::same($config->roadrunner->ref, 'v2025.1.2');
        Assert::same($this->names($config->github->plugins), ['http']);
        Assert::same($this->names($config->gitlab->plugins), ['gitlab-plugin']);
        Assert::same($config->github->token?->token, 'default-token');
        Assert::same($config->gitlab->token?->token, 'gitlab-token');
        Assert::same($config->gitlab->endpoint?->endpoint, 'https://gitlab.example.com');
    }

    public function explicitGithubTokenOverridesDefault(): void
    {
        $generator = new ConfigurationGeneratorService($this->provider(), githubToken: 'default-token');

        $config = $generator->buildConfigFromSelection(['http'], 'request-token');

        Assert::same($config->github->token?->token, 'request-token');
    }

    public function omitsTokensWhenNotConfigured(): void
    {
        $generator = new ConfigurationGeneratorService($this->provider());

        $config = $generator->buildConfigFromSelection(['http']);

        Assert::null($config->github->token);
        Assert::null($config->gitlab->token);
        Assert::null($config->gitlab->endpoint);
    }

    public function emptySelectionTakesOfficialPlugins(): void
    {
        $generator = new ConfigurationGeneratorService($this->provider());

        $config = $generator->buildConfigFromSelection([]);

        Assert::same($this->names($config->getAllPlugins()), ['http', 'gitlab-plugin']);
    }

    public function generatesToml(): void
    {
        $generator = new ConfigurationGeneratorService($this->provider());
        $config = new VeloxConfig(github: new GitHubConfig(plugins: [PluginFactory::plugin('http', folder: 'src')]));

        $toml = $generator->generateToml($config);

        Assert::string($toml)
            ->contains("[roadrunner]\nref = \"master\"")
            ->contains("[log]\nlevel = \"info\"\nmode = \"production\"")
            ->contains("[github.plugins.http]\nref = \"v5.0.2\"\nowner = \"roadrunner-server\"\nrepository = \"http\"\nfolder = \"src\"")
            ->notContains('[debug]')
            ->notContains('[gitlab]');
    }

    public function generatesDockerfileWritingTomlLineByLine(): void
    {
        $generator = new ConfigurationGeneratorService($this->provider(), veloxVersion: 'v2025.1.1');
        $config = new VeloxConfig(github: new GitHubConfig(plugins: [
            PluginFactory::plugin('http', description: "it's ignored", replace: "/it's/here"),
        ]));

        $dockerfile = $generator->generateDockerfile($config, 'php:8.4-alpine');

        Assert::string($dockerfile)
            ->contains('ghcr.io/roadrunner-server/velox:v2025.1.1 as velox')
            ->contains("RUN echo '[roadrunner]' > velox.toml\nRUN echo 'ref = \"master\"' >> velox.toml")
            ->contains("RUN echo 'replace = \"/it'\\''s/here\"' >> velox.toml")
            ->contains('FROM --platform=${TARGETPLATFORM:-linux/amd64} php:8.4-alpine')
            ->endsWith('ENTRYPOINT ["/usr/bin/rr", "serve"]');
    }

    private function provider(): ConfigPluginProvider
    {
        return new ConfigPluginProvider([
            PluginFactory::plugin('http'),
            PluginFactory::plugin('community', source: PluginSource::Community),
            PluginFactory::plugin('gitlab-plugin', repositoryType: PluginRepository::Gitlab),
        ]);
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
