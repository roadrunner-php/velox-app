<?php

declare(strict_types=1);

namespace App\Module\Velox\Service;

use App\Module\Velox\DTO\GitHubConfig;
use App\Module\Velox\DTO\GitHubToken;
use App\Module\Velox\DTO\GitLabConfig;
use App\Module\Velox\DTO\GitLabEndpoint;
use App\Module\Velox\DTO\GitLabToken;
use App\Module\Velox\DTO\Plugin;
use App\Module\Velox\DTO\PluginRepository;
use App\Module\Velox\DTO\VeloxConfig;

final class ConfigurationGeneratorService
{
    public function generateVeloxToml(VeloxConfig $config): string
    {
        $toml = [];

        // RoadRunner section
        $toml[] = '[roadrunner]';
        $toml[] = "ref = \"{$config->roadrunner->ref}\"";
        $toml[] = '';

        // Debug section
        if ($config->debug->enabled) {
            $toml[] = '[debug]';
            $toml[] = 'enabled = true';
            $toml[] = '';
        }

        // GitHub section
        if (!empty($config->github->plugins) || $config->github->token !== null) {
            $toml[] = '[github]';

            if ($config->github->token !== null) {
                $toml[] = '[github.token]';
                $toml[] = "token = \"{$config->github->token->token}\"";
                $toml[] = '';
            }

            if (!empty($config->github->plugins)) {
                $toml[] = '[github.plugins]';
                foreach ($config->github->plugins as $plugin) {
                    $toml[] = $this->formatPluginLine($plugin);
                }
                $toml[] = '';
            }
        }

        // GitLab section
        if (!empty($config->gitlab->plugins) || $config->gitlab->token !== null) {
            $toml[] = '[gitlab]';

            if ($config->gitlab->token !== null) {
                $toml[] = '[gitlab.token]';
                $toml[] = "token = \"{$config->gitlab->token->token}\"";
                $toml[] = '';
            }

            if ($config->gitlab->endpoint !== null) {
                $toml[] = '[gitlab.endpoint]';
                $toml[] = "endpoint = \"{$config->gitlab->endpoint->endpoint}\"";
                $toml[] = '';
            }

            if (!empty($config->gitlab->plugins)) {
                $toml[] = '[gitlab.plugins]';
                foreach ($config->gitlab->plugins as $plugin) {
                    $toml[] = $this->formatPluginLine($plugin);
                }
                $toml[] = '';
            }
        }

        // Log section
        $toml[] = '[log]';
        $toml[] = "level = \"{$config->log->level->value}\"";
        $toml[] = "mode = \"{$config->log->mode->value}\"";

        return implode("\n", $toml);
    }

    public function generateVeloxJson(VeloxConfig $config): string
    {
        $data = [
            'roadrunner' => [
                'ref' => $config->roadrunner->ref,
            ],
            'debug' => [
                'enabled' => $config->debug->enabled,
            ],
            'log' => [
                'level' => $config->log->level->value,
                'mode' => $config->log->mode->value,
            ],
        ];

        if (!empty($config->github->plugins) || $config->github->token !== null) {
            $data['github'] = [];

            if ($config->github->token !== null) {
                $data['github']['token'] = ['token' => $config->github->token->token];
            }

            if (!empty($config->github->plugins)) {
                $data['github']['plugins'] = [];
                foreach ($config->github->plugins as $plugin) {
                    $data['github']['plugins'][$plugin->name] = $this->formatPluginArray($plugin);
                }
            }
        }

        if (!empty($config->gitlab->plugins) || $config->gitlab->token !== null) {
            $data['gitlab'] = [];

            if ($config->gitlab->token !== null) {
                $data['gitlab']['token'] = ['token' => $config->gitlab->token->token];
            }

            if ($config->gitlab->endpoint !== null) {
                $data['gitlab']['endpoint'] = ['endpoint' => $config->gitlab->endpoint->endpoint];
            }

            if (!empty($config->gitlab->plugins)) {
                $data['gitlab']['plugins'] = [];
                foreach ($config->gitlab->plugins as $plugin) {
                    $data['gitlab']['plugins'][$plugin->name] = $this->formatPluginArray($plugin);
                }
            }
        }

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
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

    private function formatPluginLine(Plugin $plugin): string
    {
        $parts = [
            "ref = \"{$plugin->ref}\"",
            "owner = \"{$plugin->owner}\"",
            "repository = \"{$plugin->repository}\"",
        ];

        if ($plugin->folder !== null) {
            $parts[] = "folder = \"{$plugin->folder}\"";
        }

        if ($plugin->replace !== null) {
            $parts[] = "replace = \"{$plugin->replace}\"";
        }

        return "{$plugin->name} = { " . implode(', ', $parts) . ' }';
    }

    /**
     * @return array<string, mixed>
     */
    private function formatPluginArray(Plugin $plugin): array
    {
        $data = [
            'ref' => $plugin->ref,
            'owner' => $plugin->owner,
            'repository' => $plugin->repository,
        ];

        if ($plugin->folder !== null) {
            $data['folder'] = $plugin->folder;
        }

        if ($plugin->replace !== null) {
            $data['replace'] = $plugin->replace;
        }

        return $data;
    }
}
