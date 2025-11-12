<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryBuilder\Converter;

use App\Module\Velox\BinaryBuilder\DTO\BuildRequest;
use App\Module\Velox\BinaryBuilder\DTO\TargetPlatform;
use App\Module\Velox\Configuration\DTO\VeloxConfig;
use App\Module\Velox\Plugin\DTO\Plugin;
use App\Module\Velox\Plugin\DTO\PluginRepository;
use Ramsey\Uuid\Uuid;

final readonly class ConfigToRequestConverter
{
    /**
     * Convert VeloxConfig to Velox server BuildRequest
     */
    public function convert(
        VeloxConfig $config,
        ?TargetPlatform $targetPlatform = null,
        bool $forceRebuild = false,
        ?string $requestId = null,
    ): BuildRequest {
        return new BuildRequest(
            requestId: $requestId ?? Uuid::uuid4()->toString(),
            forceRebuild: $forceRebuild,
            targetPlatform: $targetPlatform ?? TargetPlatform::current(),
            rrVersion: $config->roadrunner->ref,
            plugins: $this->convertPlugins($config->getAllPlugins()),
        );
    }

    /**
     * Convert Plugin DTOs to Velox API format
     *
     * @param array<Plugin> $plugins
     * @return array<array{module_name: string, tag: string, replace?: string}>
     */
    private function convertPlugins(array $plugins): array
    {
        $converted = [];

        foreach ($plugins as $plugin) {
            $moduleName = $this->buildModuleName($plugin);

            $spec = [
                'module_name' => $moduleName,
                'tag' => $plugin->ref,
            ];

            // Add replace directive if present
            if ($plugin->replace !== null) {
                $spec['replace'] = $plugin->replace;
            }

            $converted[] = $spec;
        }

        return $converted;
    }

    /**
     * Build Go module name from plugin
     */
    private function buildModuleName(Plugin $plugin): string
    {
        $basePath = match ($plugin->repositoryType) {
            PluginRepository::Github => "github.com/{$plugin->owner}/{$plugin->repository}",
            PluginRepository::GitLab => "gitlab.com/{$plugin->owner}/{$plugin->repository}",
        };

        // Extract major version from ref (e.g., v5.2.7 -> v5)
        $majorVersion = $this->extractMajorVersion($plugin->ref);

        // Append major version if present and > v1
        if ($majorVersion !== null && $majorVersion > 1) {
            $basePath .= "/v{$majorVersion}";
        }

        // Append folder path if specified
        if ($plugin->folder !== null) {
            $basePath .= '/' . \ltrim($plugin->folder, '/');
        }

        return $basePath;
    }

    /**
     * Extract major version number from ref (e.g., v5.2.7 -> 5)
     */
    private function extractMajorVersion(string $ref): ?int
    {
        if (\preg_match('/^v(\d+)/', $ref, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }
}
