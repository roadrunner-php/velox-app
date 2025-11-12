<?php

declare(strict_types=1);

namespace App\Module\Velox\Configuration\Service;

use App\Module\Velox\Configuration\DTO\GitHubConfig;
use App\Module\Velox\Configuration\DTO\GitHubToken;
use App\Module\Velox\Configuration\DTO\GitLabConfig;
use App\Module\Velox\Configuration\DTO\GitLabEndpoint;
use App\Module\Velox\Configuration\DTO\GitLabToken;
use App\Module\Velox\Configuration\DTO\RoadRunnerConfig;
use App\Module\Velox\Configuration\DTO\VeloxConfig;
use App\Module\Velox\Plugin\DTO\PluginRepository;
use App\Module\Velox\Plugin\Service\PluginProviderInterface;
use Devium\Toml\Toml;

final readonly class ConfigurationGeneratorService
{
    public function __construct(
        private PluginProviderInterface $pluginProvider,
        private string $roadRunnerVersion = 'v2025.1.1',
        private string $veloxVersion = 'v2025.1.1',
        private ?string $githubToken = null,
        private ?string $gitlabToken = null,
        private ?string $gitlabEndpoint = null,
    ) {}

    public function generateToml(VeloxConfig $config): string
    {
        return Toml::encode(\json_decode(\json_encode($config), true));
    }

    public function generateDockerfile(
        VeloxConfig $config,
        string $baseImage = 'php:8.4-cli',
    ): string {
        $dockerfile = [];

        $dockerfile[] = '# Multi-stage build for RoadRunner with Velox';
        $dockerfile[] = \sprintf(
            'FROM --platform=${TARGETPLATFORM:-linux/amd64}  ghcr.io/roadrunner-server/velox:%s as velox',
            $this->veloxVersion,
        );
        $dockerfile[] = '';

        $dockerfile[] = '# Build arguments';
        $dockerfile[] = 'ARG APP_VERSION="1.0.0"';
        $dockerfile[] = 'ARG BUILD_TIME="undefined"';
        $dockerfile[] = '';

        // Generate TOML content and write it line by line
        $tomlContent = $this->generateToml($config);
        $dockerfile[] = '# Generate velox configuration';
        $dockerfile = [...$dockerfile, ...$this->buildLineByLineWrites($tomlContent, 'velox.toml')];
        $dockerfile[] = '';

        $dockerfile[] = '# Build RoadRunner binary';
        $dockerfile[] = 'ENV CGO_ENABLED=0';
        $dockerfile[] = 'RUN vx build -c velox.toml -o /usr/bin/';
        $dockerfile[] = '';

        $dockerfile[] = "# Runtime stage";
        $dockerfile[] = \sprintf('FROM --platform=${TARGETPLATFORM:-linux/amd64} %s', $baseImage);
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

        return \implode("\n", $dockerfile);
    }

    /**
     * @param array<string> $selectedPluginNames
     */
    public function buildConfigFromSelection(array $selectedPluginNames, ?string $githubToken = null): VeloxConfig
    {
        $githubPlugins = [];
        $gitlabPlugins = [];

        if ($selectedPluginNames === []) {
            $selectedPluginNames = \array_map(
                static fn($plugin) => $plugin->name,
                $this->pluginProvider->getOfficialPlugins(),
            );
        }

        foreach ($selectedPluginNames as $pluginName) {
            $plugin = $this->pluginProvider->getPluginByName($pluginName);
            if ($plugin === null) {
                continue;
            }

            if ($plugin->repositoryType === PluginRepository::Github) {
                $githubPlugins[] = $plugin;
            } else {
                $gitlabPlugins[] = $plugin;
            }
        }

        $githubToken ??= $this->githubToken;

        return new VeloxConfig(
            roadrunner: new RoadRunnerConfig(
                ref: $this->roadRunnerVersion,
            ),
            github: new GitHubConfig(
                token: $githubToken ? new GitHubToken($githubToken) : null,
                plugins: $githubPlugins,
            ),
            gitlab: new GitLabConfig(
                token: $this->gitlabToken ? new GitLabToken($this->gitlabToken) : null,
                endpoint: $this->gitlabEndpoint ? new GitLabEndpoint($this->gitlabEndpoint) : null,
                plugins: $gitlabPlugins,
            ),
        );
    }

    /**
     * Build line-by-line echo commands for writing TOML content
     *
     * @return array<string>
     */
    private function buildLineByLineWrites(string $content, string $targetFile): array
    {
        $lines = \explode("\n", $content);
        $commands = [];

        foreach ($lines as $index => $line) {
            // Escape single quotes by replacing them with '\''
            $escapedLine = \str_replace("'", "'\\''", $line);

            if ($index === 0) {
                // First line - create/overwrite the file
                $commands[] = "RUN echo '{$escapedLine}' > {$targetFile}";
            } else {
                // Subsequent lines - append to the file
                $commands[] = "RUN echo '{$escapedLine}' >> {$targetFile}";
            }
        }

        return $commands;
    }
}
