<?php

declare(strict_types=1);

namespace App\Module\Velox\Configuration\Service;

use App\Module\Velox\Configuration\DTO\ValidationResult;
use App\Module\Velox\Configuration\DTO\VeloxConfig;
use App\Module\Velox\Dependency\Service\DependencyResolverService;

final readonly class ConfigurationValidatorService
{
    public function __construct(
        private DependencyResolverService $dependencyResolver,
    ) {}

    public function validateConfiguration(VeloxConfig $config): ValidationResult
    {
        $errors = [];
        $warnings = [];

        // Validate plugin dependencies
        $resolution = $this->dependencyResolver->resolveDependencies($config->getAllPlugins());

        if (!$resolution->isValid) {
            foreach ($resolution->conflicts as $conflict) {
                if ($conflict->severity === 'error') {
                    $errors[] = $conflict->message;
                } else {
                    $warnings[] = $conflict->message;
                }
            }
        }

        // Validate GitHub token if GitHub plugins are present
        if (!empty($config->github->plugins) && $config->github->token === null) {
            $warnings[] = 'GitHub token is not set but GitHub plugins are configured. This may cause rate limiting issues.';
        }

        // Validate GitLab configuration
        if (!empty($config->gitlab->plugins)) {
            if ($config->gitlab->token === null) {
                $warnings[] = 'GitLab token is not set but GitLab plugins are configured.';
            }
            if ($config->gitlab->endpoint === null) {
                $warnings[] = 'GitLab endpoint is not set, using default: https://gitlab.com';
            }
        }

        // Validate RoadRunner version
        if ($config->roadrunner->ref === 'master') {
            $warnings[] = 'Using master branch for RoadRunner is not recommended for production use.';
        }

        // Validate plugin versions compatibility
        $versionSuggestions = $this->dependencyResolver->suggestCompatibleVersions($config->getAllPlugins());
        foreach ($versionSuggestions as $suggestion) {
            $warnings[] = "Plugin {$suggestion->pluginName}: {$suggestion->reason}";
        }

        // Check for essential plugins
        $hasServer = $config->hasPlugin('server');
        if (!$hasServer) {
            $errors[] = 'Server plugin is required but not configured.';
        }

        // Validate minimum plugin set for common configurations
        $hasHttp = $config->hasPlugin('http');
        $hasLogger = $config->hasPlugin('logger');

        if ($hasHttp && !$hasLogger) {
            $warnings[] = 'HTTP plugin is configured but logger is missing. Logging is recommended for HTTP servers.';
        }

        return new ValidationResult(
            isValid: empty($errors),
            errors: $errors,
            warnings: $warnings,
        );
    }
}
