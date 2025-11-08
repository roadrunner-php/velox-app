<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Discovery;

use App\Module\Github\DiscoveryClient;
use App\Module\Velox\Plugin\Discovery\Endpoint\Http\Middleware\WebhookMiddleware;
use App\Module\Velox\Plugin\Discovery\Repository\CachePluginRepository;
use App\Module\Velox\Plugin\Discovery\Repository\DiscoveredPluginRepositoryInterface;
use App\Module\Velox\Plugin\Discovery\Service\GitHubDiscoveryPluginProvider;
use App\Module\Velox\Plugin\Discovery\Service\GitHubDiscoveryService;
use App\Module\Velox\Plugin\Discovery\Service\ManifestParserService;
use App\Module\Velox\Plugin\Discovery\Service\PluginRegistryService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Cache\CacheStorageProviderInterface;

/**
 * Bootloader for plugin discovery system
 */
final class DiscoveryBootloader extends Bootloader
{
    public function defineSingletons(): array
    {
        return [
            // Repository for storing discovered plugins
            DiscoveredPluginRepositoryInterface::class => static fn(
                CacheStorageProviderInterface $provider,
            )
                => new CachePluginRepository(cache: $provider->storage('plugins')),

            // Manifest parser
            ManifestParserService::class => ManifestParserService::class,

            // Plugin registry
            PluginRegistryService::class => PluginRegistryService::class,

            // Discovery service
            GitHubDiscoveryService::class => static fn(
                DiscoveryClient $githubClient,
                ManifestParserService $manifestParser,
                PluginRegistryService $pluginRegistry,
                LoggerInterface $logger,
                EnvironmentInterface $env,
            ): GitHubDiscoveryService
                => new GitHubDiscoveryService(
                githubClient: $githubClient,
                manifestParser: $manifestParser,
                pluginRegistry: $pluginRegistry,
                logger: $logger,
                organization: $env->get('VELOX_GITHUB_ORG', 'roadrunner-plugins'),
                manifestFile: $env->get('VELOX_MANIFEST_FILE', '.velox.yaml'),
            ),

            // Discovery plugin provider
            GitHubDiscoveryPluginProvider::class => static fn(
                DiscoveredPluginRepositoryInterface $repository,
                GitHubDiscoveryService $discoveryService,
                EnvironmentInterface $env,
            ): GitHubDiscoveryPluginProvider
                => new GitHubDiscoveryPluginProvider(
                repository: $repository,
                discoveryService: $discoveryService,
                lazyLoad: (bool) $env->get('VELOX_LAZY_LOAD', true),
            ),

            // Webhook middleware
            WebhookMiddleware::class => static fn(
                ResponseFactoryInterface $responseFactory,
                EnvironmentInterface $env,
            ): WebhookMiddleware
                => new WebhookMiddleware(
                responseFactory: $responseFactory,
                webhookSecret: $env->get('VELOX_WEBHOOK_SECRET'),
            ),
        ];
    }
}
