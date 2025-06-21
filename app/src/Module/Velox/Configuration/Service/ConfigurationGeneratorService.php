<?php

declare(strict_types=1);

namespace App\Module\Velox\Configuration\Service;

use App\Module\Velox\Configuration\DTO\GitHubConfig;
use App\Module\Velox\Configuration\DTO\GitHubToken;
use App\Module\Velox\Configuration\DTO\GitLabConfig;
use App\Module\Velox\Configuration\DTO\GitLabEndpoint;
use App\Module\Velox\Configuration\DTO\GitLabToken;
use App\Module\Velox\Configuration\DTO\VeloxConfig;
use App\Module\Velox\Plugin\DTO\PluginRepository;
use App\Module\Velox\Plugin\Service\PluginProviderInterface;
use Devium\Toml\Toml;

final readonly class ConfigurationGeneratorService
{
    public function generateVeloxToml(VeloxConfig $config): string
    {
        return Toml::encode(\json_decode(\json_encode($config), true));
    }

    public function generateDockerfile(VeloxConfig $config, string $baseImage = 'php:8.3-cli'): string
    {
        $dockerfile = [];

        $dockerfile[] = '# Multi-stage build for RoadRunner with Velox';
        $dockerfile[] = 'FROM ghcr.io/roadrunner-server/velox:latest as velox';
        $dockerfile[] = '';

        $dockerfile[] = '# Build arguments';
        $dockerfile[] = 'ARG APP_VERSION="undefined"';
        $dockerfile[] = 'ARG BUILD_TIME="undefined"';
        $dockerfile[] = '';

        $dockerfile[] = '# Copy velox configuration';
        $dockerfile[] = 'COPY velox.toml .';
        $dockerfile[] = '';

        $dockerfile[] = '# Build RoadRunner binary';
        $dockerfile[] = 'ENV CGO_ENABLED=0';
        $dockerfile[] = 'RUN vx build -c velox.toml -o /usr/bin/';
        $dockerfile[] = '';

        $dockerfile[] = "# Runtime stage";
        $dockerfile[] = "FROM {$baseImage}";
        $dockerfile[] = '';

        $dockerfile[] = '# Copy RoadRunner binary from build stage';
        $dockerfile[] = 'COPY --from=velox /usr/bin/rr /usr/bin/rr';
        $dockerfile[] = '';

        $dockerfile[] = '# Copy application files';
        $dockerfile[] = 'COPY . /app';
        $dockerfile[] = 'WORKDIR /app';
        $dockerfile[] = '';

        $dockerfile[] = '# Set RoadRunner as entrypoint';
        $dockerfile[] = 'ENTRYPOINT ["/usr/bin/rr", "serve"]';

        return implode("\n", $dockerfile);
    }

    /**
     * @param array<string> $selectedPluginNames
     */
    public function buildConfigFromSelection(
        array $selectedPluginNames,
        PluginProviderInterface $pluginProvider,
        ?string $githubToken = null,
        ?string $gitlabToken = null,
        ?string $gitlabEndpoint = null,
    ): VeloxConfig {
        $githubPlugins = [];
        $gitlabPlugins = [];

        foreach ($selectedPluginNames as $pluginName) {
            $plugin = $pluginProvider->getPluginByName($pluginName);
            if ($plugin === null) {
                continue;
            }

            if ($plugin->repositoryType === PluginRepository::Github) {
                $githubPlugins[] = $plugin;
            } else {
                $gitlabPlugins[] = $plugin;
            }
        }

        return new VeloxConfig(
            github: new GitHubConfig(
                token: $githubToken ? new GitHubToken($githubToken) : null,
                plugins: $githubPlugins,
            ),
            gitlab: new GitLabConfig(
                token: $gitlabToken ? new GitLabToken($gitlabToken) : null,
                endpoint: $gitlabEndpoint ? new GitLabEndpoint($gitlabEndpoint) : null,
                plugins: $gitlabPlugins,
            ),
        );
    }
}
